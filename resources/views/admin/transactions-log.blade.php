<div>
    <style>
        @page { size: A4 portrait; margin: 0; }
        @media print {
            aside, nav, header, footer, .no-print, .summary-cards-section, .filter-form-section, .pagination-container, .export-btn-group, button {
                display: none !important;
            }
            html, body {
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 8mm 6mm !important;
                background: white !important;
                color: #111827 !important;
                font-size: 10px !important;
            }
            main, .min-h-screen { min-height: 0 !important; height: auto !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .main-table-card { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; }
            .print-header { display: block !important; text-align: center !important; margin-bottom: 12px !important; padding-bottom: 8px !important; border-bottom: 2px solid #000 !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #cbd5e1 !important; padding: 5px 4px !important; font-size: 9.5px !important; }
            th { background-color: #f3f4f6 !important; font-weight: bold !important; }
        }
        .print-header { display: none; }
    </style>

    <div class="print-header">
        @php
            $currentCityLabel = $cities->count() === 1 ? strtoupper($cities->first()->name) : 'WILAYAH BINAAN';
            if (!empty($city_id)) {
                $foundCity = $cities->firstWhere('id', (int) $city_id);
                if ($foundCity) $currentCityLabel = strtoupper($foundCity->name);
            }
        @endphp
        <h1 class="text-xl font-black uppercase">LAPORAN LOG TRANSAKSI ({{ $currentCityLabel }})</h1>
        <p class="text-xs text-gray-600 mt-1">
            Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB | Admin: {{ auth()->user()->name ?? 'Admin Wilayah' }}
            | Periode: {{ $from && $to ? \Carbon\Carbon::parse($from)->translatedFormat('d M Y') . ' s/d ' . \Carbon\Carbon::parse($to)->translatedFormat('d M Y') : 'Semua Waktu' }}
        </p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards-section grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-50/80 via-white to-blue-50/30 rounded-2xl p-4 sm:p-5 border border-blue-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-blue-900/70 uppercase tracking-wide">Top Up Customer</p>
                <h3 class="text-lg sm:text-xl font-black text-blue-700 mt-0.5">{{ format_rupiah($totalTopup) }}</h3>
            </div>
            <div class="w-11 h-11 bg-blue-100 text-[#0066FF] border border-blue-200/80 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50/80 via-white to-emerald-50/30 rounded-2xl p-4 sm:p-5 border border-emerald-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-emerald-900/70 uppercase tracking-wide">Pendapatan Mitra</p>
                <h3 class="text-lg sm:text-xl font-black text-emerald-700 mt-0.5">{{ format_rupiah($totalMitraPayout) }}</h3>
            </div>
            <div class="w-11 h-11 bg-emerald-100 text-emerald-600 border border-emerald-200/80 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-rose-50/80 via-white to-rose-50/30 rounded-2xl p-4 sm:p-5 border border-rose-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-rose-900/70 uppercase tracking-wide">Total Withdraw</p>
                <h3 class="text-lg sm:text-xl font-black text-rose-700 mt-0.5">{{ format_rupiah($totalWithdraw) }}</h3>
            </div>
            <div class="w-11 h-11 bg-rose-100 text-rose-600 border border-rose-200/80 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-slate-50/80 via-white to-slate-50/30 rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-600 uppercase tracking-wide">Jumlah Mutasi</p>
                <h3 class="text-lg sm:text-xl font-black text-gray-900 mt-0.5">{{ number_format($totalTransactions) }}</h3>
            </div>
            <div class="w-11 h-11 bg-slate-100 text-slate-600 border border-slate-200/80 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="main-table-card bg-white rounded-2xl shadow">
        <div class="p-6 border-b border-gray-100 no-print">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-800">Log Transaksi Saldo Wilayah</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pantau mutasi top up, pembayaran bantuan, pencairan otomatis 24 jam ke mitra, dan withdraw di wilayah Anda.</p>
                </div>
                <div class="export-btn-group flex flex-wrap items-center gap-2">
                    <button wire:click="resetFilters" type="button" class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-xs sm:text-sm font-medium rounded-lg transition-colors cursor-pointer shadow-2xs">
                        Reset
                    </button>
                    <button wire:click="exportExcel" type="button" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors cursor-pointer shadow-2xs">
                        Excel
                    </button>
                    <button type="button" onclick="window.print()" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors cursor-pointer shadow-2xs">
                        PDF
                    </button>
                </div>
            </div>

            <div class="filter-form-section flex flex-wrap items-center gap-2">
                <div class="flex-1 min-w-[180px]">
                    <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama, email, ref, atau Auto-confirm..."
                        class="border border-gray-300 rounded-lg px-3 py-1.5 h-9 w-full focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-xs sm:text-sm" />
                </div>

                @if($cities->count() > 1)
                    <select wire:model.live="city_id" class="border border-gray-300 rounded-lg pl-2.5 pr-9 py-1.5 h-9 bg-white text-xs sm:text-sm cursor-pointer font-medium text-gray-700">
                        <option value="">Semua Wilayah Binaan</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                @endif

                <select wire:model.live="type" class="border border-gray-300 rounded-lg pl-2.5 pr-9 py-1.5 h-9 bg-white text-xs sm:text-sm cursor-pointer font-medium text-gray-700">
                    <option value="all">Semua Tipe</option>
                    <option value="topup">Top Up Customer</option>
                    <option value="mitra_income">Pendapatan Mitra (Selesai / Auto-Confirm)</option>
                    <option value="deduction">Pembayaran Bantuan</option>
                    <option value="withdraw">Withdraw</option>
                    <option value="other">Lainnya</option>
                </select>

                <select wire:model.live="period" class="border border-gray-300 rounded-lg pl-2.5 pr-9 py-1.5 h-9 bg-white text-xs sm:text-sm cursor-pointer font-medium text-gray-700">
                    <option value="this_month">Bulan Ini</option>
                    <option value="all_time">Keseluruhan Transaksi</option>
                    <option value="today">Hari Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                    <option value="custom">Pilih Rentang Tanggal</option>
                </select>

                <select wire:model.live="perPage" class="border border-gray-300 rounded-lg pl-2.5 pr-9 py-1.5 h-9 bg-white text-xs sm:text-sm cursor-pointer font-medium text-gray-700">
                    <option value="10">10 / halaman</option>
                    <option value="15">15 / halaman</option>
                    <option value="30">30 / halaman</option>
                    <option value="50">50 / halaman</option>
                </select>
            </div>

            @if($period === 'custom' || ($from && $to && !in_array($period, ['this_month', 'all_time', 'today', 'last_month'])))
                <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-2.5 bg-slate-50/70 p-2.5 rounded-lg border border-dashed border-slate-200">
                    <span class="text-xs font-semibold text-slate-600">Rentang Tanggal:</span>
                    <div class="flex items-center gap-1.5">
                        <input wire:model.live="from" type="date" max="{{ date('Y-m-d') }}" class="border border-gray-300 rounded-md px-3 py-1.5 bg-white text-xs" />
                        <span class="text-gray-400 text-xs font-semibold">s/d</span>
                        <input wire:model.live="to" type="date" max="{{ date('Y-m-d') }}" class="border border-gray-300 rounded-md px-3 py-1.5 bg-white text-xs" />
                    </div>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Waktu</th>
                        <th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">User</th>
                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Wilayah</th>
                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Tipe</th>
                        <th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Keterangan &amp; Ref</th>
                        <th class="px-3 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Jumlah</th>
                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider no-print">Saldo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($transactions as $t)
                        @php
                            $userRole = optional($t->user)->role;
                            if ($t->type === 'withdraw' || ($userRole === 'mitra' && in_array($t->type, ['deduction', 'withdraw_deduction']))) {
                                $label = 'Withdraw';
                                $badgeStyle = 'bg-amber-50 text-amber-700 border border-amber-200';
                            } elseif ($userRole === 'mitra' && ($t->type === 'topup' || $t->type === 'income')) {
                                $label = 'Pendapatan Mitra';
                                $badgeStyle = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                            } elseif ($t->type === 'topup' || $t->type === 'deposit') {
                                $label = 'Top Up';
                                $badgeStyle = 'bg-blue-50 text-blue-700 border border-blue-100';
                            } elseif ($t->type === 'deduction') {
                                $label = 'Bantuan';
                                $badgeStyle = 'bg-rose-50 text-rose-700 border border-rose-100';
                            } else {
                                $label = ucfirst($t->type);
                                $badgeStyle = 'bg-gray-50 text-gray-700 border border-gray-200';
                            }
                            $refCode = $t->request_code ?? $t->order_id ?? $t->reference_id ?? $t->reference ?? '-';
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-3 py-2.5 text-xs text-center text-gray-500 whitespace-nowrap">
                                {{ ($transactions->currentPage() - 1) * $transactions->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-3 py-2.5 text-xs text-gray-600 whitespace-nowrap">
                                <div class="font-medium text-gray-800">{{ $t->created_at ? \Carbon\Carbon::parse($t->created_at)->translatedFormat('d M Y') : '-' }}</div>
                                <div class="text-[10px] text-gray-400">{{ $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('H:i') . ' WIB' : '' }}</div>
                            </td>
                            <td class="px-3 py-2.5">
                                @if($t->user)
                                    <div class="text-xs font-semibold text-gray-900">{{ $t->user->name }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $t->user->email }}</div>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="text-[11px] font-medium text-gray-700 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded">
                                    {{ $t->user?->city_name ?? ($t->user?->city ?? '-') }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $badgeStyle }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 max-w-[240px]">
                                <div class="text-xs text-gray-700 truncate" title="{{ $t->description ?? '-' }}">{{ $t->description ?: '-' }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">Ref: {{ $refCode }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-xs text-right font-bold text-gray-900 whitespace-nowrap">
                                {{ format_rupiah($t->amount) }}
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-green-50 text-green-700 border border-green-200">
                                    {{ $t->status ?? 'completed' }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap no-print">
                                @if($t->user)
                                    <button type="button" wire:click="viewUserBalance({{ $t->user->id }})" wire:loading.attr="disabled"
                                        class="w-7 h-7 bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 rounded-lg text-xs font-semibold transition cursor-pointer inline-flex items-center justify-center"
                                        title="Lihat Detail Saldo ({{ $t->user->name }})">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-500 font-medium">Tidak ada transaksi di wilayah Anda</p>
                                <p class="text-xs text-gray-400 mt-1">Data mutasi transaksi sesuai filter akan tampil di sini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container px-6 py-4 border-t border-gray-100 bg-white no-print">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Modal Detail Saldo User -->
    @if ($showUserBalanceModal && $selectedUser)
        <div class="fixed inset-0 z-50 overflow-y-auto no-print">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/75 backdrop-blur-sm" wire:click="closeUserBalanceModal"></div>

                <div class="relative inline-block w-full max-w-xl p-6 my-8 overflow-hidden text-left align-middle bg-white shadow-2xl rounded-3xl border border-gray-100">
                    <div class="flex items-start justify-between pb-4 border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold text-gray-900">{{ $selectedUser->name }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $selectedUser->role === 'mitra' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                    {{ $selectedUser->role === 'mitra' ? 'Mitra Relawan' : 'Customer' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $selectedUser->email }}</p>
                        </div>
                        <button type="button" wire:click="closeUserBalanceModal" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center cursor-pointer">✕</button>
                    </div>

                    <div class="space-y-4 my-5">
                        <div class="p-5 rounded-2xl shadow-sm text-white" style="background: {{ $selectedUser->role === 'mitra' ? 'linear-gradient(135deg, #059669 0%, #0d9488 100%)' : 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)' }};">
                            <span class="text-xs font-semibold text-white/90 uppercase tracking-wider">Saldo Aktif Saat Ini</span>
                            <h4 class="text-3xl font-black mt-2 text-white">{{ format_rupiah($selectedUserBalance) }}</h4>
                        </div>

                        @if($selectedUser->role === 'mitra')
                            <div class="grid grid-cols-3 gap-3">
                                <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-100 text-center">
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase block">Pendapatan</span>
                                    <div class="text-xs sm:text-sm font-black text-gray-900 mt-1">{{ format_rupiah($selectedUserStats['total_income'] ?? 0) }}</div>
                                </div>
                                <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-100 text-center">
                                    <span class="text-[10px] font-bold text-amber-700 uppercase block">Withdraw</span>
                                    <div class="text-xs sm:text-sm font-black text-gray-900 mt-1">{{ format_rupiah($selectedUserStats['total_withdraw'] ?? 0) }}</div>
                                </div>
                                <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100 text-center">
                                    <span class="text-[10px] font-bold text-blue-700 uppercase block">Transaksi</span>
                                    <div class="text-xs sm:text-sm font-black text-gray-900 mt-1">{{ number_format($selectedUserStats['total_tx'] ?? 0) }}x</div>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-3 gap-3">
                                <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-100 text-center">
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase block">Top Up</span>
                                    <div class="text-xs sm:text-sm font-black text-gray-900 mt-1">{{ format_rupiah($selectedUserStats['total_topup'] ?? 0) }}</div>
                                </div>
                                <div class="p-3.5 rounded-2xl bg-rose-50/70 border border-rose-100 text-center">
                                    <span class="text-[10px] font-bold text-rose-700 uppercase block">Pembayaran</span>
                                    <div class="text-xs sm:text-sm font-black text-gray-900 mt-1">{{ format_rupiah($selectedUserStats['total_payment'] ?? 0) }}</div>
                                </div>
                                <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100 text-center">
                                    <span class="text-[10px] font-bold text-blue-700 uppercase block">Transaksi</span>
                                    <div class="text-xs sm:text-sm font-black text-gray-900 mt-1">{{ number_format($selectedUserStats['total_tx'] ?? 0) }}x</div>
                                </div>
                            </div>
                        @endif

                        @if(count($selectedUserRecentTransactions ?? []) > 0)
                            <div class="border border-gray-100 rounded-2xl overflow-hidden">
                                <div class="px-4 py-2.5 bg-gray-50 text-xs font-bold text-gray-700 border-b border-gray-100">Mutasi Terakhir</div>
                                <div class="divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                    @foreach($selectedUserRecentTransactions as $rtx)
                                        <div class="px-4 py-2.5 flex items-center justify-between gap-2 text-xs">
                                            <div class="min-w-0">
                                                <div class="font-medium text-gray-800 truncate">{{ $rtx->description ?: ucfirst($rtx->type) }}</div>
                                                <div class="text-[10px] text-gray-400">{{ $rtx->created_at?->format('d M Y H:i') }}</div>
                                            </div>
                                            <span class="font-bold text-gray-900 shrink-0">{{ format_rupiah($rtx->amount) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
