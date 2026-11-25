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
            <div x-data="visitorLocationHandler(@js($userLocation))"
                 x-init="init()"
                 class="relative">
                <button type="button"
                     @click="detectLocationDirectly(true)"
                     :disabled="isDetecting"
                     title="Klik untuk deteksi ulang lokasi GPS Anda langsung"
                     class="w-full bg-white rounded-full px-4 py-2.5 shadow-xs border border-slate-200/90 flex items-center justify-between gap-2 transition hover:border-[#0066FF]/60 hover:shadow-sm cursor-pointer select-none text-left">
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
                        <div class="min-w-0 flex flex-col">
                            <span class="text-xs font-bold text-slate-800 truncate" x-text="locationName">{{ $userLocation ?? 'Kabupaten Bantul' }}</span>
                            <span class="text-[10px] text-slate-400 font-medium" x-show="isDetecting" x-cloak>Mendeteksi lokasi GPS...</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold text-[#0066FF] bg-blue-50 border border-blue-100 shrink-0 flex items-center gap-1.5">
                        <template x-if="isDetecting">
                            <svg class="animate-spin w-3 h-3 text-[#0066FF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <template x-if="!isDetecting">
                            <svg class="w-3 h-3 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </template>
                        <span>Radius {{ $radius ?? 5 }} km</span>
                    </span>
                </button>
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
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                            <div class="w-5 h-5 rounded-md bg-blue-50 text-[#0066FF] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-slate-600">Saldo Dompet</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1 break-words">
                            {{ format_rupiah($balance, false) }}
                        </h2>
                    </div>

                    <a href="{{ auth()->check() ? route('customer.topup') : route('login') }}" wire:navigate
                       class="px-4 py-2 rounded-full text-xs font-bold text-white bg-[#0066FF] hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-95 transition flex items-center gap-1.5 shrink-0 whitespace-nowrap cursor-pointer">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <div class="grid grid-cols-3 min-[380px]:grid-cols-4 gap-2 min-[380px]:gap-2.5">
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
                            <div class="col-span-3 min-[380px]:col-span-4 py-6 text-center">
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
                                        {{ format_rupiah($order->final_cost ?? $order->budget, false) }}
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

        <!-- 6. BOTTOM NAVIGATION BAR (Beranda, Buat Order, Pesanan, Chat, Profil) -->
        <x-customer-bottom-nav />

    </main>
