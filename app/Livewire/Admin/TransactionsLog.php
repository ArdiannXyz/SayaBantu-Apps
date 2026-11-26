<?php

namespace App\Livewire\Admin;

use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsLog extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = 'all'; // all, topup, mitra_income, deduction, withdraw, other

    public int $perPage = 15;

    public string $period = 'this_month'; // this_month, all_time, today, last_month, custom

    public ?string $from = null;

    public ?string $to = null;

    public string $city_id = '';

    public bool $showUserBalanceModal = false;

    public ?int $selectedUserId = null;

    public ?User $selectedUser = null;

    public float $selectedUserBalance = 0;

    /** @var array<string, mixed> */
    public array $selectedUserStats = [];

    /** @var \Illuminate\Support\Collection<int, BalanceTransaction>|array<int, mixed> */
    public Collection|array $selectedUserRecentTransactions = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => 'all'],
        'period' => ['except' => 'this_month'],
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'city_id' => ['except' => ''],
    ];

    public function mount()
    {
        if (request()->has('period')) {
            $this->period = request('period');
        }

        if (request()->has('city_id')) {
            $managed = $this->getManagedCityIds();
            $requestedCity = (int) request('city_id');
            if ($managed->isEmpty() || $managed->contains($requestedCity)) {
                $this->city_id = (string) $requestedCity;
            }
        }

        $this->applyPeriodDates($this->period);
    }

    protected function applyPeriodDates(string $val): void
    {
        if ($val === 'this_month') {
            $this->from = now()->startOfMonth()->format('Y-m-d');
            $this->to = now()->format('Y-m-d');
        } elseif ($val === 'all_time') {
            $this->from = null;
            $this->to = null;
        } elseif ($val === 'today') {
            $this->from = now()->format('Y-m-d');
            $this->to = now()->format('Y-m-d');
        } elseif ($val === 'last_month') {
            $this->from = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $this->to = now()->subMonth()->endOfMonth()->format('Y-m-d');
        } elseif ($val === 'custom') {
            $this->from = $this->from ?: now()->startOfMonth()->format('Y-m-d');
            $this->to = $this->to ?: now()->format('Y-m-d');
        }
    }

    /**
     * Get city IDs managed by the current Admin Wilayah user.
     */
    protected function getManagedCityIds(): Collection
    {
        $user = auth()->user();
        if (! $user) {
            return collect();
        }

        if (in_array($user->role, ['super_admin', 'superadmin'], true)) {
            return collect(); // Empty means unrestricted for superadmin fallback
        }

        $ids = collect($user->getAdminCityIds())
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        return $ids;
    }

    /**
     * Enforce city scoping for Admin Wilayah (returns allowed city IDs, or null if unrestricted superadmin).
     */
    protected function resolveEffectiveCityFilter(): ?Collection
    {
        $user = auth()->user();
        $isSuperAdmin = $user && in_array($user->role, ['super_admin', 'superadmin'], true);

        if ($isSuperAdmin) {
            return $this->city_id ? collect([(int) $this->city_id]) : null;
        }

        $managed = $this->getManagedCityIds();
        if ($managed->isEmpty()) {
            return collect([-1]); // Unassigned admin sees nothing
        }

        if ($this->city_id !== '' && $this->city_id !== null) {
            $selected = (int) $this->city_id;
            if ($managed->contains($selected)) {
                return collect([$selected]);
            }
        }

        return $managed;
    }

    public function updatedCityId(mixed $val): void
    {
        $user = auth()->user();
        if ($user && ! in_array($user->role, ['super_admin', 'superadmin'], true) && $val !== '' && $val !== null) {
            if (! $this->getManagedCityIds()->contains((int) $val)) {
                $this->city_id = '';
            }
        }
        $this->resetPage();
    }

    public function updatedPeriod(mixed $val): void
    {
        $this->resetPage();
        $this->applyPeriodDates((string) $val);
    }

    public function updatedFrom()
    {
        $this->period = 'custom';
        $this->resetPage();
    }

    public function updatedTo()
    {
        $this->period = 'custom';
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->type = 'all';
        $this->period = 'this_month';
        $this->city_id = '';
        $this->applyPeriodDates('this_month');
        $this->resetPage();
    }

    private function getBaseQuery()
    {
        $query = BalanceTransaction::query();

        if ($this->type && $this->type !== 'all') {
            if ($this->type === 'withdraw') {
                $query->where(function ($q) {
                    $q->where('type', 'withdraw')
                        ->orWhere(function ($sub) {
                            $sub->whereIn('type', ['deduction', 'withdraw_deduction'])
                                ->whereHas('user', fn ($u) => $u->where('role', 'mitra'));
                        });
                });
            } elseif ($this->type === 'mitra_income') {
                $query->whereIn('type', ['topup', 'income'])
                    ->whereHas('user', fn ($u) => $u->where('role', 'mitra'));
            } elseif ($this->type === 'topup') {
                $query->where('type', 'topup')
                    ->whereHas('user', fn ($u) => $u->where('role', '!=', 'mitra'));
            } elseif ($this->type === 'deduction') {
                $query->where('type', 'deduction')
                    ->whereHas('user', fn ($u) => $u->where('role', '!=', 'mitra'));
            } elseif ($this->type === 'other') {
                $query->whereNotIn('type', ['topup', 'withdraw', 'deduction']);
            } else {
                $query->where('type', $this->type);
            }
        }

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('request_code', 'like', "%{$s}%")
                    ->orWhere('order_id', 'like', "%{$s}%")
                    ->orWhere('reference_id', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($qu) use ($s) {
                        $qu->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
                    });
            });
        }

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        $effectiveCities = $this->resolveEffectiveCityFilter();
        if ($effectiveCities !== null) {
            $cityIds = $effectiveCities->all();
            $query->whereHas('user', function ($q) use ($cityIds) {
                $q->whereIn('city_id', $cityIds);
            });
        }

        return $query;
    }

    public function exportExcel()
    {
        $effectiveCities = $this->resolveEffectiveCityFilter();
        $cityName = 'Wilayah-Binaan';
        if ($effectiveCities && $effectiveCities->count() === 1 && $effectiveCities->first() > 0) {
            $city = City::find($effectiveCities->first());
            if ($city) {
                $cityName = Str::slug($city->name);
            }
        }

        $transactions = $this->getBaseQuery()->with(['user.city'])->orderBy('created_at', 'desc')->get();
        $filename = 'Log-Transaksi-Wilayah-'.$cityName.'-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($transactions, $cityName) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<style>
                body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #111827; }
                table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
                th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; border: 1px solid #0f172a; padding: 8px 10px; text-align: center; vertical-align: middle; }
                td { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: middle; }
                .text-center { text-align: center; }
                .text-left { text-align: left; }
                .text-right { text-align: right; }
                .font-bold { font-weight: bold; }
                .bg-total { background-color: #f1f5f9; font-weight: bold; }
                .title-main { font-size: 16pt; font-weight: bold; text-align: center; color: #1e3a8a; }
                .title-sub { font-size: 10pt; color: #64748b; text-align: center; }
            </style></head><body>';

            echo '<table>';
            echo '<tr><td colspan="9" class="title-main" style="border:none; padding-top:10px; padding-bottom:4px;">LAPORAN TRANSAKSI WILAYAH ('.strtoupper(str_replace('-', ' ', $cityName)).')</td></tr>';
            echo '<tr><td colspan="9" class="title-sub" style="border:none; padding-bottom:15px;">Tanggal Cetak: '.now()->translatedFormat('d F Y, H:i').' WIB | Oleh: '.htmlspecialchars(auth()->user()->name ?? 'Admin Wilayah').'</td></tr>';
            echo '</table>';

            echo '<table><thead><tr>';
            echo '<th>No</th><th>Waktu</th><th>Nama User</th><th>Email</th><th>Wilayah / Kota</th><th>Tipe</th><th>Keterangan</th><th>Jumlah (Rp)</th><th>Ref &amp; Status</th>';
            echo '</tr></thead><tbody>';

            $no = 1;
            $total = 0;

            foreach ($transactions as $t) {
                $userRole = optional($t->user)->role;
                if ($t->type === 'withdraw' || ($userRole === 'mitra' && in_array($t->type, ['deduction', 'withdraw_deduction']))) {
                    $label = 'Withdraw';
                } elseif ($userRole === 'mitra' && ($t->type === 'topup' || $t->type === 'income')) {
                    $label = 'Pendapatan Mitra';
                } elseif ($t->type === 'topup' || $t->type === 'deposit') {
                    $label = 'Top Up';
                } elseif ($t->type === 'deduction') {
                    $label = 'Pembayaran Bantuan';
                } else {
                    $label = ucfirst($t->type);
                }

                $total += (float) $t->amount;
                $refCode = $t->request_code ?? $t->order_id ?? $t->reference_id ?? $t->reference ?? '-';
                $userCity = $t->user?->city_name ?? ($t->user?->city ?? '-');

                echo '<tr>';
                echo '<td class="text-center">'.$no++.'</td>';
                echo '<td class="text-center">'.($t->created_at ? $t->created_at->format('d/m/Y H:i') : '-').'</td>';
                echo '<td class="text-left">'.htmlspecialchars(optional($t->user)->name ?? '-').'</td>';
                echo '<td class="text-left">'.htmlspecialchars(optional($t->user)->email ?? '-').'</td>';
                echo '<td class="text-center">'.htmlspecialchars($userCity).'</td>';
                echo '<td class="text-center">'.$label.'</td>';
                echo '<td class="text-left">'.htmlspecialchars((string) ($t->description ?? '-')).'</td>';
                echo '<td class="text-right font-bold">'.number_format($t->amount, 0, ',', '.').'</td>';
                echo '<td class="text-center">'.htmlspecialchars($refCode.' ('.($t->status ?? 'completed').')').'</td>';
                echo '</tr>';
            }

            echo '<tr>';
            echo '<td colspan="7" class="text-right font-bold bg-total">TOTAL KESELURUHAN</td>';
            echo '<td class="text-right font-bold bg-total">Rp '.number_format($total, 0, ',', '.').'</td>';
            echo '<td class="bg-total"></td>';
            echo '</tr></tbody></table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function viewUserBalance(int|string $userId): void
    {
        $user = User::with(['city'])->find($userId);
        if (! $user) {
            return;
        }

        $admin = auth()->user();
        if ($admin && ! in_array($admin->role, ['super_admin', 'superadmin'], true)) {
            $managed = $this->getManagedCityIds();
            if ($managed->isEmpty() || ! $managed->contains((int) $user->city_id)) {
                return;
            }
        }

        $this->selectedUserId = $user->id;
        $this->selectedUser = $user;
        $this->selectedUserBalance = (float) $user->balance;

        $userTxQuery = BalanceTransaction::where('user_id', $user->id);
        $isMitra = $user->role === 'mitra';

        if ($isMitra) {
            $totalIncome = (clone $userTxQuery)->whereIn('type', ['income', 'topup'])->where('status', 'completed')->sum('amount');
            $withdrawTx = (clone $userTxQuery)->whereIn('type', ['withdraw', 'withdraw_deduction', 'deduction'])->where('status', 'completed')->sum('amount');
            $withdrawReq = WithdrawRequest::where('user_id', $user->id)->whereIn('status', ['success', 'completed', 'approved'])->sum('amount');

            $this->selectedUserStats = [
                'is_mitra' => true,
                'total_income' => $totalIncome,
                'total_withdraw' => max((float) $withdrawTx, (float) $withdrawReq),
                'total_tx' => (clone $userTxQuery)->count(),
            ];
        } else {
            $this->selectedUserStats = [
                'is_mitra' => false,
                'total_topup' => (clone $userTxQuery)->where('type', 'topup')->where('status', 'completed')->sum('amount'),
                'total_payment' => (clone $userTxQuery)->where('type', 'deduction')->where('status', 'completed')->sum('amount'),
                'total_tx' => (clone $userTxQuery)->count(),
            ];
        }

        $this->selectedUserRecentTransactions = (clone $userTxQuery)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $this->showUserBalanceModal = true;
    }

    public function closeUserBalanceModal()
    {
        $this->showUserBalanceModal = false;
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->selectedUserStats = [];
        $this->selectedUserRecentTransactions = [];
    }

    public function render()
    {
        $query = $this->getBaseQuery()->with(['user.city']);
        $transactions = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        $summaryQuery = $this->getBaseQuery();

        $totalTopup = $summaryQuery->clone()
            ->where('type', 'topup')
            ->whereHas('user', fn ($q) => $q->where('role', '!=', 'mitra'))
            ->where('status', 'completed')
            ->sum('amount');

        $totalMitraPayout = $summaryQuery->clone()
            ->whereIn('type', ['topup', 'income'])
            ->whereHas('user', fn ($q) => $q->where('role', 'mitra'))
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawTx = $summaryQuery->clone()
            ->where('type', 'withdraw')
            ->where('status', 'completed')
            ->sum('amount');

        $effectiveCities = $this->resolveEffectiveCityFilter();
        $withdrawReqQuery = WithdrawRequest::whereIn('status', ['success', 'completed', 'approved', 'pending', 'processing']);
        if ($this->from) {
            $withdrawReqQuery->whereDate('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $withdrawReqQuery->whereDate('created_at', '<=', $this->to);
        }
        if ($effectiveCities !== null) {
            $cityIds = $effectiveCities->all();
            $withdrawReqQuery->whereHas('user', fn ($q) => $q->whereIn('city_id', $cityIds));
        }
        $totalWithdraw = max((float) $totalWithdrawTx, (float) $withdrawReqQuery->sum('amount'));

        $totalTransactions = $summaryQuery->clone()->count();

        $managedCityIds = $this->getManagedCityIds();
        $cities = $managedCityIds->isNotEmpty()
            ? City::whereIn('id', $managedCityIds->all())->orderBy('name')->get()
            : City::orderBy('name')->get();

        return view('admin.transactions-log', [
            'transactions' => $transactions,
            'totalTopup' => $totalTopup,
            'totalMitraPayout' => $totalMitraPayout,
            'totalWithdraw' => $totalWithdraw,
            'totalTransactions' => $totalTransactions,
            'cities' => $cities,
        ])->layout('layouts.admin', ['pageTitle' => 'Log Transaksi Wilayah']);
    }
}
