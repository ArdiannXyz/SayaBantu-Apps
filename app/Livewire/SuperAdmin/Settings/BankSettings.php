<?php

namespace App\Livewire\SuperAdmin\Settings;

use App\Models\AppSetting;
use App\Models\BankChannel;
use App\Services\ImageCompressionService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.superadmin')]
#[Title('Pengaturan Saluran Bank & E-Wallet - Super Admin')]
class BankSettings extends Component
{
    use WithFileUploads;

    public string $search = '';

    public string $categoryFilter = 'all'; // all, bank, ewallet

    // Modal controls for Bank / E-Wallet
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $category = 'bank';

    public string $account_number = '';

    public string $account_name = '';

    public bool $is_active = true;

    public int $order = 0;

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    // QRIS Settings & Upload
    public bool $qris_enabled = true;

    public string $qris_merchant_name = '';

    public $qris_image_upload = null;

    public ?string $current_qris_image = null;

    public function mount()
    {
        // Load QRIS config
        $paymentMethods = json_decode((string) AppSetting::get('topup_payment_methods', '{}'), true) ?: [];
        $this->qris_enabled = ! isset($paymentMethods['qris']['enabled']) || ! empty($paymentMethods['qris']['enabled']);
        $this->qris_merchant_name = (string) AppSetting::get('qris_merchant_name', 'SayaBantu');

        $storedQris = AppSetting::get('qris_image');
        if ($storedQris && Storage::disk('public')->exists($storedQris)) {
            $this->current_qris_image = $storedQris;
        } elseif (file_exists(public_path('images/payment/qris.png'))) {
            $this->current_qris_image = 'images/payment/qris.png';
        } else {
            $this->current_qris_image = null;
        }

        // Otomatis rapikan urutan saluran transaksi
        $this->resequenceOrders();
    }

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:30|unique:bank_channels,code,' . $this->editingId,
            'name' => 'required|string|max:100',
            'category' => 'required|in:bank,ewallet',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ];
    }

    protected $messages = [
        'code.required' => 'Kode bank wajib diisi (contoh: BCA, DANA).',
        'code.unique' => 'Kode bank ini sudah terdaftar.',
        'name.required' => 'Nama lengkap bank / e-wallet wajib diisi.',
    ];

    public function openAddModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->editingId = null;
        // Otomatis urutkan tanpa perlu isi manual
        $this->order = ((int) BankChannel::max('order')) + 1;
        $this->showModal = true;
    }

    public function openEditModal(int $id)
    {
        $this->resetValidation();
        $bank = BankChannel::findOrFail($id);
        $this->editingId = $bank->id;
        $this->code = $bank->code;
        $this->name = $bank->name;
        $this->category = $bank->category;
        $this->account_number = (string) ($bank->account_number ?? '');
        $this->account_name = (string) ($bank->account_name ?? '');
        $this->is_active = (bool) $bank->is_active;
        $this->order = (int) $bank->order;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->code = '';
        $this->name = '';
        $this->category = 'bank';
        $this->account_number = '';
        $this->account_name = '';
        $this->is_active = true;
        $this->order = 0;
    }

    public function save()
    {
        $this->code = strtoupper(trim($this->code));
        $this->name = trim($this->name);
        $this->account_number = trim($this->account_number);
        $this->account_name = trim($this->account_name);
        $this->validate();

        if (! $this->isEditing || ! $this->order || $this->order <= 0) {
            $this->order = ((int) BankChannel::max('order')) + 1;
        }

        if ($this->isEditing && $this->editingId) {
            $bank = BankChannel::findOrFail($this->editingId);
            $bank->update([
                'code' => $this->code,
                'name' => $this->name,
                'category' => $this->category,
                'account_number' => $this->account_number ?: null,
                'account_name' => $this->account_name ?: null,
                'is_active' => $this->is_active,
                'order' => $this->order,
            ]);
            session()->flash('success', "Saluran '{$this->name}' berhasil diperbarui.");
        } else {
            BankChannel::create([
                'code' => $this->code,
                'name' => $this->name,
                'category' => $this->category,
                'account_number' => $this->account_number ?: null,
                'account_name' => $this->account_name ?: null,
                'is_active' => $this->is_active,
                'order' => $this->order,
            ]);
            session()->flash('success', "Saluran '{$this->name}' berhasil ditambahkan ke daftar transaksi.");
        }

        $this->resequenceOrders();
        $this->syncTopupPaymentMethods();
        $this->closeModal();
    }

    public function toggleStatus(int $id)
    {
        $bank = BankChannel::findOrFail($id);
        $bank->is_active = ! $bank->is_active;
        $bank->save();

        $this->syncTopupPaymentMethods();

        $statusText = $bank->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Status '{$bank->name}' berhasil {$statusText}.");
    }

    public function moveUp(int $id)
    {
        $current = BankChannel::findOrFail($id);
        $previous = BankChannel::where('order', '<', $current->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previous) {
            $prevOrder = $previous->order;
            $previous->order = $current->order;
            $current->order = $prevOrder;
            $current->saveQuietly();
            $previous->saveQuietly();
            $this->resequenceOrders();
            $this->syncTopupPaymentMethods();
        }
    }

    public function moveDown(int $id)
    {
        $current = BankChannel::findOrFail($id);
        $next = BankChannel::where('order', '>', $current->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($next) {
            $nextOrder = $next->order;
            $next->order = $current->order;
            $current->order = $nextOrder;
            $current->saveQuietly();
            $next->saveQuietly();
            $this->resequenceOrders();
            $this->syncTopupPaymentMethods();
        }
    }

    public function resequenceOrders(): void
    {
        $channels = BankChannel::orderBy('order', 'asc')->orderBy('id', 'asc')->get();
        foreach ($channels as $index => $channel) {
            $expected = $index + 1;
            if ($channel->order !== $expected) {
                $channel->order = $expected;
                $channel->saveQuietly();
            }
        }
    }

    public function confirmDelete(int $id)
    {
        $bank = BankChannel::findOrFail($id);
        $this->deletingId = $bank->id;
        $this->deletingName = $bank->name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
    }

    public function delete()
    {
        if ($this->deletingId) {
            $bank = BankChannel::find($this->deletingId);
            if ($bank) {
                $name = $bank->name;
                $bank->delete();
                $this->resequenceOrders();
                $this->syncTopupPaymentMethods();
                session()->flash('success', "Saluran '{$name}' telah dihapus dari daftar.");
            }
        }

        $this->cancelDelete();
    }

    // ==========================================
    // METODE PEMBAYARAN QRIS
    // ==========================================

    public function saveQrisSettings()
    {
        $this->validate([
            'qris_merchant_name' => 'nullable|string|max:100',
            'qris_image_upload' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
        ], [
            'qris_image_upload.image' => 'File harus berupa gambar.',
            'qris_image_upload.mimes' => 'Format gambar harus JPEG, PNG, JPG, WEBP, atau SVG.',
            'qris_image_upload.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        if ($this->qris_image_upload) {
            $path = ImageCompressionService::compressAndStore($this->qris_image_upload, 'payment');
            @copy(storage_path('app/public/'.$path), public_path('images/payment/qris.png'));

            if ($this->current_qris_image && $this->current_qris_image !== 'images/payment/qris.png' && Storage::disk('public')->exists($this->current_qris_image)) {
                Storage::disk('public')->delete($this->current_qris_image);
            }

            $this->current_qris_image = $path;
            AppSetting::set('qris_image', $path);
            $this->qris_image_upload = null;
        }

        AppSetting::set('qris_merchant_name', trim($this->qris_merchant_name));

        $this->syncTopupPaymentMethods();

        session()->flash('success', 'Pengaturan QRIS berhasil disimpan.');
    }

    public function toggleQrisStatus()
    {
        $this->qris_enabled = ! $this->qris_enabled;
        $this->syncTopupPaymentMethods();

        $statusText = $this->qris_enabled ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Metode pembayaran QRIS berhasil {$statusText}.");
    }

    public function removeQrisImage()
    {
        if ($this->current_qris_image && $this->current_qris_image !== 'images/payment/qris.png') {
            if (Storage::disk('public')->exists($this->current_qris_image)) {
                Storage::disk('public')->delete($this->current_qris_image);
            }
        }

        if (file_exists(public_path('images/payment/qris.png'))) {
            @unlink(public_path('images/payment/qris.png'));
        }

        $this->current_qris_image = null;
        $this->qris_image_upload = null;
        AppSetting::forget('qris_image');

        $this->syncTopupPaymentMethods();

        session()->flash('success', 'Gambar barcode QRIS berhasil dihapus.');
    }

    /**
     * Sinkronisasi saluran aktif ke format topup_payment_methods di AppSetting
     * agar pelanggan yang top-up & buat order langsung mendapatkan daftar rekening & QRIS terbaru.
     */
    public function syncTopupPaymentMethods(): void
    {
        $activeChannels = BankChannel::where('is_active', true)
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $banksList = [];
        foreach ($activeChannels as $channel) {
            $banksList[] = [
                'code' => strtolower($channel->code),
                'name' => $channel->name,
                'account_number' => $channel->account_number,
                'account_name' => $channel->account_name ?: 'PT SayaBantu Indonesia',
                'category' => $channel->category,
                'enabled' => (bool) $channel->is_active,
            ];
        }

        // Jika belum ada saluran di bank_channels dengan account_number, pertahankan yang lama
        if (empty($banksList)) {
            $raw = AppSetting::get('topup_payment_methods', '{}');
            $methods = json_decode((string) $raw, true) ?: [];
            $banksList = $methods['banks'] ?? [];
        }

        $payload = [
            'qris' => [
                'enabled' => (bool) $this->qris_enabled,
                'merchant_name' => trim($this->qris_merchant_name ?: (string) AppSetting::get('qris_merchant_name', 'SayaBantu')),
            ],
            'banks' => $banksList,
        ];

        AppSetting::set('topup_payment_methods', json_encode($payload));
    }

    public function render()
    {
        $query = BankChannel::query();

        if (! empty($this->search)) {
            $q = trim($this->search);
            $query->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('account_number', 'like', "%{$q}%")
                    ->orWhere('account_name', 'like', "%{$q}%");
            });
        }

        if ($this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }

        $channels = $query->orderBy('order')->orderBy('name')->get();

        $activeCount = BankChannel::where('is_active', true)->count();
        $inactiveCount = BankChannel::where('is_active', false)->count();
        $banksCount = BankChannel::where('category', 'bank')->count();
        $ewalletCount = BankChannel::where('category', 'ewallet')->count();

        // QRIS image preview URL
        $qrisPreviewUrl = null;
        if ($this->qris_image_upload) {
            try {
                $qrisPreviewUrl = $this->qris_image_upload->temporaryUrl();
            } catch (\Throwable $e) {
                $qrisPreviewUrl = null;
            }
        } elseif ($this->current_qris_image) {
            $qrisPreviewUrl = ($this->current_qris_image === 'images/payment/qris.png')
                ? asset('images/payment/qris.png')
                : asset('storage/' . $this->current_qris_image);
        }

        return view('livewire.super-admin.settings.bank-settings', [
            'channels' => $channels,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'banksCount' => $banksCount,
            'ewalletCount' => $ewalletCount,
            'totalCount' => BankChannel::count(),
            'qrisPreviewUrl' => $qrisPreviewUrl,
        ]);
    }
}
