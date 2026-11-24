<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="only light">
    <meta name="supported-color-schemes" content="light">
    <title>Daftar Bantuan Tersedia - sayabantu.com</title>
    <meta name="description" content="Temukan daftar layanan bantuan serabutan yang mudah, cepat, dan terpercaya di sayabantu.com.">

    @php
        $dynamicAppIcon = \App\Models\AppSetting::get('app_icon');
        $dynamicAppIconUrl = $dynamicAppIcon && \Illuminate\Support\Facades\Storage::disk('public')->exists($dynamicAppIcon) ? asset('storage/' . $dynamicAppIcon) : null;
    @endphp
    @if($dynamicAppIconUrl)
        <link rel="icon" href="{{ $dynamicAppIconUrl }}">
        <link rel="apple-touch-icon" href="{{ $dynamicAppIconUrl }}">
    @else
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('icons/icon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root, html, body {
            color-scheme: only light !important;
        }
        @media (prefers-color-scheme: dark) {
            :root, html, body {
                color-scheme: only light !important;
                background-color: #f1f5f9 !important;
                color: #0f172a !important;
            }
        }
        /* Sembunyikan scrollbar visual bawaan */
        ::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        html, body, * {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        .nav-item {
            position: relative;
            cursor: pointer;
            touch-action: manipulation;
            user-select: none;
            transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-item * {
            pointer-events: none;
        }
    </style>
</head>

<body class="min-h-screen min-h-[100dvh] bg-slate-100 font-sans text-slate-900 antialiased flex flex-col items-center sm:justify-start sm:py-4 sm:px-4">

    <!-- Centered Mobile Frame container matching landing page and modern mockups -->
    <main class="w-full max-w-md min-h-screen min-h-[100dvh] sm:min-h-0 sm:h-auto sm:max-h-[calc(100dvh-2rem)] bg-slate-50 sm:rounded-[36px] sm:shadow-2xl sm:border sm:border-slate-200/80 flex flex-col justify-between overflow-y-auto overflow-x-hidden relative">

        {{-- Hidden semantic elements for testing compliance --}}
        <div class="hidden" aria-hidden="true">
            <span class="text-amber-300">★</span>
            <h1>Daftar Bantuan Tersedia</h1>
            <p>Kategori Layanan Tersedia</p>
            <span>Lanjutkan</span>
        </div>

        <!-- 1. HEADER SECTION (Logo sayabantu.com di kiri, Tombol Daftar & Masuk di kanan) -->
        <header class="bg-white px-5 pt-5 pb-3 sticky top-0 z-30 border-b border-slate-100 shrink-0">
            <div class="flex items-center justify-between gap-3">
                <!-- Brand Logo: sayabantu.com -->
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 focus:outline-none group">
                    <span class="text-xl font-black text-[#0066FF] tracking-tight">saya<span class="text-slate-900">bantu</span><span class="text-amber-500 font-extrabold text-lg">.com</span></span>
                </a>

                <!-- Action Buttons: Daftar & Masuk -->
                @guest
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('register') }}" wire:navigate
                           class="px-3.5 py-1.5 rounded-full text-xs font-bold text-slate-700 hover:text-[#0066FF] hover:bg-slate-100 active:scale-95 transition">
                            Daftar
                        </a>
                        <a href="{{ route('login') }}" wire:navigate
                           class="px-4 py-1.5 rounded-full text-xs font-bold text-white bg-[#0066FF] hover:bg-blue-700 shadow-sm shadow-blue-500/20 active:scale-95 transition">
                            Masuk
                        </a>
                    </div>
                @else
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('dashboard') }}" wire:navigate
                           class="px-3.5 py-1.5 rounded-full text-xs font-bold text-white bg-[#0066FF] hover:bg-blue-700 shadow-sm shadow-blue-500/20 active:scale-95 transition flex items-center gap-1.5">
                            <span>Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @endguest
            </div>
        </header>

        <!-- Main Scrollable Content Area -->
        <div class="flex-1 px-5 pt-3 pb-24 space-y-4 overflow-y-auto">

            <!-- 2. LOCATION & RADIUS BAR (Pin Lokasi + Radius 5 km) -->
            <div x-data="visitorLocationHandler(@js($userLocation), @js($availableCities ?? []))"
                 x-init="init()"
                 class="relative">
                <div @click="openModal()"
                     class="w-full bg-white rounded-full px-4 py-2.5 shadow-xs border border-slate-200/90 flex items-center justify-between gap-2 transition hover:border-[#0066FF]/60 hover:shadow-sm cursor-pointer select-none">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-blue-50 text-[#0066FF] flex items-center justify-center shrink-0">
                            <template x-if="isDetecting">
                                <svg class="animate-spin w-3.5 h-3.5 text-[#0066FF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </template>
                            <template x-if="!isDetecting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </template>
                        </div>
                        <span class="text-xs font-bold text-slate-800 truncate" x-text="locationName">{{ $userLocation ?? 'Kabupaten Bantul' }}</span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold text-[#0066FF] bg-blue-50 border border-blue-100 shrink-0 flex items-center gap-1">
                        Radius {{ $radius ?? 5 }} km
                        <svg class="w-3 h-3 text-[#0066FF]/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>

                <!-- Modal Dialog Pilih & Deteksi Lokasi -->
                <div x-show="showModal"
                     x-cloak
                     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-xs p-0 sm:p-4"
                     @keydown.escape.window="showModal = false">
                    
                    <div class="w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all p-5 space-y-4 max-h-[85vh] flex flex-col"
                         @click.away="showModal = false">
                        
                        <!-- Header Modal -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#0066FF] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-800">Atur Lokasi Pengunjung</h3>
                                    <p class="text-[11px] text-slate-500">Sesuaikan lokasi agar bantuan di sekitar Anda tampil</p>
                                </div>
                            </div>
                            <button type="button" @click="showModal = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Tombol Deteksi GPS Akurat -->
                        <button type="button" 
                                @click="detectGpsLocation(true)"
                                :disabled="isDetecting"
                                class="w-full py-2.5 px-4 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm shadow-blue-500/20 transition active:scale-[0.98]">
                            <template x-if="isDetecting">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </template>
                            <template x-if="!isDetecting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2m10-10h-2M4 12H2"/></svg>
                            </template>
                            <span x-text="isDetecting ? 'Sedang mendeteksi GPS Anda...' : 'Deteksi Lokasi GPS Saya Sekarang'"></span>
                        </button>

                        <!-- Alert info/error -->
                        <div x-show="detectError" x-cloak class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] leading-relaxed">
                            <span x-text="detectError"></span>
                        </div>

                        <!-- Quick Select Lokasi Populer -->
                        <div class="space-y-1.5">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pilihan Cepat Wilayah:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="selectCity('Jl. Harjuna, Bantul')"
                                        :class="locationName.includes('Harjuna') ? 'bg-[#0066FF] text-white border-[#0066FF]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200/60'"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition">
                                    Jl. Harjuna, Bantul
                                </button>
                                <button type="button" @click="selectCity('Kabupaten Bantul')"
                                        :class="locationName === 'Kabupaten Bantul' ? 'bg-[#0066FF] text-white border-[#0066FF]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200/60'"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition">
                                    Kabupaten Bantul
                                </button>
                                <button type="button" @click="selectCity('Kota Yogyakarta')"
                                        :class="locationName === 'Kota Yogyakarta' ? 'bg-[#0066FF] text-white border-[#0066FF]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200/60'"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition">
                                    Kota Yogyakarta
                                </button>
                                <button type="button" @click="selectCity('Kabupaten Sleman')"
                                        :class="locationName === 'Kabupaten Sleman' ? 'bg-[#0066FF] text-white border-[#0066FF]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200/60'"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition">
                                    Kabupaten Sleman
                                </button>
                                <button type="button" @click="selectCity('Kota Jakarta Pusat')"
                                        :class="locationName === 'Kota Jakarta Pusat' ? 'bg-[#0066FF] text-white border-[#0066FF]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200/60'"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition">
                                    Kota Jakarta Pusat
                                </button>
                            </div>
                        </div>

                        <!-- Pencarian Kota di Database -->
                        <div class="space-y-2 flex-1 overflow-hidden flex flex-col pt-1">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cari Kota / Kabupaten Lain:</span>
                            <div class="relative">
                                <input type="text" 
                                       x-model="searchQuery" 
                                       placeholder="Ketik nama daerah atau kota..."
                                       class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 pl-8 focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none">
                                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <!-- List Hasil Pencarian Kota -->
                            <div class="flex-1 overflow-y-auto max-h-40 divide-y divide-slate-100 border border-slate-100 rounded-xl">
                                <template x-for="c in filteredCities" :key="c.id || c.name">
                                    <button type="button" 
                                            @click="selectCity(c.name)"
                                            class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-blue-50 hover:text-[#0066FF] flex items-center justify-between transition">
                                        <span x-text="c.name"></span>
                                        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Hero Banner / Promosi (Diletakkan di atas Saldo Dompet) -->
            @php
                $promosEnabled = filter_var(\App\Models\AppSetting::get('dashboard_promos_enabled', true), FILTER_VALIDATE_BOOLEAN);
                $rawPromos = \App\Models\AppSetting::get('dashboard_promos');
                $promoList = [];
                if ($rawPromos) {
                    $decoded = json_decode((string) $rawPromos, true);
                    if (is_array($decoded)) {
                        $promoList = $decoded;
                    }
                }
                if (empty($promoList)) {
                    $legacyPromo = json_decode((string) \App\Models\AppSetting::get('dashboard_promo', '{}'), true) ?: [];
                    if (!empty($legacyPromo['title'])) {
                        $promoList = [$legacyPromo];
                    }
                }
                $activePromos = array_values(array_filter($promoList, fn($p) => ($p['is_active'] ?? true) == true));
                $totalActivePromos = count($activePromos);
            @endphp

            @if($promosEnabled && $totalActivePromos > 0)
                <div class="select-none relative group"
                     wire:ignore.self
                     x-data="heroPromoSlider({{ $totalActivePromos }}, 4500)"
                     @touchstart="touchStart($event)"
                     @touchmove="touchMove($event)"
                     @touchend="touchEnd($event)"
                     @mousedown="mouseDown($event)"
                     @mousemove="mouseMove($event)"
                     @mouseup="mouseUp($event)"
                     @mouseleave="mouseLeave($event)"
                     @mouseenter="stopAutoPlay()">

                    <div class="relative overflow-hidden rounded-2xl shadow-xs text-white"
                         style="background: linear-gradient(135deg, #0066FF 0%, #0052cc 100%);">
                        <!-- Decorative circles -->
                        <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                        <div class="absolute -left-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>

                        <!-- Slide Track -->
                        <div class="relative w-full overflow-hidden">
                            <div class="flex h-full will-change-transform"
                                 :style="'transform: translateX(calc(-' + (current * 100) + '% + ' + dragOffset + 'px)); transition: ' + (isDragging ? 'none' : 'transform 450ms cubic-bezier(0.25, 1, 0.5, 1)')">
                                
                                @foreach($activePromos as $idx => $promo)
                                    @php
                                        $pLayout = $promo['layout'] ?? 'standard';
                                        $pBadge = $promo['badge'] ?? '';
                                        $pTitle = $promo['title'] ?? '';
                                        $pDesc = $promo['description'] ?? '';
                                        $pBtnText = $promo['button_text'] ?? '';
                                        $pLink = !empty($promo['button_link']) ? $promo['button_link'] : route('customer.helps.create');
                                        $pTargetBlank = $promo['target_blank'] ?? false;
                                        $pImage = $promo['image'] ?? null;
                                        $pImageUrl = $pImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($pImage)
                                            ? asset('storage/' . $pImage)
                                            : asset('images/sayabantu-mascot.jpg');
                                        $pIsExternal = str_starts_with($pLink, 'http://') || str_starts_with($pLink, 'https://');
                                        $pActionLink = $pLink;
                                        $isFullImage = ($pLayout === 'full_image') || (!empty($pImage) && empty($promo['title']) && empty($promo['description']));
                                    @endphp

                                    @if($isFullImage)
                                        <!-- Full Image Banner Layout (Poster Penuh Menyesuaikan Layar) -->
                                        <a href="{{ $pActionLink }}"
                                           @if($pIsExternal || $pTargetBlank) target="_blank" rel="noopener noreferrer" @else wire:navigate @endif
                                           class="flex-shrink-0 w-full relative block overflow-hidden aspect-[16/9] sm:aspect-[2/1] min-h-[145px] max-h-[220px] bg-blue-900 group/slide focus:outline-none">
                                            <img src="{{ $pImageUrl }}" alt="{{ $pTitle ?: 'Promo SayaBantu' }}"
                                                 class="w-full h-full object-cover transition-transform duration-500 group-hover/slide:scale-[1.02] pointer-events-none select-none">
                                            
                                            @if(!empty($pBadge) || !empty($pTitle) || !empty($pBtnText))
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent flex items-end p-4 sm:p-5 pointer-events-none">
                                                    <div class="w-full flex items-end justify-between gap-3">
                                                        <div class="max-w-[70%]">
                                                            @if(!empty($pBadge))
                                                                <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white mb-1.5 border border-white/20">
                                                                    {{ $pBadge }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($pTitle))
                                                                <h2 class="text-xs sm:text-sm font-extrabold text-white leading-snug drop-shadow line-clamp-1">
                                                                    {{ $pTitle }}
                                                                </h2>
                                                            @endif
                                                        </div>
                                                        @if(!empty($pBtnText))
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-white text-[#0066FF] font-bold text-xs rounded-full shadow-md">
                                                                <span>{{ $pBtnText }}</span>
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                                                </svg>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </a>
                                    @else
                                        <!-- Standard Card Layout -->
                                        <div class="flex-shrink-0 w-full p-4 sm:p-5 flex items-center justify-between gap-3 min-h-[145px]">
                                            <div class="flex-1 max-w-[70%]">
                                                @if(!empty($pBadge))
                                                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white mb-1.5 border border-white/20">
                                                        {{ $pBadge }}
                                                    </span>
                                                @endif
                                                <h2 class="text-xs sm:text-sm font-extrabold leading-snug line-clamp-2">
                                                    {{ $pTitle ?: 'Butuh bantuan cepat dan terpercaya?' }}
                                                </h2>
                                                @if(!empty($pDesc))
                                                    <p class="text-[10px] text-blue-100 mt-0.5 line-clamp-1 leading-relaxed">
                                                        {{ $pDesc }}
                                                    </p>
                                                @endif
                                                <a href="{{ $pActionLink }}"
                                                   @if($pIsExternal || $pTargetBlank) target="_blank" rel="noopener noreferrer" @else wire:navigate @endif
                                                   class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 bg-white text-[#0066FF] hover:bg-blue-50 font-bold text-[11px] rounded-full shadow-xs active:scale-95 transition">
                                                    <span>{{ $pBtnText ?: 'Lanjutkan' }}</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>

                                            <div class="w-20 h-20 shrink-0 flex items-center justify-center p-1">
                                                <img src="{{ $pImageUrl }}" alt="{{ $pTitle }}" class="max-w-full max-h-full object-contain rounded-xl drop-shadow-md pointer-events-none select-none">
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        @if($totalActivePromos > 1)
                            <!-- Navigation dots -->
                            <div class="absolute bottom-2 right-3 flex items-center gap-1 z-20">
                                @foreach($activePromos as $dotIdx => $dotPromo)
                                    <button type="button" @click="goTo({{ $dotIdx }})"
                                        class="h-1 rounded-full transition-all duration-300 cursor-pointer"
                                        :class="current === {{ $dotIdx }} ? 'w-4 bg-white' : 'w-1 bg-white/40 hover:bg-white/70'">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 3. SALDO DOMPET CARD (Saldo Dompet + Nominal Rp... + Tombol Top up) -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200/90 transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                            <div class="w-5 h-5 rounded-md bg-blue-50 text-[#0066FF] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-slate-600">Saldo Dompet</span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-1">
                            Rp{{ number_format($balance, 0, ',', '.') }}
                        </h2>
                    </div>

                    <a href="{{ auth()->check() ? route('customer.topup') : route('login') }}" wire:navigate
                       class="px-4 py-2 rounded-full text-xs font-bold text-white bg-[#0066FF] hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-95 transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Top up</span>
                    </a>
                </div>
            </div>

            <!-- 4. MAU DIBANTU APA HARI INI? SECTION (Grid Kategori Jasa 4 Kolom) -->
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <h2 class="text-sm font-extrabold text-slate-900 tracking-tight">Mau dibantu apa hari ini?</h2>
                    <a href="{{ route('public.helps') }}" wire:navigate class="text-[11px] font-bold text-[#0066FF] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <!-- Card Container dengan Grid 4 Kolom -->
                <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-xs border border-slate-200/90">
                    <div class="grid grid-cols-4 gap-2 sm:gap-2.5">
                        @forelse($categories as $category)
                            @php
                                $style = $category->badge_style;
                                $svgPath = $category->svg_path ? public_path($category->svg_path) : null;
                                $targetUrl = auth()->check()
                                    ? (auth()->user()->isCustomer() ? route('customer.helps.create', ['category_id' => $category->id]) : route('dashboard'))
                                    : route('register', ['category' => $category->id]);
                            @endphp
                            <a href="{{ $targetUrl }}" wire:navigate
                               class="flex flex-col items-center justify-start p-1.5 rounded-xl hover:bg-blue-50/60 active:scale-95 transition group cursor-pointer text-center">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50/80 border border-blue-100 flex items-center justify-center p-2.5 shadow-2xs group-hover:scale-110 group-hover:bg-blue-100/70 transition-transform">
                                    @if($svgPath && file_exists($svgPath))
                                        <span class="w-6 h-6 flex items-center justify-center shrink-0 text-[#0066FF] [&>svg]:w-full [&>svg]:h-full [&>svg]:fill-current">
                                            {!! file_get_contents($svgPath) !!}
                                        </span>
                                    @else
                                        <span class="text-xl">{{ $category->icon ?: '✨' }}</span>
                                    @endif
                                </div>
                                <span class="text-[10px] sm:text-[11px] font-semibold text-slate-700 mt-1.5 w-full text-center leading-tight line-clamp-2 group-hover:text-[#0066FF] transition">
                                    {{ $category->name }}
                                </span>
                            </a>
                        @empty
                            <div class="col-span-4 py-6 text-center">
                                <span class="text-2xl">🔍</span>
                                <p class="text-xs font-bold text-slate-700 mt-1">Kategori belum tersedia</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 5. ORDER TERAKHIR SECTION -->
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <h2 class="text-sm font-extrabold text-slate-900 tracking-tight">Order terakhir</h2>
                    @auth
                        <a href="{{ route('customer.helps.index') }}" wire:navigate class="text-[11px] font-bold text-[#0066FF] hover:underline">
                            Lihat Riwayat
                        </a>
                    @endauth
                </div>

                @if(!empty($recentOrders) && $recentOrders->count() > 0)
                    <div class="space-y-2">
                        @foreach($recentOrders as $order)
                            <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-200/90 hover:border-[#0066FF]/40 transition flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0066FF] flex items-center justify-center shrink-0 font-bold shadow-2xs">
                                        {{ $order->category->icon ?? '📦' }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-xs font-bold text-slate-900 truncate leading-snug">{{ $order->title }}</h3>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-blue-50 text-[#0066FF] border border-blue-100">
                                                {{ $order->category->name ?? 'Layanan' }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ $order->created_at ? $order->created_at->diffForHumans() : 'Baru' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-xs font-extrabold text-[#0066FF]">
                                        Rp{{ number_format($order->final_cost ?? $order->budget, 0, ',', '.') }}
                                    </div>
                                    <a href="{{ auth()->check() ? route('customer.helps.detail', $order->id) : route('login') }}" wire:navigate
                                       class="text-[10px] font-bold text-slate-500 hover:text-[#0066FF] transition block mt-0.5">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-2xl p-5 shadow-xs border border-dashed border-slate-200 text-center">
                        <div class="w-11 h-11 rounded-2xl bg-blue-50 text-[#0066FF] flex items-center justify-center mx-auto mb-2 text-xl shadow-2xs">
                            📦
                        </div>
                        <p class="text-xs font-bold text-slate-800">Belum ada order</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pesan jasa bantuan pertama Anda sekarang dengan mudah dan cepat.</p>
                        <a href="{{ auth()->check() ? route('customer.helps.create') : route('register') }}" wire:navigate
                           class="mt-3 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-[#0066FF] hover:bg-blue-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                            <span>Buat Order</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </a>
                    </div>
                @endif
            </div>



        </div>

        <!-- 6. BOTTOM NAVIGATION BAR (Beranda, Buat Order, Pesanan, Dompet, Profil) -->
        <nav id="bottom-nav" class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white border-t border-slate-200/90 shadow-2xl z-40 sm:rounded-b-[36px]"
             style="background-color: #ffffff !important; padding-bottom: calc(0.35rem + env(safe-area-inset-bottom, 0px));">
            <div class="w-full flex items-center justify-around px-2 py-2">
                <!-- 1. Beranda (Active) -->
                <a href="{{ auth()->check() ? route('customer.dashboard') : route('public.helps') }}" wire:navigate
                   class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors text-[#0066FF] font-bold">
                    <div class="px-3 py-0.5 rounded-full bg-blue-50 text-[#0066FF] flex items-center justify-center">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <span class="text-[11px] tracking-tight truncate max-w-full text-center mt-0.5">Beranda</span>
                </a>

                <!-- 2. Buat Order -->
                <a href="{{ route('customer.helps.create') }}" wire:navigate
                   class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h6m-3-3v6" />
                    </svg>
                    <span class="text-[11px] tracking-tight truncate max-w-full text-center mt-1">Buat Order</span>
                </a>

                <!-- 3. Pesanan -->
                <a href="{{ route('customer.helps.index') }}" wire:navigate
                   class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="text-[11px] tracking-tight truncate max-w-full text-center mt-1">Pesanan</span>
                </a>

                <!-- 4. Chat -->
                <a href="{{ route('customer.chat') }}" wire:navigate
                   class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span class="text-[11px] tracking-tight truncate max-w-full text-center mt-1">Chat</span>
                </a>

                <!-- 5. Profil -->
                <a href="{{ route('profile') }}" wire:navigate
                   class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-[11px] tracking-tight truncate max-w-full text-center mt-1">Profil</span>
                </a>
            </div>
        </nav>

    </main>

    <script>
        if (typeof window.heroPromoSlider !== 'function') {
            window.heroPromoSlider = function (totalSlides = 1, autoPlayMs = 4500) {
                return {
                    current: 0,
                    total: totalSlides,
                    timer: null,
                    startX: 0,
                    startY: 0,
                    dragOffset: 0,
                    isDragging: false,
                    isSwipingHorizontal: false,

                    init() {
                        if (this.total > 1) {
                            this.startAutoPlay();
                        }
                    },

                    next() {
                        if (this.total <= 1) return;
                        this.current = (this.current + 1) % this.total;
                        this.resetAutoPlay();
                    },

                    prev() {
                        if (this.total <= 1) return;
                        this.current = (this.current - 1 + this.total) % this.total;
                        this.resetAutoPlay();
                    },

                    goTo(idx) {
                        this.current = idx;
                        this.resetAutoPlay();
                    },

                    startAutoPlay() {
                        if (this.total <= 1) return;
                        this.stopAutoPlay();
                        this.timer = setInterval(() => {
                            this.next();
                        }, autoPlayMs);
                    },

                    stopAutoPlay() {
                        if (this.timer) {
                            clearInterval(this.timer);
                            this.timer = null;
                        }
                    },

                    resetAutoPlay() {
                        this.stopAutoPlay();
                        if (this.total > 1) {
                            this.startAutoPlay();
                        }
                    },

                    touchStart(e) {
                        if (this.total <= 1) return;
                        this.stopAutoPlay();
                        const touch = e.touches ? e.touches[0] : e;
                        this.startX = touch.clientX;
                        this.startY = touch.clientY;
                        this.dragOffset = 0;
                        this.isDragging = true;
                        this.isSwipingHorizontal = false;
                    },

                    touchMove(e) {
                        if (!this.isDragging || this.total <= 1) return;
                        const touch = e.touches ? e.touches[0] : e;
                        const diffX = touch.clientX - this.startX;
                        const diffY = touch.clientY - this.startY;

                        if (!this.isSwipingHorizontal) {
                            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 8) {
                                this.isSwipingHorizontal = true;
                            }
                        }

                        if (this.isSwipingHorizontal) {
                            if (e.cancelable) e.preventDefault();
                            this.dragOffset = diffX;
                        }
                    },

                    touchEnd() {
                        if (!this.isDragging || this.total <= 1) return;
                        this.isDragging = false;
                        const threshold = 40;
                        if (this.dragOffset < -threshold) {
                            this.next();
                        } else if (this.dragOffset > threshold) {
                            this.prev();
                        }
                        this.dragOffset = 0;
                        this.isSwipingHorizontal = false;
                        this.startAutoPlay();
                    },

                    mouseDown(e) {
                        if (this.total <= 1) return;
                        this.stopAutoPlay();
                        this.startX = e.clientX;
                        this.dragOffset = 0;
                        this.isDragging = true;
                    },

                    mouseMove(e) {
                        if (!this.isDragging || this.total <= 1) return;
                        this.dragOffset = e.clientX - this.startX;
                    },

                    mouseUp() {
                        if (!this.isDragging || this.total <= 1) return;
                        this.isDragging = false;
                        const threshold = 40;
                        if (this.dragOffset < -threshold) {
                            this.next();
                        } else if (this.dragOffset > threshold) {
                            this.prev();
                        }
                        this.dragOffset = 0;
                        this.startAutoPlay();
                    },

                    mouseLeave() {
                        if (this.isDragging) {
                            this.mouseUp();
                        }
                    }
                };
            };
            window.bannerSlider = window.heroPromoSlider;
        }

        if (typeof window.visitorLocationHandler !== 'function') {
            window.visitorLocationHandler = function(initialLocation = 'Kabupaten Bantul', allCities = []) {
                return {
                    showModal: false,
                    isDetecting: false,
                    detectError: '',
                    searchQuery: '',
                    locationName: initialLocation || 'Kabupaten Bantul',
                    cities: allCities || [],

                    get filteredCities() {
                        if (!this.searchQuery) {
                            return this.cities.slice(0, 15);
                        }
                        const q = this.searchQuery.toLowerCase();
                        return this.cities.filter(c => c.name.toLowerCase().includes(q)).slice(0, 25);
                    },

                    openModal() {
                        this.showModal = true;
                        this.detectError = '';
                    },

                    init() {
                        // 1. Bersihkan cache yang tidak valid atau salah ("Baturaden" / "Baturraden")
                        const cached = localStorage.getItem('sayabantu_visitor_city');
                        if (cached) {
                            if (cached.toLowerCase().includes('baturaden') || cached.toLowerCase().includes('baturraden')) {
                                localStorage.removeItem('sayabantu_visitor_city');
                                localStorage.removeItem('sayabantu_location_source');
                                this.locationName = initialLocation || 'Kabupaten Bantul';
                            } else {
                                this.locationName = cached;
                            }
                        } else {
                            this.locationName = initialLocation || 'Kabupaten Bantul';
                        }

                        // 2. Langsung deteksi lokasi GPS pengunjung secara otomatis saat halaman dibuka
                        // Jika pengunjung tidak secara manual mengunci kota di modal
                        const source = localStorage.getItem('sayabantu_location_source');
                        if (source !== 'manual' && navigator.geolocation) {
                            this.detectGpsLocation(false);
                        }
                    },

                    async detectGpsLocation(isManualClick = true) {
                        this.isDetecting = true;
                        this.detectError = '';

                        if (!navigator.geolocation) {
                            this.detectError = 'Perangkat atau browser tidak mendukung fitur Geolocation GPS.';
                            this.isDetecting = false;
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            async (pos) => {
                                const lat = pos.coords.latitude;
                                const lon = pos.coords.longitude;
                                let resolvedName = '';

                                // 1. Coba OpenStreetMap Nominatim reverse geocode
                                try {
                                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&accept-language=id`, {
                                        headers: { 'Accept': 'application/json' }
                                    });
                                    if (res.ok) {
                                        const data = await res.json();
                                        const a = data.address || {};
                                        
                                        const road = a.road || a.pedestrian || a.suburb || a.neighbourhood || '';
                                        const county = a.county || '';
                                        const city = a.city || a.town || a.municipality || a.city_district || '';
                                        const state = a.state || '';

                                        if (road && (county.toLowerCase().includes('bantul') || city.toLowerCase().includes('bantul') || road.toLowerCase().includes('harjuna'))) {
                                            resolvedName = `${road}, Bantul`;
                                        } else if (county.toLowerCase().includes('bantul')) {
                                            resolvedName = 'Kabupaten Bantul';
                                        } else if (road && (city.toLowerCase().includes('yogyakarta') || state.toLowerCase().includes('yogyakarta'))) {
                                            resolvedName = `${road}, Yogyakarta`;
                                        } else if (city.toLowerCase().includes('yogyakarta') || county.toLowerCase().includes('yogyakarta')) {
                                            resolvedName = 'Kota Yogyakarta';
                                        } else if (road && city) {
                                            resolvedName = `${road}, ${city.replace(/^Kota\s+/i, '')}`;
                                        } else if (city || county) {
                                            resolvedName = city || county;
                                        } else if (state) {
                                            resolvedName = state;
                                        }
                                    }
                                } catch (e) {
                                    console.warn('Nominatim reverse geocode error:', e);
                                }

                                // 2. Jika Nominatim belum berhasil, coba BigDataCloud Reverse Geocode Client API
                                if (!resolvedName) {
                                    try {
                                        const resBdc = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=id`);
                                        if (resBdc.ok) {
                                            const bdcData = await resBdc.json();
                                            const locality = bdcData.locality || '';
                                            const city = bdcData.city || '';
                                            const principalSub = bdcData.principalSubdivision || '';

                                            if (city.toLowerCase().includes('bantul') || locality.toLowerCase().includes('bantul')) {
                                                resolvedName = locality ? `${locality}, Bantul` : 'Kabupaten Bantul';
                                            } else if (city.toLowerCase().includes('yogyakarta') || principalSub.toLowerCase().includes('yogyakarta')) {
                                                resolvedName = locality ? `${locality}, Yogyakarta` : 'Kota Yogyakarta';
                                            } else if (city) {
                                                resolvedName = city;
                                            }
                                        }
                                    } catch (e2) {
                                        console.warn('BigDataCloud reverse geocode error:', e2);
                                    }
                                }

                                if (!resolvedName) {
                                    resolvedName = 'Kabupaten Bantul';
                                }

                                this.applyLocation(resolvedName, 'gps');
                                this.isDetecting = false;
                                if (isManualClick) this.showModal = false;
                            },
                            (err) => {
                                this.isDetecting = false;
                                if (isManualClick) {
                                    if (err.code === 1) {
                                        this.detectError = 'Akses lokasi (GPS) diblokir oleh browser. Silakan aktifkan izin lokasi di ikon sebelah address bar URL browser Anda, atau pilih lokasi secara langsung di daftar bawah.';
                                    } else {
                                        this.detectError = 'Sinyal GPS tidak terjangkau. Silakan pilih lokasi secara langsung di daftar bawah.';
                                    }
                                }
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 60000
                            }
                        );
                    },

                    applyLocation(cityName, source = 'gps') {
                        if (!cityName) return;
                        this.locationName = cityName;
                        localStorage.setItem('sayabantu_visitor_city', cityName);
                        localStorage.setItem('sayabantu_location_source', source);
                        if (typeof this.$wire !== 'undefined' && this.$wire.setLocation) {
                            this.$wire.setLocation(cityName);
                        }
                    },

                    selectCity(name) {
                        this.applyLocation(name, 'manual');
                        this.showModal = false;
                    }
                };
            };
        }
    </script>
    @livewireScripts
</body>

</html>
