<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\WithdrawRequest;
use App\Notifications\NewWithdrawRequest;
use App\Services\PaymentGatewayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawController extends Controller
{
    protected PaymentGatewayService $gateway;

    public function __construct(PaymentGatewayService $gateway)
    {
        $this->gateway = $gateway;
    }

    private function minWithdrawAmount(): int
    {
        return max(1000, (int) AppSetting::get('min_withdraw_nominal', 10000));
    }

    /** Show withdraw form */
    public function showForm(Request $request)
    {
        $user = $request->user();
        $isCustomer = ($user->role === 'customer');
        $prefix = $isCustomer ? 'customer' : 'mitra';
        $dashboardRoute = $prefix . '.dashboard';
        $profileEditRoute = $isCustomer ? 'profile.edit' : 'mitra.profile.edit';

        if (! $user->isProfileComplete()) {
            $missing = implode(', ', $user->getMissingProfileFields());

            return redirect()->route($profileEditRoute)->with('error', "Harap lengkapi profil Anda ($missing) terlebih dahulu sebelum melakukan penarikan saldo.");
        }

        // Cek batasan penarikan 1x dalam 24 jam
        $limit24h = $user->getWithdraw24hLimitStatus();

        // If the user's latest withdraw was already processed successfully,
        // redirect them to the success page so they see the confirmation.
        $latest = WithdrawRequest::where('user_id', $user->id)->orderByDesc('created_at')->first();
        $pendingWithdraw = WithdrawRequest::where('user_id', $user->id)
            ->whereIn('status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_PROCESSING])
            ->orderByDesc('created_at')
            ->first();
        // If caller explicitly requests the form (force=1) skip redirect so "Kembali ke Pengajuan" works
        $force = $request->query('force');

        // Also avoid redirecting when the user clicked the link from the dashboard
        $referer = $request->headers->get('referer');
        $fromDashboard = false;
        try {
            $dashboardUrl = route($dashboardRoute);
            if ($referer && str_contains($referer, $dashboardUrl)) {
                $fromDashboard = true;
            }
        } catch (\Throwable $e) {
            // ignore route exceptions
        }

        // Redirect to success only if the latest withdraw is success AND it was processed recently
        // This avoids redirecting to the success page on refresh long after processing occurred.
        $recentWindowMinutes = 15;
        $isRecent = false;
        if ($latest && $latest->processed_at) {
            try {
                $isRecent = Carbon::parse($latest->processed_at)->greaterThanOrEqualTo(Carbon::now()->subMinutes($recentWindowMinutes));
            } catch (\Throwable $e) {
                $isRecent = false;
            }
        }

        if (! $force && ! $fromDashboard && $latest && $isRecent) {
            if ($latest->status === WithdrawRequest::STATUS_SUCCESS) {
                return redirect()->route($prefix . '.withdraw.success', $latest->id);
            }

            if ($latest->status === WithdrawRequest::STATUS_FAILED) {
                return redirect()->route($prefix . '.withdraw.rejected', $latest->id);
            }
        }

        // Otherwise render the withdraw form (the form view will show pending message if needed)
        $bankAccounts = $user->bankAccounts()->get();
        $primaryAccount = $user->primaryBankAccount();
        $minWithdraw = $this->minWithdrawAmount();

        return view('livewire.mitra.withdraw.form', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
            'primaryAccount' => $primaryAccount,
            'limit24h' => $limit24h,
            'pendingWithdraw' => $pendingWithdraw,
            'minWithdraw' => $minWithdraw,
            'prefix' => $prefix,
            'isCustomer' => $isCustomer,
        ]);
    }

    /** Handle withdraw request */
    public function requestWithdraw(Request $request)
    {
        $user = $request->user();
        $isCustomer = ($user->role === 'customer');
        $prefix = $isCustomer ? 'customer' : 'mitra';
        $profileEditRoute = $isCustomer ? 'profile.edit' : 'mitra.profile.edit';

        if (! $user->isProfileComplete()) {
            $missing = implode(', ', $user->getMissingProfileFields());

            return redirect()->route($profileEditRoute)->with('error', "Harap lengkapi profil Anda ($missing) terlebih dahulu sebelum melakukan penarikan saldo.");
        }

        if (! $user->hasVerifiedEmail()) {
            return back()->withErrors(['general' => 'Saldo belum dapat ditarik. Harap verifikasi email Anda terlebih dahulu sebelum mengajukan penarikan dana.'])->withInput();
        }

        if (! $user->hasTransactionPin()) {
            return redirect()->route($prefix . '.bank-accounts')->with('error', 'Harap atur PIN transaksi 6 digit terlebih dahulu untuk keamanan penarikan saldo.');
        }

        if ($user->isPinLocked()) {
            return back()->withErrors(['pin' => 'PIN transaksi Anda terkunci karena salah 3x berturut-turut. Coba lagi dalam ' . $user->getPinLockRemainingMinutes() . ' menit.'])->withInput();
        }

        // Cek batasan penarikan per 24 jam (diatur di Super Admin)
        $limit24h = $user->getWithdraw24hLimitStatus();
        if (! $limit24h['allowed']) {
            return back()->withErrors([
                'general' => "Penarikan saldo dibatasi {$limit24h['max_limit']}x dalam 24 jam. Anda dapat mengajukan penarikan kembali pada {$limit24h['available_at']->translatedFormat('d M Y, H:i')} WIB (sisa {$limit24h['remaining_text']}).",
            ])->withInput();
        }

        $minWithdraw = $this->minWithdrawAmount();
        $userBalance = (int) $user->balance;

        if ($request->has('amount')) {
            $rawAmount = preg_replace('/\D/', '', (string) $request->input('amount'));
            $request->merge(['amount' => $rawAmount !== '' ? (int) $rawAmount : null]);
        }

        $request->validate([
            'amount' => ['required', 'integer', 'min:'.$minWithdraw, 'max:'.max($minWithdraw, $userBalance)],
            'bank_account_id' => ['required', 'exists:user_bank_accounts,id'],
            'pin' => ['required', 'digits:6'],
        ], [
            'amount.required' => 'Jumlah penarikan wajib diisi.',
            'amount.integer' => 'Jumlah penarikan harus berupa angka.',
            'amount.min' => 'Jumlah penarikan minimal '.format_rupiah($minWithdraw).'.',
            'amount.max' => 'Saldo Anda tidak mencukupi untuk penarikan sebesar '.format_rupiah((int) $request->input('amount', 0)).'. Saldo tersedia: '.format_rupiah($userBalance).'.',
            'bank_account_id.required' => 'Silakan pilih rekening bank atau e-wallet tujuan.',
            'bank_account_id.exists' => 'Rekening tujuan tidak valid.',
            'pin.required' => 'PIN transaksi 6 digit wajib diisi.',
            'pin.digits' => 'PIN transaksi harus berupa 6 digit angka.',
        ]);

        // Verifikasi kepemilikan rekening
        $bankAccount = $user->bankAccounts()->where('id', $request->input('bank_account_id'))->first();
        if (! $bankAccount) {
            return back()->withErrors(['bank_account_id' => 'Rekening bank tidak ditemukan atau bukan milik Anda.'])->withInput();
        }

        // Verifikasi PIN Transaksi
        if (! $user->verifyTransactionPin($request->input('pin'))) {
            $attemptsLeft = max(0, 3 - (int) $user->pin_attempts);
            if ($attemptsLeft === 0) {
                return back()->withErrors(['pin' => 'PIN salah! Akun Anda dikunci selama 15 menit karena 3x salah memasukkan PIN.'])->withInput();
            }
            return back()->withErrors(['pin' => 'PIN transaksi salah. Sisa percobaan: ' . $attemptsLeft . ' kali.'])->withInput();
        }

        $amount = (int) $request->input('amount');

        try {
            $txResult = DB::transaction(function () use ($user, $bankAccount, $amount, $minWithdraw) {
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

                if ($lockedUser->hasPendingOrProcessingWithdraws()) {
                    return ['errorField' => 'general', 'errorMsg' => 'Anda memiliki permintaan tarik saldo yang sedang diproses. Mohon tunggu hingga selesai.'];
                }

                $txLimit24h = $lockedUser->getWithdraw24hLimitStatus();
                if (! $txLimit24h['allowed']) {
                    return ['errorField' => 'general', 'errorMsg' => "Penarikan saldo dibatasi {$txLimit24h['max_limit']}x dalam 24 jam. Anda dapat mengajukan penarikan kembali pada {$txLimit24h['available_at']->translatedFormat('d M Y, H:i')} WIB (sisa {$txLimit24h['remaining_text']})."];
                }

                $userBalanceModel = $lockedUser->balance()->firstOrCreate(['user_id' => $lockedUser->id], ['balance' => 0]);
                $freshBalance = (int) round(UserBalance::recalculateForUser($lockedUser->id));

                if ($freshBalance < $minWithdraw) {
                    return ['errorField' => 'amount', 'errorMsg' => 'Saldo Anda tidak mencukupi untuk melakukan penarikan. Minimal saldo adalah '.format_rupiah($minWithdraw).' (Saldo Anda: '.format_rupiah($freshBalance).').'];
                }

                if ($amount > $freshBalance) {
                    return ['errorField' => 'amount', 'errorMsg' => 'Jumlah penarikan ('.format_rupiah($amount).') melebihi saldo tersedia Anda ('.format_rupiah($freshBalance).').'];
                }

                $withdraw = WithdrawRequest::create([
                    'user_id' => $lockedUser->id,
                    'amount' => $amount,
                    'bank_code' => $bankAccount->bank_code,
                    'account_number' => $bankAccount->account_number,
                    'account_name' => $bankAccount->account_name,
                    'status' => WithdrawRequest::STATUS_PENDING,
                ]);

                $userBalanceModel->deductBalance(
                    $amount,
                    'Pengajuan Penarikan Saldo #'.$withdraw->withdraw_code.' ke '.$bankAccount->bank_name.' ('.$bankAccount->masked_account_number.')',
                    'WD-'.$withdraw->id
                );

                return ['withdraw' => $withdraw];
            });

            if (isset($txResult['errorMsg'])) {
                return back()->withErrors([$txResult['errorField'] => $txResult['errorMsg']])->withInput();
            }

            $withdraw = $txResult['withdraw'];

            // Notify super admins and admin of the user's city
            try {
                $superAdmins = User::where('role', 'super_admin')->where('status', 'active')->get();
                $mitraCityId = $user->city_id ?? $withdraw->user?->city_id;
                $cityAdmins = User::getAdminsForCity($mitraCityId);

                $recipients = $superAdmins->merge($cityAdmins)->unique('id');

                foreach ($recipients as $recipient) {
                    $recipient->notify(new NewWithdrawRequest($withdraw));
                }
            } catch (\Throwable $ne) {
                Log::warning('WithdrawController: failed to send withdraw notification', ['error' => $ne->getMessage()]);
            }

            // Redirect back to form — the form view will show a 'pending' message when a request exists
            return redirect()->route($prefix . '.withdraw.form')->with('status', 'Pengajuan Anda berhasil dikirim dan sedang menunggu proses oleh admin. Mohon tunggu 1-5 hari kerja.');
        } catch (\Throwable $e) {
            Log::error('WithdrawController: error creating withdraw', ['error' => $e->getMessage()]);

            return back()->withErrors(['general' => 'Terjadi kesalahan saat membuat permintaan tarik saldo.'])->withInput();
        }
    }

    /** Cancel pending withdraw request and refund held balance */
    public function cancelPendingWithdraw(Request $request, WithdrawRequest $withdraw)
    {
        $user = $request->user();
        if ($withdraw->user_id !== $user->id) {
            abort(403);
        }

        $isCustomer = ($user->role === 'customer');
        $prefix = $isCustomer ? 'customer' : 'mitra';

        try {
            $result = DB::transaction(function () use ($user, $withdraw) {
                $lockedWithdraw = WithdrawRequest::where('id', $withdraw->id)->lockForUpdate()->firstOrFail();

                if ($lockedWithdraw->status !== WithdrawRequest::STATUS_PENDING) {
                    return ['error' => 'Hanya pengajuan berstatus menunggu yang dapat dibatalkan.'];
                }

                $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                $exactHold = BalanceTransaction::where('user_id', $lockedUser->id)
                    ->where('type', 'deduction')
                    ->where('reference_id', 'WD-'.$lockedWithdraw->id)
                    ->exists();

                $legacyHold = ! $exactHold && BalanceTransaction::where('user_id', $lockedUser->id)
                    ->where('type', 'deduction')
                    ->where('amount', $lockedWithdraw->amount)
                    ->where('created_at', '>=', ($lockedWithdraw->created_at ?? now())->copy()->subMinutes(5))
                    ->where(function ($q) {
                        $q->where('reference_id', 'WD-PENDING')
                            ->orWhere('description', 'like', '%Pengajuan Penarikan Saldo%');
                    })
                    ->exists();

                if ($exactHold || $legacyHold) {
                    $cancelRef = 'CANCEL-WD-'.$lockedWithdraw->id;
                    $alreadyRefunded = BalanceTransaction::where('user_id', $lockedUser->id)
                        ->whereIn('reference_id', [$cancelRef, 'REFUND-WD-'.$lockedWithdraw->id])
                        ->exists();

                    if (! $alreadyRefunded) {
                        $userBalance = $lockedUser->balance()->firstOrCreate(['user_id' => $lockedUser->id], ['balance' => 0]);
                        $userBalance->addBalance(
                            (int) $lockedWithdraw->amount,
                            'Pembatalan Penarikan Saldo #'.$lockedWithdraw->withdraw_code.' oleh pengguna',
                            $cancelRef
                        );
                    }
                }

                $lockedWithdraw->update([
                    'status' => WithdrawRequest::STATUS_FAILED,
                    'processed_at' => now(),
                    'description' => 'Dibatalkan oleh pengguna',
                ]);

                return ['ok' => true];
            });

            if (isset($result['error'])) {
                return redirect()->route($prefix.'.withdraw.form')->withErrors(['general' => $result['error']]);
            }

            return redirect()->route($prefix.'.withdraw.form', ['force' => 1])->with('status', 'Pengajuan penarikan saldo berhasil dibatalkan dan saldo telah dikembalikan.');
        } catch (\Throwable $e) {
            Log::error('WithdrawController: error cancelling withdraw', ['error' => $e->getMessage(), 'withdraw_id' => $withdraw->id]);

            return redirect()->route($prefix.'.withdraw.form')->withErrors(['general' => 'Gagal membatalkan pengajuan penarikan saldo.']);
        }
    }

    /** Show transaction & withdraw history for user */
    public function withdrawHistory(Request $request)
    {
        $user = $request->user();
        $isCustomer = ($user->role === 'customer');
        $prefix = $isCustomer ? 'customer' : 'mitra';
        $activeTab = $request->query('tab', 'income');

        $incomeQuery = BalanceTransaction::where('user_id', $user->id)
            ->where(function ($q) use ($isCustomer) {
                if ($isCustomer) {
                    $q->whereIn('type', ['topup', 'refund'])
                        ->orWhere('description', 'like', 'Pengembalian%')
                        ->orWhere('description', 'like', 'Refund%')
                        ->orWhere('description', 'like', 'Pembatalan%');
                } else {
                    $q->where('type', 'topup')
                        ->orWhere('description', 'like', 'Pendapatan Bantuan%');
                }
            })
            ->where('status', 'completed');

        $incomeHistory = (clone $incomeQuery)
            ->with(['help'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'income_page');

        $withdrawHistory = WithdrawRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'withdraw_page');

        $totalIncome = (float) (clone $incomeQuery)->sum('amount');

        $totalWithdrawn = (float) WithdrawRequest::where('user_id', $user->id)
            ->whereIn('status', ['success', 'approved'])
            ->sum('amount');

        // Build combined transactions feed matching new mobile design
        $recentIncomes = (clone $incomeQuery)
            ->with(['help.category'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(function ($item) use ($prefix, $isCustomer) {
                $help = $item->help;
                $desc = (string) ($item->description ?? '');

                if ($isCustomer) {
                    $isRefund = $item->type === 'refund'
                        || str_contains(strtolower($desc), 'pengembalian')
                        || str_contains(strtolower($desc), 'refund')
                        || str_contains(strtolower($desc), 'pembatalan');

                    return (object) [
                        'id' => $item->id,
                        'type' => 'income',
                        'title' => $isRefund ? 'Pengembalian Dana' : 'Top Up Saldo',
                        'subtitle' => $desc !== '' ? $desc : ($isRefund ? 'Pengembalian saldo dompet' : 'Isi ulang saldo dompet'),
                        'amount' => (float) $item->amount,
                        'created_at' => $item->created_at,
                        'status' => 'completed',
                        'status_label' => null,
                        'detail_url' => $help ? route('customer.helps.detail', $help->id) : null,
                    ];
                }

                $orderCode = $help ? ($help->order_id ?: ('SRB'.str_pad($help->id, 4, '0', STR_PAD_LEFT))) : ($item->order_id ?: ('SRB'.str_pad($item->id, 4, '0', STR_PAD_LEFT)));
                if (str_starts_with($orderCode, 'HELP-')) {
                    $orderCode = 'SRB'.str_pad(substr($orderCode, 5), 4, '0', STR_PAD_LEFT);
                }
                $catName = $help ? (optional($help->category)->name ?? $help->title) : ($desc !== '' ? $desc : 'Pekerjaan Jasa');

                return (object) [
                    'id' => $item->id,
                    'type' => 'income',
                    'title' => 'Order #'.$orderCode,
                    'subtitle' => $catName,
                    'amount' => (float) $item->amount,
                    'created_at' => $item->created_at,
                    'status' => 'completed',
                    'status_label' => null,
                    'detail_url' => $help ? route($prefix . '.helps.detail', $help->id) : null,
                ];
            });

        $recentWithdraws = WithdrawRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(function ($item) use ($prefix) {
                $bankCode = strtoupper($item->bank_code ?? 'BCA');
                $accMasked = strlen((string) $item->account_number) >= 4 ? '****'.substr((string) $item->account_number, -4) : '****';

                $statusLabel = match ($item->status) {
                    WithdrawRequest::STATUS_SUCCESS => 'Berhasil',
                    WithdrawRequest::STATUS_PROCESSING => 'Diproses',
                    WithdrawRequest::STATUS_FAILED => 'Ditolak / Batal',
                    default => 'Menunggu',
                };

                $detailUrl = match ($item->status) {
                    WithdrawRequest::STATUS_SUCCESS => route($prefix.'.withdraw.success', $item->id),
                    WithdrawRequest::STATUS_FAILED => route($prefix.'.withdraw.rejected', $item->id),
                    default => null,
                };

                return (object) [
                    'id' => $item->id,
                    'type' => 'withdraw',
                    'title' => 'Penarikan Saldo',
                    'subtitle' => 'Ke '.$bankCode.' '.$accMasked,
                    'amount' => (float) $item->amount,
                    'created_at' => $item->created_at,
                    'status' => $item->status,
                    'status_label' => $statusLabel,
                    'detail_url' => $detailUrl,
                ];
            });

        $combinedTransactions = $recentIncomes->concat($recentWithdraws)
            ->sortByDesc('created_at')
            ->values();

        return view('livewire.mitra.withdraw.history', [
            'incomeHistory' => $incomeHistory,
            'withdrawHistory' => $withdrawHistory,
            'combinedTransactions' => $combinedTransactions,
            'history' => $withdrawHistory, // backward compatibility
            'activeTab' => $activeTab,
            'totalIncome' => $totalIncome,
            'totalWithdrawn' => $totalWithdrawn,
            'user' => $user,
            'prefix' => $user->role === 'customer' ? 'customer' : 'mitra',
            'isCustomer' => ($user->role === 'customer'),
        ]);
    }

    /** Show success page for a completed withdraw */
    public function showSuccess(Request $request, WithdrawRequest $withdraw)
    {
        $user = $request->user();
        if ($withdraw->user_id !== $user->id) {
            abort(403);
        }

        $prefix = $user->role === 'customer' ? 'customer' : 'mitra';

        if ($withdraw->status !== WithdrawRequest::STATUS_SUCCESS) {
            return redirect()->route($prefix . '.withdraw.history')->with('status', 'Penarikan belum selesai atau tidak ditemukan.');
        }

        return view('livewire.mitra.withdraw.success', [
            'withdraw' => $withdraw,
            'user' => $user,
            'prefix' => $prefix,
            'isCustomer' => ($user->role === 'customer'),
        ]);
    }

    /** Show rejected page for a failed withdraw */
    public function showRejected(Request $request, WithdrawRequest $withdraw)
    {
        $user = $request->user();
        if ($withdraw->user_id !== $user->id) {
            abort(403);
        }

        $prefix = $user->role === 'customer' ? 'customer' : 'mitra';

        if ($withdraw->status !== WithdrawRequest::STATUS_FAILED) {
            return redirect()->route($prefix . '.withdraw.history')->with('status', 'Penarikan belum dibatalkan atau tidak ditemukan.');
        }

        return view('livewire.mitra.withdraw.rejected', [
            'withdraw' => $withdraw,
            'user' => $user,
            'prefix' => $prefix,
            'isCustomer' => ($user->role === 'customer'),
        ]);
    }

    /** Public endpoint: gateway callback (for real integration) */
    public function gatewayCallback(Request $request)
    {
        // In production validate signature / secret
        $payload = $request->all();

        if (empty($payload['external_id']) || empty($payload['status'])) {
            return response()->json(['error' => 'invalid_payload'], 422);
        }

        $withdraw = WithdrawRequest::where('external_id', $payload['external_id'])->first();
        if (! $withdraw) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $status = $payload['status'];

        if ($status === WithdrawRequest::STATUS_SUCCESS) {
            $withdraw->update(['status' => WithdrawRequest::STATUS_SUCCESS, 'processed_at' => now()]);
        } else {
            $withdraw->update(['status' => WithdrawRequest::STATUS_FAILED, 'processed_at' => now()]);
            // refund
            $user = $withdraw->user;
            if ($user) {
                $user->adjustBalance($withdraw->amount);
            }
        }

        return response()->json(['ok' => true]);
    }
}
