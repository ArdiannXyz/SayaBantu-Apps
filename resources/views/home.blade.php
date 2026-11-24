<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="only light">
    <meta name="supported-color-schemes" content="only light">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            color-scheme: light !important;
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        @media (prefers-color-scheme: dark) {
            :root, html, body {
                color-scheme: light !important;
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
    </style>
</head>

<body class="min-h-screen min-h-[100dvh] bg-slate-100 font-sans text-slate-900 antialiased flex flex-col items-center sm:justify-start sm:py-4 sm:px-4">
    <livewire:home.index />

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
            window.visitorLocationHandler = function(initialLocation = 'Kabupaten Bantul') {
                return {
                    isDetecting: false,
                    locationName: initialLocation || 'Kabupaten Bantul',

                    init() {
                        // 1. Ambil cache lokasi tersimpan (jika valid)
                        const cached = localStorage.getItem('sayabantu_visitor_city');
                        if (cached && !cached.toLowerCase().includes('baturaden') && !cached.toLowerCase().includes('baturraden')) {
                            this.locationName = cached;
                        } else {
                            this.locationName = initialLocation || 'Kabupaten Bantul';
                        }

                        // 2. Langsung deteksi lokasi pengunjung secara otomatis saat halaman dibuka
                        this.detectLocationDirectly(false);
                    },

                    async detectLocationDirectly(isManualClick = false) {
                        this.isDetecting = true;

                        // Langkah A: Coba Geolocation GPS browser (paling presisi)
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                async (pos) => {
                                    const lat = pos.coords.latitude;
                                    const lon = pos.coords.longitude;
                                    let resolvedName = '';

                                    // Coba OpenStreetMap Nominatim reverse geocode
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

                                    // Jika Nominatim belum berhasil, coba BigDataCloud reverse geocode
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

                                    if (resolvedName) {
                                        this.applyLocation(resolvedName);
                                    }
                                    this.isDetecting = false;
                                },
                                async (err) => {
                                    // Jika izin GPS diblokir / timeout, otomatis fallback ke IP Geolocation tanpa popup modal
                                    await this.fallbackIpLocation();
                                    this.isDetecting = false;
                                },
                                {
                                    enableHighAccuracy: true,
                                    timeout: 8000,
                                    maximumAge: 60000
                                }
                            );
                        } else {
                            await this.fallbackIpLocation();
                            this.isDetecting = false;
                        }
                    },

                    async fallbackIpLocation() {
                        try {
                            const res = await fetch('https://api.bigdatacloud.net/data/reverse-geocode-client?localityLanguage=id');
                            if (res.ok) {
                                const data = await res.json();
                                let name = '';
                                const admin = data.localityInfo?.administrative || [];
                                const regencyObj = admin.find(x => x.order === 8) || admin.find(x => x.adminLevel === 5);
                                const regency = regencyObj?.name || '';
                                const city = data.city || '';
                                const locality = data.locality || '';

                                if (locality && regency && locality.toLowerCase() !== regency.toLowerCase()) {
                                    name = `${locality}, ${regency}`;
                                } else if (regency) {
                                    name = regency.toLowerCase().startsWith('kota') || regency.toLowerCase().startsWith('kabupaten') ? regency : `Kabupaten ${regency}`;
                                } else if (city) {
                                    name = city;
                                }

                                if (name) {
                                    this.applyLocation(name);
                                }
                            }
                        } catch (e) {
                            console.warn('Fallback IP location error:', e);
                        }
                    },

                    applyLocation(cityName) {
                        if (!cityName) return;
                        this.locationName = cityName;
                        localStorage.setItem('sayabantu_visitor_city', cityName);
                        if (typeof this.$wire !== 'undefined' && this.$wire.setLocation) {
                            this.$wire.setLocation(cityName);
                        }
                    }
                };
            };
        }
    </script>
    @livewireScripts
</body>

</html>
