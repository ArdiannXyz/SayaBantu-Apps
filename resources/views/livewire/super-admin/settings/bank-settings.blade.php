<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('superadmin.dashboard') }}" wire:navigate class="hover:text-[#0066FF] transition">Super Admin</a>
                <span>/</span>
                <span class="text-slate-400">Pengaturan</span>
                <span>/</span>
                <span class="text-slate-700 font-medium">Saluran Bank, E-Wallet & QRIS</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Pengaturan Bank & E-Wallet</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola rekening bank, nomor e-wallet, dan barcode QRIS untuk penerimaan transfer top-up & pembayaran pengguna.</p>
        </div>

        <div>
            <button type="button" wire:click="openAddModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0066FF] hover:bg-[#0052cc] active:scale-95 text-white rounded-xl text-xs font-bold transition shadow-md shadow-blue-500/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Bank / E-Wallet Baru</span>
            </button>
        </div>
    </div>

    <!-- Flash Message -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs flex items-start gap-3 shadow-2xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1 font-medium leading-relaxed">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- BAGIAN 1: METODE PEMBAYARAN QRIS (UPLOAD GAMBAR & PENGATURAN) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 bg-gradient-to-r from-slate-50/70 to-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-bold shadow-xs">
                    📱
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900">Metode Pembayaran QRIS</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Menerima pembayaran instan dari GoPay, OVO, DANA, BCA, ShopeePay, LinkAja, dll.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="toggleQrisStatus"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold border transition cursor-pointer {{ $qris_enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-300 hover:bg-slate-200' }}">
                    <span class="w-2 h-2 rounded-full {{ $qris_enabled ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                    <span>{{ $qris_enabled ? 'QRIS Aktif' : 'QRIS Dinonaktifkan' }}</span>
                </button>
            </div>
        </div>

        <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <!-- Kolom Preview Barcode QRIS -->
            <div class="md:col-span-4 flex flex-col items-center justify-center p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3">Tampilan Barcode QRIS</span>
                
                @if($qrisPreviewUrl)
                    <div class="relative group">
                        <img src="{{ $qrisPreviewUrl }}" 
                             alt="QRIS Barcode" 
                             class="w-48 h-48 aspect-square object-contain bg-white rounded-2xl border border-slate-200 shadow-md p-2">
                    </div>
                    @if($qris_merchant_name)
                        <p class="text-xs font-bold text-gray-900 mt-2">{{ $qris_merchant_name }}</p>
                    @endif
                    <button type="button" wire:click="removeQrisImage"
                            wire:confirm="Apakah Anda yakin ingin menghapus gambar barcode QRIS ini?"
                            class="mt-3 text-[11px] font-bold text-rose-600 hover:text-rose-700 underline cursor-pointer">
                        Hapus Gambar QRIS
                    </button>
                @else
                    <div class="w-48 h-48 aspect-square rounded-2xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center p-4 bg-white">
                        <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <p class="text-xs font-bold text-slate-600">Belum Ada Barcode</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Upload gambar barcode QRIS di formulir sebelah kanan</p>
                    </div>
                @endif
            </div>

            <!-- Kolom Formulir Pengaturan & Upload QRIS -->
            <div class="md:col-span-8 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Merchant / NMID QRIS</label>
                    <input type="text" wire:model="qris_merchant_name" 
                           placeholder="Contoh: SayaBantu / PT SayaBantu Indonesia"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 outline-none transition" />
                    <p class="text-[11px] text-slate-400 mt-1">Nama ini akan tampil di bawah barcode QRIS pada layar pelanggan.</p>
                    @error('qris_merchant_name') <span class="text-rose-600 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload File Gambar Barcode QRIS</label>
                    <div class="border-2 border-dashed border-slate-200 rounded-2xl p-4 bg-slate-50/50 hover:bg-slate-50 transition relative">
                        <input type="file" wire:model="qris_image_upload" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0066FF] file:text-white hover:file:bg-[#0052cc] file:cursor-pointer cursor-pointer" />
                        <div wire:loading wire:target="qris_image_upload" class="text-xs font-semibold text-emerald-600 mt-2 flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Mengunggah dan memproses gambar barcode...</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Format didukung: PNG, JPG, JPEG, WEBP, SVG (Maks. 5 MB). Gambar otomatis dioptimalkan.</p>
                    @error('qris_image_upload') <span class="text-rose-600 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button type="button" wire:click="saveQrisSettings"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0066FF] hover:bg-[#0052cc] active:scale-95 text-white rounded-xl text-xs font-bold transition shadow-md shadow-blue-500/20 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Pengaturan QRIS</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Saluran</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-black text-gray-900">{{ $totalCount }}</span>
                <span class="text-xs text-slate-400">bank & e-wallet</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-blue-200/80 bg-blue-50/20 p-4 shadow-xs">
            <span class="text-[11px] font-bold text-blue-800 uppercase tracking-wider">🏦 Bank Transfer</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-black text-[#0066FF]">{{ $banksCount }}</span>
                <span class="text-xs text-blue-600">terdaftar</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-purple-200/80 bg-purple-50/20 p-4 shadow-xs">
            <span class="text-[11px] font-bold text-purple-800 uppercase tracking-wider">📱 Dompet Digital (E-Wallet)</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-black text-purple-700">{{ $ewalletCount }}</span>
                <span class="text-xs text-purple-600">terdaftar</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-200/80 bg-emerald-50/20 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Status Aktif (Tampil)</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-black text-emerald-700">{{ $activeCount }}</span>
                <span class="text-xs text-emerald-600">aktif digunakan</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama, kode, nomor rekening / HP..."
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 transition outline-none" />
        </div>

        <!-- Category Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-bold w-full sm:w-auto">
            <button type="button" wire:click="$set('categoryFilter', 'all')"
                    class="flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg transition {{ $categoryFilter === 'all' ? 'bg-white text-gray-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                Semua ({{ $totalCount }})
            </button>
            <button type="button" wire:click="$set('categoryFilter', 'bank')"
                    class="flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg transition {{ $categoryFilter === 'bank' ? 'bg-white text-[#0066FF] shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                🏦 Bank
            </button>
            <button type="button" wire:click="$set('categoryFilter', 'ewallet')"
                    class="flex-1 sm:flex-none px-3.5 py-1.5 rounded-lg transition {{ $categoryFilter === 'ewallet' ? 'bg-white text-purple-700 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                📱 E-Wallet
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 2: DAFTAR REKENING BANK & NOMOR E-WALLET -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/75 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-5">Kode</th>
                        <th class="py-3 px-4">Nama Saluran</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Nomor Rekening / No. HP</th>
                        <th class="py-3 px-4">Atas Nama (a.n.)</th>
                        <th class="py-3 px-4 text-center">No.</th>
                        <th class="py-3 px-4 text-center">Status Aktif</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($channels as $channel)
                        <tr class="hover:bg-slate-50/60 transition {{ !$channel->is_active ? 'opacity-60 bg-slate-50/30' : '' }}">
                            <!-- Code Badge -->
                            <td class="py-3.5 px-5">
                                <span class="font-mono font-black text-xs px-2.5 py-1 rounded-lg {{ $channel->is_active ? 'bg-blue-50 text-[#0066FF] border border-blue-200/80' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $channel->code }}
                                </span>
                            </td>

                            <!-- Name -->
                            <td class="py-3.5 px-4 font-bold text-gray-900 text-sm">
                                <div class="flex items-center gap-2">
                                    <span>{{ $channel->category === 'ewallet' ? '📱' : '🏦' }}</span>
                                    <span>{{ $channel->name }}</span>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="py-3.5 px-4">
                                @if($channel->category === 'ewallet')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        E-Wallet
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Bank Transfer
                                    </span>
                                @endif
                            </td>

                            <!-- Account Number -->
                            <td class="py-3.5 px-4">
                                @if($channel->account_number)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-gray-900 text-xs tracking-wider bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                            {{ $channel->account_number }}
                                        </span>
                                        <button type="button" 
                                                onclick="navigator.clipboard.writeText('{{ $channel->account_number }}'); if (window.sbToast) { window.sbToast('Nomor disalin: {{ $channel->account_number }}', 'success'); }"
                                                class="text-slate-400 hover:text-[#0066FF] transition p-1" title="Salin nomor">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="inline-flex items-center text-[11px] font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                                        Belum diisi
                                    </span>
                                @endif
                            </td>

                            <!-- Account Name (Atas Nama) -->
                            <td class="py-3.5 px-4 font-medium text-gray-700">
                                {{ $channel->account_name ?: '-' }}
                            </td>

                            <!-- Auto No. & Quick Move -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <span class="font-mono font-bold text-xs bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                                        {{ $loop->iteration }}
                                    </span>
                                    <div class="inline-flex flex-col -space-y-0.5">
                                        <button type="button" wire:click="moveUp({{ $channel->id }})" 
                                                class="text-slate-400 hover:text-[#0066FF] p-0.5 rounded transition cursor-pointer {{ $loop->first ? 'opacity-20 pointer-events-none' : '' }}" 
                                                title="Pindah ke atas">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                        <button type="button" wire:click="moveDown({{ $channel->id }})" 
                                                class="text-slate-400 hover:text-[#0066FF] p-0.5 rounded transition cursor-pointer {{ $loop->last ? 'opacity-20 pointer-events-none' : '' }}" 
                                                title="Pindah ke bawah">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </td>

                            <!-- Toggle Status Switch -->
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $channel->id }})"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $channel->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"
                                        title="{{ $channel->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ $channel->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                                <div class="text-[10px] font-bold mt-1 {{ $channel->is_active ? 'text-emerald-700' : 'text-slate-400' }}">
                                    {{ $channel->is_active ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" wire:click="openEditModal({{ $channel->id }})"
                                            class="p-2 rounded-xl text-slate-500 hover:text-[#0066FF] hover:bg-blue-50 transition cursor-pointer"
                                            title="Edit saluran & nomor rekening">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="confirmDelete({{ $channel->id }})"
                                            class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                            title="Hapus saluran">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                <div class="text-3xl mb-2">🔍</div>
                                <p class="font-bold text-gray-700">Tidak ada saluran bank atau e-wallet yang ditemukan</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Coba gunakan kata kunci pencarian lain atau klik tombol "Tambah Bank / E-Wallet Baru".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL FORM TAMBAH / EDIT BANK & E-WALLET -->
    <!-- ========================================================================= -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-xs p-4" x-data>
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="p-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base text-gray-900">{{ $isEditing ? 'Edit Saluran Bank / E-Wallet' : 'Tambah Bank / E-Wallet Baru' }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Atur rincian rekening untuk transfer pelanggan & mitra</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form wire:submit="save" class="p-5 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kategori Saluran</label>
                            <select wire:model.live="category"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 outline-none transition">
                                <option value="bank">🏦 Bank Transfer</option>
                                <option value="ewallet">📱 Dompet Digital</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kode Saluran</label>
                            <input type="text" wire:model="code" placeholder="Contoh: BCA / DANA"
                                   class="w-full uppercase font-mono px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 outline-none transition" />
                            @error('code') <span class="text-rose-600 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Saluran</label>
                        <input type="text" wire:model="name" placeholder="{{ $category === 'ewallet' ? 'Contoh: DANA / GoPay / OVO' : 'Contoh: BCA (Bank Central Asia)' }}"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 outline-none transition" />
                        @error('name') <span class="text-rose-600 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            {{ $category === 'ewallet' ? 'Nomor Akun / No. HP E-Wallet' : 'Nomor Rekening Bank' }}
                        </label>
                        <input type="text" wire:model="account_number" 
                               placeholder="{{ $category === 'ewallet' ? 'Contoh: 081234567890' : 'Contoh: 1234567890' }}"
                               class="w-full font-mono px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 outline-none transition" />
                        <p class="text-[11px] text-slate-400 mt-1">Nomor ini yang akan disalin oleh pelanggan saat melakukan pembayaran / top-up.</p>
                        @error('account_number') <span class="text-rose-600 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Pemilik Rekening / Akun (Atas Nama)</label>
                        <input type="text" wire:model="account_name" placeholder="Contoh: PT SayaBantu Indonesia / Kas SayaBantu"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 outline-none transition" />
                        <p class="text-[11px] text-slate-400 mt-1">Nama pemilik yang tertera di mutasi bank/e-wallet.</p>
                        @error('account_name') <span class="text-rose-600 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 text-[#0066FF] rounded border-slate-300 focus:ring-[#0066FF]" />
                            <span class="font-bold text-slate-800 text-xs">Aktifkan untuk transaksi</span>
                        </label>
                        <p class="text-[11px] text-slate-400 pl-6.5 mt-0.5">Jika dicentang, saluran ini dapat dipilih oleh pelanggan saat top-up & mitra saat penarikan.</p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 bg-[#0066FF] hover:bg-[#0052cc] text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-500/20 cursor-pointer">
                            Simpan Saluran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-xs p-4" x-data>
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-200 p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl mx-auto mb-3 shadow-2xs">
                    🗑️
                </div>
                <h3 class="font-bold text-base text-gray-900">Hapus Saluran?</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Apakah Anda yakin ingin menghapus <strong>"{{ $deletingName }}"</strong> dari daftar saluran transaksi?
                </p>
                <div class="mt-6 flex items-center gap-2.5">
                    <button type="button" wire:click="cancelDelete"
                            class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="delete"
                            class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-rose-600/20 cursor-pointer">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
