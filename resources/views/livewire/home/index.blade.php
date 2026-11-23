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
        .header-pattern {
            position: relative;
            overflow: hidden;
        }
        .header-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .header-pattern::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
    </style>
</head>

<body class="min-h-screen min-h-[100dvh] bg-slate-100 font-sans text-slate-900 antialiased flex flex-col items-center sm:justify-start sm:py-4 sm:px-4">

    <!-- Centered Mobile Frame container matching landing page and modern mockups -->
    <main class="w-full max-w-md min-h-screen min-h-[100dvh] sm:min-h-0 sm:h-auto sm:max-h-[calc(100dvh-2rem)] bg-slate-50 sm:rounded-[36px] sm:shadow-2xl sm:border sm:border-slate-200/80 flex flex-col justify-between overflow-y-auto overflow-x-hidden relative">

        <!-- Header Section -->
        <header class="px-5 pt-5 pb-6 text-white relative header-pattern shrink-0" style="background: linear-gradient(135deg, #0066FF 0%, #0052cc 100%);">
            <div class="relative z-10">
                <!-- Top Nav Bar -->
                <div class="flex items-center justify-between gap-3 mb-3">
                    <a href="{{ route('home') }}"
                       onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); setTimeout(function() { window.location.href = '{{ route('home') }}'; }, 350); } else { window.location.href = '{{ route('home') }}'; }"
                       class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition active:scale-95 cursor-pointer shadow-xs"
                       aria-label="Kembali"
                       title="Kembali ke halaman sebelumnya">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <div class="flex items-center gap-1.5">
                        <x-sayabantu-logo size="sm" :light="true" />
                    </div>

                    <div class="w-9 h-9"></div>
                </div>

                <!-- Title & Subtitle -->
                <div class="text-center pt-1 pb-2">
                    <h1 class="text-lg sm:text-xl font-extrabold text-white tracking-tight leading-snug">
                        Daftar Bantuan Tersedia
                    </h1>
                    <p class="text-xs text-blue-100 mt-1 max-w-xs mx-auto leading-relaxed">
                        Pilih jenis bantuan yang Anda butuhkan atau daftarkan diri Anda untuk mulai membantu.
                    </p>
                </div>

                <!-- Search Input Bar -->
                <div class="mt-3 relative">
                    <div class="flex items-center gap-2.5 w-full bg-white px-4 py-2.5 rounded-full shadow-md">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari jenis bantuan (misal: bersih, pindahan)..."
                            class="flex-1 bg-transparent text-xs text-gray-800 placeholder-gray-400 outline-none border-none focus:ring-0 p-0"
                        />
                        @if($search)
                            <button type="button" wire:click="$set('search', '')" class="text-gray-400 hover:text-gray-600 text-xs font-bold px-1 cursor-pointer">
                                ✕
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Soft Wave Bottom Curve -->
            <svg class="absolute -bottom-1 left-0 w-full pointer-events-none" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true" style="display: block; height: 24px; bottom: -2px;">
                <path d="M0,28 C360,76 1080,-4 1440,36 L1440,84 L0,84 Z" fill="#f8fafc"></path>
            </svg>
        </header>

        <!-- Main Scrollable Content Area -->
        <div class="flex-1 px-5 pt-3 pb-6 space-y-6">

            <!-- Hero Banner / Iklan Promosi (Sinkron dengan Dashboard & Pengaturan SuperAdmin) -->
            @php
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
                    } else {
                        $promoList = [
                            [
                                'id' => 'promo_default',
                                'layout' => 'standard',
                                'is_active' => true,
                                'badge' => 'sayabantu.com 🤝',
                                'title' => 'Butuh bantuan untuk pekerjaan kecil hingga besar?',
                                'description' => 'Pesan jasa serabutan cepat, mudah, dan terpercaya kapan saja.',
                                'button_text' => 'Kunjungi',
                                'button_link' => route('customer.helps.create'),
                                'target_blank' => false,
                                'image' => null,
                            ]
                        ];
                    }
                }

                $activePromos = array_values(array_filter($promoList, fn($p) => ($p['is_active'] ?? true) == true));
                $totalActivePromos = count($activePromos);
            @endphp

            @if($totalActivePromos > 0)
                <div class="mb-2 select-none relative group"
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

                    <div class="relative overflow-hidden rounded-2xl shadow-sm text-white"
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
                                        $pBtnText = $promo['button_text'] ?? 'Kunjungi';
                                        $pLink = !empty($promo['button_link']) ? $promo['button_link'] : route('customer.helps.create');
                                        $pTargetBlank = $promo['target_blank'] ?? false;
                                        $pImage = $promo['image'] ?? null;
                                        $pImageUrl = $pImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($pImage)
                                            ? asset('storage/' . $pImage)
                                            : asset('images/sayabantu-mascot.jpg');
                                        $pIsExternal = str_starts_with($pLink, 'http://') || str_starts_with($pLink, 'https://');
                                        $pActionLink = auth()->check() ? $pLink : ($pIsExternal ? $pLink : route('register'));
                                    @endphp

                                    @if($pLayout === 'full_image')
                                        <!-- Full Image Banner Layout (Poster Penuh Menyesuaikan Layar) -->
                                        <a href="{{ $pActionLink }}"
                                           @if($pIsExternal || $pTargetBlank) target="_blank" rel="noopener noreferrer" @else wire:navigate @endif
                                           class="flex-shrink-0 w-full relative block overflow-hidden min-h-[175px] max-h-[220px] bg-gray-900 group/slide focus:outline-none">
                                            <img src="{{ $pImageUrl }}" alt="{{ $pTitle ?: 'Promo SayaBantu' }}"
                                                 class="w-full h-full min-h-[175px] max-h-[220px] object-cover transition-transform duration-500 group-hover/slide:scale-[1.02] pointer-events-none select-none">
                                            
                                            @if(!empty($pBadge) || !empty($pTitle) || !empty($pBtnText))
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent flex items-end p-4 sm:p-5 pointer-events-none">
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
                                        <!-- Standard Card Layout (Teks di Kiri + Gambar/Maskot Proporsional di Kanan) -->
                                        <div class="flex-shrink-0 w-full p-5 flex items-center justify-between gap-3 min-h-[175px]">
                                            <div class="flex-1 max-w-[65%]">
                                                @if(!empty($pBadge))
                                                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white mb-2">
                                                        {{ $pBadge }}
                                                    </span>
                                                @endif
                                                <h2 class="text-sm font-extrabold leading-snug line-clamp-2">
                                                    {{ $pTitle ?: 'Butuh bantuan untuk pekerjaan kecil hingga besar?' }}
                                                </h2>
                                                @if(!empty($pDesc))
                                                    <p class="text-[11px] text-blue-100 mt-1 line-clamp-2 leading-relaxed">
                                                        {{ $pDesc }}
                                                    </p>
                                                @endif
                                                <a href="{{ $pActionLink }}"
                                                   @if($pIsExternal || $pTargetBlank) target="_blank" rel="noopener noreferrer" @else wire:navigate @endif
                                                   class="mt-3.5 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-[#0066FF] hover:bg-blue-50 font-bold text-xs rounded-full shadow-md active:scale-95 transition">
                                                    <span>{{ $pBtnText ?: 'Kunjungi' }}</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>

                                            <div class="w-24 h-24 sm:w-28 sm:h-28 shrink-0 flex items-center justify-center p-1">
                                                <img src="{{ $pImageUrl }}" alt="{{ $pTitle }}" class="max-w-full max-h-full object-contain rounded-xl drop-shadow-md pointer-events-none select-none">
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        @if($totalActivePromos > 1)
                            <!-- Navigation dots -->
                            <div class="absolute bottom-2.5 right-4 flex items-center gap-1.5 z-20">
                                @foreach($activePromos as $dotIdx => $dotPromo)
                                    <button type="button" @click="goTo({{ $dotIdx }})"
                                        class="h-1.5 rounded-full transition-all duration-300 cursor-pointer"
                                        :class="current === {{ $dotIdx }} ? 'w-5 bg-white' : 'w-1.5 bg-white/40 hover:bg-white/70'">
                                    </button>
                                @endforeach
                            </div>

                            <!-- Left / Right Arrows -->
                            <button type="button" @click="prev()"
                                class="absolute left-1.5 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-black/20 hover:bg-black/40 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-20 cursor-pointer"
                                title="Sebelumnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                            </button>
                            <button type="button" @click="next()"
                                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-black/20 hover:bg-black/40 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-20 cursor-pointer"
                                title="Berikutnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SECTION 1: DAFTAR KATEGORI BANTUAN -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900 tracking-tight">
                            Kategori Layanan Tersedia
                        </h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Klik salah satu bantuan di bawah untuk mendaftar & memesan:</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    @forelse($categories as $category)
                        @php
                            $targetUrl = auth()->check() 
                                ? (auth()->user()->isCustomer() ? route('customer.helps.create', ['category_id' => $category->id]) : route('dashboard'))
                                : route('register', ['category' => $category->id]);
                        @endphp
                        <a href="{{ $targetUrl }}" wire:navigate
                           class="flex flex-col justify-between p-3.5 rounded-2xl bg-white border border-gray-200/80 hover:border-[#0066FF] hover:shadow-md hover:scale-[1.01] active:scale-[0.98] transition-all group cursor-pointer">
                            <div>
                                <div class="w-11 h-11 rounded-xl bg-blue-50 text-2xl flex items-center justify-center mb-2.5 group-hover:scale-110 transition-transform shadow-2xs">
                                    <span>{{ $category->icon ?: '✨' }}</span>
                                </div>
                                <h3 class="text-xs font-extrabold text-gray-900 group-hover:text-[#0066FF] transition leading-snug line-clamp-1">
                                    {{ $category->name }}
                                </h3>
                                <p class="text-[11px] text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $category->description ?: 'Bantuan serabutan cepat, mudah, dan terpercaya.' }}
                                </p>
                            </div>

                            <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] font-bold text-[#0066FF]">
                                <span>Pilih Jasa</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-2 py-8 text-center bg-white rounded-2xl border border-dashed border-gray-300 p-6">
                            <span class="text-3xl">🔍</span>
                            <p class="text-xs font-bold text-gray-700 mt-2">Kategori tidak ditemukan</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Coba cari dengan kata kunci lain.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Sticky Bottom CTA Section (Tombol Lanjutkan) -->
        <footer class="p-4 sm:p-5 bg-white border-t border-gray-100 sticky bottom-0 z-30 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] shrink-0">
            @php
                $continueUrl = auth()->check() ? route('dashboard') : route('login');
            @endphp
            <a href="{{ $continueUrl }}" wire:navigate
               class="w-full flex items-center justify-center gap-2 py-3.5 sm:py-4 rounded-full bg-[#0066FF] hover:bg-[#0055D4] active:scale-[0.98] text-white font-bold text-base shadow-md shadow-blue-500/25 transition cursor-pointer">
                <span>Lanjutkan</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </footer>

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
    </script>
    @livewireScripts
</body>

</html>
