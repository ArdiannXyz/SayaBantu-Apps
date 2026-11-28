@php
    $title = 'Biaya Admin Top-Up';
    $breadcrumb = 'Super Admin / Pengaturan / Bantuan / Biaya Admin Top-Up';
@endphp

<div>
    <!-- Sub-Nav Tabs Pengaturan Bantuan -->
    <div class="mb-6 bg-white border border-slate-200/90 rounded-2xl p-1.5 shadow-2xs">
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
            <a href="{{ route('superadmin.pengaturan.bantuan.fee-pendapatan') }}" wire:navigate
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('superadmin.pengaturan.bantuan.fee-pendapatan*') ? 'bg-[#0066FF] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                <span>Fee & Pendapatan</span>
            </a>
            <a href="{{ route('superadmin.pengaturan.bantuan.biaya-topup') }}" wire:navigate
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('superadmin.pengaturan.bantuan.biaya-topup*') ? 'bg-[#0066FF] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>Biaya Admin Top-Up</span>
            </a>
            <a href="{{ route('superadmin.pengaturan.bantuan.tarif-radius') }}" wire:navigate
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('superadmin.pengaturan.bantuan.tarif-radius*') || request()->routeIs('superadmin.settings.radius*') || request()->routeIs('superadmin.settings.tarif-radius*') ? 'bg-[#0066FF] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Tarif & Radius Jangkauan Mitra</span>
            </a>
        </div>
    </div>

    <!-- Top-Up Admin Fee Stats & Chart Section -->
    <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-200 px-4 sm:px-8 py-5 bg-gray-50/80">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-medium text-gray-900">Pendapatan Biaya Admin Top-Up</h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Statistik dan grafik perolehan biaya admin dari transaksi pengisian saldo customer & mitra</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Summary Cards (4 Cards) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-6 sm:mb-8" style="gap: 16px;">
                    <!-- 1. Total Pendapatan Admin Top-Up -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 hover:shadow-md transition-all duration-200 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-gray-500 truncate" title="Total Pendapatan Admin Top-Up">Total Admin Top-Up</div>
                            <div class="text-lg sm:text-xl font-medium text-gray-900 leading-tight my-0.5 truncate" title="{{ format_rupiah($totalTopupAdmin ?? 0) }}">
                                {{ format_rupiah($totalTopupAdmin ?? 0) }}
                            </div>
                            <div class="text-xs text-emerald-600 font-medium truncate">Sepanjang waktu</div>
                        </div>
                    </div>

                    <!-- 2. Pendapatan Bulan Ini -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 hover:shadow-md transition-all duration-200 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-gray-500 truncate" title="Admin Top-Up Bulan Ini">Admin Bulan Ini</div>
                            <div class="text-lg sm:text-xl font-medium text-gray-900 leading-tight my-0.5 truncate" title="{{ format_rupiah($monthTopupAdmin ?? 0) }}">
                                {{ format_rupiah($monthTopupAdmin ?? 0) }}
                            </div>
                            <div class="text-xs text-blue-600 font-medium truncate">{{ now()->translatedFormat('F Y') }}</div>
                        </div>
                    </div>

                    <!-- 3. Jumlah Transaksi Sukses -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 hover:shadow-md transition-all duration-200 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-gray-500 truncate" title="Transaksi Top-Up Sukses">Transaksi Sukses</div>
                            <div class="text-lg sm:text-xl font-medium text-gray-900 leading-tight my-0.5 truncate" title="{{ number_format($countTopupAdmin ?? 0, 0, ',', '.') }} Transaksi">
                                {{ number_format($countTopupAdmin ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-purple-600 font-medium truncate">Top-Up Selesai</div>
                        </div>
                    </div>

                    <!-- 4. Rata-rata Biaya Admin / Top-Up -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 hover:shadow-md transition-all duration-200 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-gray-500 truncate" title="Rata-rata Biaya Admin per Top-Up">Rata-rata / Top-Up</div>
                            <div class="text-lg sm:text-xl font-medium text-gray-900 leading-tight my-0.5 truncate" title="{{ format_rupiah($avgTopupAdmin ?? 0) }}">
                                {{ format_rupiah($avgTopupAdmin ?? 0) }}
                            </div>
                            <div class="text-xs text-amber-600 font-medium truncate">Per transaksi</div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend Chart -->
                <div class="bg-gray-50/60 rounded-2xl p-4 sm:p-6 border border-gray-200" id="topupAdminChartContainer">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-medium text-gray-900">Grafik Pendapatan Biaya Admin Top-Up</div>
                                <p class="text-[11px] text-gray-400">Akumulasi pendapatan biaya admin dari setiap pengisian saldo</p>
                            </div>
                        </div>
                        <div class="inline-flex p-1 bg-gray-200/60 rounded-xl border border-gray-200/80 shadow-2xs self-start sm:self-auto">
                            <button type="button" data-range="daily" class="topup-range-tab px-3 py-1 text-xs font-medium rounded-lg transition-all duration-200">Harian</button>
                            <button type="button" data-range="monthly" class="topup-range-tab px-3 py-1 text-xs font-medium rounded-lg transition-all duration-200">Bulanan</button>
                            <button type="button" data-range="yearly" class="topup-range-tab px-3 py-1 text-xs font-medium rounded-lg transition-all duration-200">Tahunan</button>
                        </div>
                    </div>
                    <div class="relative h-64 sm:h-72">
                        <canvas id="topupAdminChart"></canvas>
                    </div>
                </div>

                <!-- Breakdown Section: Sumber Biaya Admin Top-Up -->
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">Breakdown Sumber Biaya Admin Top-Up</h3>
                            <p class="text-xs text-gray-500">Rincian perolehan biaya admin berdasarkan saluran dan metode pembayaran pengisian saldo</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- 1. Transfer Bank Manual / Virtual Account -->
                        <div class="bg-gradient-to-br from-blue-50/80 to-blue-100/60 rounded-2xl p-5 border border-blue-200 shadow-xs">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-xs flex-shrink-0" style="background-color: #2563eb; color: #ffffff; width: 40px; height: 40px; min-width: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; stroke: #ffffff;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 text-sm">Transfer Bank Manual / VA</h4>
                                        <p class="text-xs text-gray-500">BCA, BNI, Mandiri, BRI, dll</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 border border-blue-200 rounded-full text-xs font-medium">
                                    {{ $topupBreakdown['bank']['percent'] ?? 0 }}%
                                </span>
                            </div>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-600 font-medium">Total Perolehan:</span>
                                    <span class="text-base font-medium text-blue-700">
                                         {{ format_rupiah($topupBreakdown['bank']['total'] ?? 0) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs pt-2 border-t border-blue-200/80">
                                    <span class="text-gray-600 font-medium">Jumlah Transaksi:</span>
                                    <span class="font-medium text-gray-700">
                                        {{ number_format($topupBreakdown['bank']['count'] ?? 0, 0, ',', '.') }} transaksi
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs pt-2 border-t border-blue-200/80">
                                    <span class="text-gray-600 font-medium">Rata-rata Admin Fee:</span>
                                    <span class="font-medium text-gray-700">
                                        {{ format_rupiah($topupBreakdown['bank']['avg'] ?? 0) }} / top-up
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. QRIS & Instant Payment -->
                        <div class="bg-gradient-to-br from-emerald-50/80 to-emerald-100/60 rounded-2xl p-5 border border-emerald-200 shadow-xs">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-xs flex-shrink-0" style="background-color: #059669; color: #ffffff; width: 40px; height: 40px; min-width: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; stroke: #ffffff;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 text-sm">QRIS & Instant Payment</h4>
                                        <p class="text-xs text-gray-500">Scan QRIS & E-Wallet</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-full text-xs font-medium">
                                    {{ $topupBreakdown['qris']['percent'] ?? 0 }}%
                                </span>
                            </div>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-600 font-medium">Total Perolehan:</span>
                                    <span class="text-base font-medium text-emerald-700">
                                         {{ format_rupiah($topupBreakdown['qris']['total'] ?? 0) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs pt-2 border-t border-emerald-200/80">
                                    <span class="text-gray-600 font-medium">Jumlah Transaksi:</span>
                                    <span class="font-medium text-gray-700">
                                        {{ number_format($topupBreakdown['qris']['count'] ?? 0, 0, ',', '.') }} transaksi
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs pt-2 border-t border-emerald-200/80">
                                    <span class="text-gray-600 font-medium">Rata-rata Admin Fee:</span>
                                    <span class="font-medium text-gray-700">
                                        {{ format_rupiah($topupBreakdown['qris']['avg'] ?? 0) }} / top-up
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Volume Saldo Terproses -->
                        <div class="bg-gradient-to-br from-purple-50/80 to-purple-100/60 rounded-2xl p-5 border border-purple-200 shadow-xs">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-purple-600 flex items-center justify-center text-white shadow-xs flex-shrink-0" style="background-color: #9333ea; color: #ffffff; width: 40px; height: 40px; min-width: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; stroke: #ffffff;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 text-sm">Volume Saldo Top-Up</h4>
                                        <p class="text-xs text-gray-500">Akumulasi pengisian saldo</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-purple-100 text-purple-800 border border-purple-200 rounded-full text-xs font-medium">
                                    {{ $topupBreakdown['volume']['fee_ratio'] ?? 0 }}% fee
                                </span>
                            </div>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-600 font-medium">Total Saldo Masuk:</span>
                                    <span class="text-base font-medium text-purple-700">
                                         {{ format_rupiah($topupBreakdown['volume']['total_amount'] ?? 0) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs pt-2 border-t border-purple-200/80">
                                    <span class="text-gray-600 font-medium">Rata-rata Nominal Top-Up:</span>
                                    <span class="font-medium text-gray-700">
                                        {{ format_rupiah($topupBreakdown['volume']['avg_amount'] ?? 0) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs pt-2 border-t border-purple-200/80">
                                    <span class="text-gray-600 font-medium">Rasio Fee terhadap Saldo:</span>
                                    <span class="font-medium text-gray-700">
                                        {{ $topupBreakdown['volume']['fee_ratio'] ?? 0 }}% efisiensi
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="save"
        wire:confirm="Apakah Anda yakin ingin menyimpan perubahan pengaturan biaya admin top-up ini? Perubahan akan langsung diterapkan pada transaksi pengisian saldo.">
        @if(session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-green-800 font-medium text-sm">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        <div id="settingsFlash" data-message="{{ session('message') ?? '' }}" style="display:none"></div>

        <!-- Top-Up Fees Settings Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 px-4 sm:px-8 py-5 bg-gray-50/80">
                <h2 class="text-base sm:text-lg font-medium text-gray-900">Konfigurasi Biaya Admin Top-Up</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Atur biaya admin berdasarkan nominal transaksi top-up (3 tier bertingkat)</p>
            </div>

            <div class="p-4 sm:p-8 space-y-6">
                <!-- Tier 1 Settings -->
                <div class="border border-gray-200 rounded-2xl p-5 sm:p-6 bg-gray-50/60 hover:border-gray-300 transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 font-medium border border-blue-200 flex items-center justify-center text-sm shadow-xs">1</span>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Tier 1 - Nominal Kecil</h3>
                            <p class="text-xs text-gray-500">Biaya tetap untuk nominal top-up di bawah batas tier 1</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Batas Maksimal Tier 1</label>
                            <div class="flex items-center rounded-xl shadow-xs border border-gray-300 bg-white" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; padding-left: 14px; padding-right: 14px;">
                                <span style="color: #64748b; font-size: 14px; font-weight: 500; margin-right: 8px; user-select: none;">Rp</span>
                                <input type="text"
                                    inputmode="numeric"
                                    wire:ignore
                                    x-data
                                    x-init="$el.value = ($wire.tier1_limit !== null && $wire.tier1_limit !== '') ? Number($wire.tier1_limit).toLocaleString('id-ID') : ''"
                                    x-on:input="
                                        let raw = $el.value.replace(/\D/g, '');
                                        $el.value = raw ? Number(raw).toLocaleString('id-ID') : '';
                                        $wire.set('tier1_limit', raw ? parseInt(raw, 10) : 0);
                                    "
                                    value="{{ number_format((int) ($tier1_limit ?? 50000), 0, ',', '.') }}"
                                    placeholder="50.000"
                                    style="border: none; outline: none; padding: 10px 0; width: 100%; font-size: 14px; font-weight: 500; color: #0f172a; background: transparent;" />
                            </div>
                            @error('tier1_limit')
                                <div class="flex items-center gap-2 mt-1.5 text-red-600 text-xs font-medium">
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1.5">Nominal top-up di bawah nilai ini menggunakan biaya tier 1</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Biaya Admin Tier 1</label>
                            <div class="flex items-center rounded-xl shadow-xs border border-gray-300 bg-white" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; padding-left: 14px; padding-right: 14px;">
                                <span style="color: #64748b; font-size: 14px; font-weight: 500; margin-right: 8px; user-select: none;">Rp</span>
                                <input type="text"
                                    inputmode="numeric"
                                    wire:ignore
                                    x-data
                                    x-init="$el.value = ($wire.tier1_fee !== null && $wire.tier1_fee !== '') ? Number($wire.tier1_fee).toLocaleString('id-ID') : ''"
                                    x-on:input="
                                        let raw = $el.value.replace(/\D/g, '');
                                        $el.value = raw ? Number(raw).toLocaleString('id-ID') : '';
                                        $wire.set('tier1_fee', raw ? parseInt(raw, 10) : 0);
                                    "
                                    value="{{ number_format((int) ($tier1_fee ?? 5000), 0, ',', '.') }}"
                                    placeholder="5.000"
                                    style="border: none; outline: none; padding: 10px 0; width: 100%; font-size: 14px; font-weight: 500; color: #0f172a; background: transparent;" />
                            </div>
                            @error('tier1_fee')
                                <div class="flex items-center gap-2 mt-1.5 text-red-600 text-xs font-medium">
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1.5">Biaya tetap untuk nominal di bawah {{ format_rupiah($tier1_limit ?? 50000) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tier 2 Settings -->
                <div class="border border-gray-200 rounded-2xl p-5 sm:p-6 bg-gray-50/60 hover:border-gray-300 transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 font-medium border border-purple-200 flex items-center justify-center text-sm shadow-xs">2</span>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Tier 2 - Nominal Menengah</h3>
                            <p class="text-xs text-gray-500">Biaya tetap untuk nominal top-up antara batas tier 1 dan tier 2</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Batas Maksimal Tier 2</label>
                            <div class="flex items-center rounded-xl shadow-xs border border-gray-300 bg-white" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; padding-left: 14px; padding-right: 14px;">
                                <span style="color: #64748b; font-size: 14px; font-weight: 500; margin-right: 8px; user-select: none;">Rp</span>
                                <input type="text"
                                    inputmode="numeric"
                                    wire:ignore
                                    x-data
                                    x-init="$el.value = ($wire.tier2_limit !== null && $wire.tier2_limit !== '') ? Number($wire.tier2_limit).toLocaleString('id-ID') : ''"
                                    x-on:input="
                                        let raw = $el.value.replace(/\D/g, '');
                                        $el.value = raw ? Number(raw).toLocaleString('id-ID') : '';
                                        $wire.set('tier2_limit', raw ? parseInt(raw, 10) : 0);
                                    "
                                    value="{{ number_format((int) ($tier2_limit ?? 100000), 0, ',', '.') }}"
                                    placeholder="100.000"
                                    style="border: none; outline: none; padding: 10px 0; width: 100%; font-size: 14px; font-weight: 500; color: #0f172a; background: transparent;" />
                            </div>
                            @error('tier2_limit')
                                <div class="flex items-center gap-2 mt-1.5 text-red-600 text-xs font-medium">
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1.5">Nominal top-up di bawah nilai ini menggunakan biaya tier 2</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Biaya Admin Tier 2</label>
                            <div class="flex items-center rounded-xl shadow-xs border border-gray-300 bg-white" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; padding-left: 14px; padding-right: 14px;">
                                <span style="color: #64748b; font-size: 14px; font-weight: 500; margin-right: 8px; user-select: none;">Rp</span>
                                <input type="text"
                                    inputmode="numeric"
                                    wire:ignore
                                    x-data
                                    x-init="$el.value = ($wire.tier2_fee !== null && $wire.tier2_fee !== '') ? Number($wire.tier2_fee).toLocaleString('id-ID') : ''"
                                    x-on:input="
                                        let raw = $el.value.replace(/\D/g, '');
                                        $el.value = raw ? Number(raw).toLocaleString('id-ID') : '';
                                        $wire.set('tier2_fee', raw ? parseInt(raw, 10) : 0);
                                    "
                                    value="{{ number_format((int) ($tier2_fee ?? 7500), 0, ',', '.') }}"
                                    placeholder="7.500"
                                    style="border: none; outline: none; padding: 10px 0; width: 100%; font-size: 14px; font-weight: 500; color: #0f172a; background: transparent;" />
                            </div>
                            @error('tier2_fee')
                                <div class="flex items-center gap-2 mt-1.5 text-red-600 text-xs font-medium">
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1.5">Biaya tetap untuk nominal {{ format_rupiah($tier1_limit ?? 50000) }} - {{ format_rupiah($tier2_limit ?? 100000) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tier 3 Settings -->
                <div class="border border-gray-200 rounded-2xl p-5 sm:p-6 bg-gray-50/60 hover:border-gray-300 transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 font-medium border border-amber-200 flex items-center justify-center text-sm shadow-xs">3</span>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Tier 3 - Nominal Besar (Persentase)</h3>
                            <p class="text-xs text-gray-500">Biaya dihitung berdasarkan persentase nominal dengan batas maksimal</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Persentase Biaya Admin</label>
                            <div class="flex items-center rounded-xl shadow-xs border border-gray-300 bg-white" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; padding-left: 14px; padding-right: 14px;">
                                <input type="number" step="0.01" wire:model="tier3_percentage" placeholder="3"
                                    style="border: none; outline: none; padding: 10px 0; width: 100%; font-size: 14px; font-weight: 500; color: #0f172a; background: transparent;" />
                                <span style="color: #64748b; font-size: 14px; font-weight: 500; margin-left: 8px; user-select: none;">%</span>
                            </div>
                            @error('tier3_percentage')
                                <div class="flex items-center gap-2 mt-1.5 text-red-600 text-xs font-medium">
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1.5">Persentase dari nominal top-up (untuk nominal ≥ {{ format_rupiah($tier2_limit ?? 100000) }})</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Biaya Maksimal Tier 3 (Cap)</label>
                            <div class="flex items-center rounded-xl shadow-xs border border-gray-300 bg-white" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; padding-left: 14px; padding-right: 14px;">
                                <span style="color: #64748b; font-size: 14px; font-weight: 500; margin-right: 8px; user-select: none;">Rp</span>
                                <input type="text"
                                    inputmode="numeric"
                                    wire:ignore
                                    x-data
                                    x-init="$el.value = ($wire.tier3_max !== null && $wire.tier3_max !== '') ? Number($wire.tier3_max).toLocaleString('id-ID') : ''"
                                    x-on:input="
                                        let raw = $el.value.replace(/\D/g, '');
                                        $el.value = raw ? Number(raw).toLocaleString('id-ID') : '';
                                        $wire.set('tier3_max', raw ? parseInt(raw, 10) : 0);
                                    "
                                    value="{{ number_format((int) ($tier3_max ?? 15000), 0, ',', '.') }}"
                                    placeholder="15.000"
                                    style="border: none; outline: none; padding: 10px 0; width: 100%; font-size: 14px; font-weight: 500; color: #0f172a; background: transparent;" />
                            </div>
                            @error('tier3_max')
                                <div class="flex items-center gap-2 mt-1.5 text-red-600 text-xs font-medium">
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1.5">Batas maksimal biaya admin untuk tier 3</p>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pengaturan Saluran Pembayaran Terpusat -->
                <div class="border border-blue-200/80 rounded-2xl p-4 sm:p-5 bg-blue-50/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white border border-blue-200 text-[#0066FF] flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Pengaturan QRIS, Bank Transfer &amp; E-Wallet</h3>
                            <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">
                                Pengelolaan barcode QRIS dan daftar rekening bank/e-wallet penerima top-up maupun penarikan kini dipusatkan di menu <strong>Saluran Bank &amp; E-Wallet</strong>.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('superadmin.settings.banks') }}" wire:navigate
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-blue-50 text-[#0066FF] border border-blue-200 rounded-xl text-xs font-bold shadow-2xs transition shrink-0">
                        <span>Kelola di Saluran Bank</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <!-- Submit Button for Topup Fees -->
                <div class="pt-4 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium shadow-md shadow-primary-500/20 hover:shadow-lg transition-all duration-200 w-full sm:w-auto cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Pengaturan Biaya Top-Up
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Saved confirmation modal -->
    <div id="settingsSavedModal" class="fixed inset-0 z-50 flex items-center justify-center hidden transition-opacity duration-300">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="settingsSavedContent">
            <button id="settingsSavedClose" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="p-6 text-center">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4 animate-bounce-once">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Berhasil Disimpan!</h3>
                <p class="text-sm text-gray-600 leading-relaxed" id="settingsSavedMessage">
                    Perubahan telah diterapkan dan tersimpan.
                </p>
            </div>
        </div>
    </div>

    <style>
        @keyframes bounce-once {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .animate-bounce-once {
            animation: bounce-once 0.5s ease-in-out;
        }
        #settingsSavedModal:not(.hidden) #settingsSavedContent {
            transform: scale(1);
            opacity: 1;
        }
    </style>
</div>

<!-- Chart.js for Topup Admin -->
@php
    $topupAdminChartJson = json_encode($topupAdminChart ?? ['daily' => ['labels' => [], 'data' => []], 'monthly' => ['labels' => [], 'data' => []], 'yearly' => ['labels' => [], 'data' => []]]);
@endphp
<script>
    function initTopupAdminChart() {
        const canvas = document.getElementById('topupAdminChart');
        if (!canvas) return;

        if (typeof Chart === 'undefined') {
            setTimeout(initTopupAdminChart, 50);
            return;
        }

        const topupData = {!! $topupAdminChartJson !!};
        const topupCtx = canvas.getContext('2d');
        let topupChartInstance = Chart.getChart(canvas);
        if (topupChartInstance) {
            topupChartInstance.destroy();
            topupChartInstance = null;
        }

        function renderTopupChart(range) {
            if (!topupCtx) return;
            const container = document.getElementById('topupAdminChartContainer');
            if (container) {
                container.style.opacity = '0.3';
                container.style.transition = 'opacity 150ms ease';
            }

            setTimeout(() => {
                const labels = topupData[range]?.labels || [];
                const data = topupData[range]?.data || [];

                const gradient = topupCtx.createLinearGradient(0, 0, 0, 240);
                gradient.addColorStop(0, 'rgba(5, 150, 105, 0.85)');
                gradient.addColorStop(1, 'rgba(52, 211, 153, 0.35)');

                const cfg = {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Admin Top-Up',
                            data: data,
                            backgroundColor: gradient,
                            borderColor: 'rgba(5, 150, 105, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                            maxBarThickness: 38
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                padding: 10,
                                titleColor: '#fff',
                                bodyColor: '#e2e8f0',
                                borderColor: 'rgba(51, 65, 85, 0.4)',
                                borderWidth: 1,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function (context) {
                                        const v = context.raw ?? context.parsed?.y ?? 0;
                                        return 'Admin Top-Up: ' + (window.formatRupiah ? window.formatRupiah(v) : 'Rp ' + Number(v).toLocaleString('id-ID'));
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    autoSkip: true,
                                    maxRotation: 45,
                                    font: { size: 10 },
                                    color: '#64748b'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (v) { 
                                        if (v >= 1000000) return window.formatRupiah ? window.formatRupiah(v) : (Number.isInteger(v) && v % 10000 === 0 ? ('Rp ' + (v / 1000000).toFixed(2).replace(/\.?0+$/, '').replace('.', ',') + ' jt') : ('Rp ' + Number(v).toLocaleString('id-ID')));
                                        if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + 'k';
                                        return 'Rp ' + Number(v).toLocaleString('id-ID'); 
                                    },
                                    font: { size: 10 },
                                    color: '#64748b'
                                },
                                grid: { color: 'rgba(226, 232, 240, 0.6)' }
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                };

                const existing = Chart.getChart(canvas);
                if (existing) existing.destroy();
                topupChartInstance = new Chart(topupCtx, cfg);

                if (container) container.style.opacity = '1';
            }, 50);
        }

        const topupTabs = document.querySelectorAll('.topup-range-tab');
        function setTopupActive(r) {
            topupTabs.forEach(t => {
                if (t.dataset.range === r) {
                    t.classList.add('bg-white', 'text-emerald-700', 'shadow-xs');
                    t.classList.remove('text-gray-600');
                } else {
                    t.classList.remove('bg-white', 'text-emerald-700', 'shadow-xs');
                    t.classList.add('text-gray-600');
                }
            });
        }
        topupTabs.forEach(t => {
            t.onclick = function () {
                const r = t.dataset.range;
                setTopupActive(r);
                renderTopupChart(r);
            };
        });

        setTopupActive('daily');
        renderTopupChart('daily');
    }

    document.addEventListener('DOMContentLoaded', initTopupAdminChart);
    document.addEventListener('livewire:navigated', initTopupAdminChart);
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initTopupAdminChart();
    }
</script>

<script>
    let modalTimeout = null;

    function showSettingsSaved(message) {
        const modal = document.getElementById('settingsSavedModal');
        const msgEl = document.getElementById('settingsSavedMessage');
        if (!modal) return;
        if (!message) return;

        if (msgEl) msgEl.textContent = message;

        if (modalTimeout) {
            clearTimeout(modalTimeout);
            modalTimeout = null;
        }

        modal.classList.add('hidden');

        setTimeout(() => {
            modal.classList.remove('hidden');
            modalTimeout = setTimeout(() => {
                modal.classList.add('hidden');
                modalTimeout = null;
            }, 3000);
        }, 50);
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('settingsSaved', (event) => {
            const message = event[0]?.message || event.message || 'Pengaturan berhasil disimpan';
            showSettingsSaved(message);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const closeBtn = document.getElementById('settingsSavedClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                if (modalTimeout) {
                    clearTimeout(modalTimeout);
                    modalTimeout = null;
                }
                const modal = document.getElementById('settingsSavedModal');
                if (modal) modal.classList.add('hidden');
            });
        }
    });
</script>
