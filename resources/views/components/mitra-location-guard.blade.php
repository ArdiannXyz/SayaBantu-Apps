<div
    x-data="{
        gpsState: (window._sbMitraGps && (Date.now() - window._sbMitraGps.ts) < 120000) ? 'granted' : 'checking',
        isRequesting: false,
        errorText: '',

        init() {
            this.checkAndRequest(false);

            window.addEventListener('mitra-request-gps', () => {
                this.requestLocation();
            });

            window.addEventListener('focus', () => {
                if (this.gpsState !== 'granted') {
                    this.checkAndRequest(false);
                }
            });

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && this.gpsState !== 'granted') {
                    this.checkAndRequest(false);
                }
            });
        },

        notifyState(state) {
            this.gpsState = state;
            window._sbMitraGpsState = state;
            window.dispatchEvent(new CustomEvent('sb-mitra-gps-changed', {
                detail: { state, coords: window._sbMitraGps || null }
            }));
        },

        async checkAndRequest(isManual = false) {
            if (!('geolocation' in navigator)) {
                this.errorText = 'Browser atau perangkat ini tidak mendukung GPS / geolokasi.';
                this.notifyState('unsupported');
                return;
            }

            if (navigator.permissions && typeof navigator.permissions.query === 'function') {
                try {
                    const perm = await navigator.permissions.query({ name: 'geolocation' });
                    if (perm.state === 'denied') {
                        this.errorText = 'Izin lokasi diblokir browser. Buka pengaturan izin situs (ikon gembok/info di bilah alamat), pilih Izinkan Lokasi, lalu coba lagi.';
                        this.notifyState('denied');
                        return;
                    }
                    if (perm.state === 'prompt' && !isManual && window._sbMitraGpsPrompted) {
                        this.notifyState('prompt');
                        return;
                    }
                    if (perm.state === 'prompt') {
                        window._sbMitraGpsPrompted = true;
                        this.notifyState('prompt');
                        this.fetchPosition();
                        return;
                    }
                    // granted
                    this.fetchPosition();
                    return;
                } catch (e) {
                    // Fallback below when Permissions API fails
                }
            }

            if (!isManual && window._sbMitraGpsPrompted && this.gpsState !== 'granted') {
                return;
            }
            window._sbMitraGpsPrompted = true;
            this.fetchPosition();
        },

        requestLocation() {
            this.errorText = '';
            this.checkAndRequest(true);
        },

        fetchPosition() {
            this.isRequesting = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.isRequesting = false;
                    this.errorText = '';
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    window._sbMitraGps = { lat, lng, ts: Date.now() };
                    this.notifyState('granted');
                    if (typeof $wire !== 'undefined' && typeof $wire.setCoordinates === 'function') {
                        $wire.setCoordinates(lat, lng);
                    }
                },
                (err) => {
                    this.isRequesting = false;
                    if (err && err.code === 1) {
                        this.errorText = 'Izin lokasi ditolak. Aktifkan izin lokasi di browser/HP Anda agar bisa melihat dan mengambil order radius 10 km.';
                        this.notifyState('denied');
                    } else if (err && err.code === 2) {
                        this.errorText = 'Lokasi GPS tidak terdeteksi. Pastikan fitur Lokasi/GPS di perangkat Anda sudah dinyalakan.';
                        this.notifyState('denied');
                    } else {
                        this.errorText = 'Gagal mendapatkan lokasi (timeout). Pastikan GPS aktif lalu tekan Coba Lagi.';
                        this.notifyState('prompt');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        }
    }"
>
    <!-- Banner / Guard Wajib Lokasi GPS (Muncul sampai lokasi aktif) -->
    <div x-cloak x-show="gpsState !== 'granted'" class="mb-4 rounded-2xl p-4 border-2 bg-white shadow-sm transition-all"
         :class="gpsState === 'denied' || gpsState === 'unsupported' ? 'border-rose-300 bg-rose-50/40' : 'border-[#0066FF]/40 bg-blue-50/40'">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-white shadow-xs"
                 :class="gpsState === 'denied' || gpsState === 'unsupported' ? 'bg-rose-500' : 'bg-[#0066FF]'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <h3 class="font-extrabold text-xs text-gray-900">Wajib Aktifkan Lokasi GPS</h3>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold"
                          :class="gpsState === 'denied' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-[#0066FF]'"
                          x-text="gpsState === 'denied' ? 'Diblokir / Mati' : (isRequesting ? 'Mendeteksi...' : 'Belum Aktif')"></span>
                </div>
                <p class="text-[11px] text-gray-600 mt-1 leading-relaxed"
                   x-text="errorText || 'Aktifkan lokasi GPS perangkat Anda untuk menampilkan dan mengambil pesanan bantuan dalam radius 10 km di sekitar Anda.'">
                </p>

                <div class="mt-3 flex items-center gap-2 flex-wrap">
                    <button type="button"
                        @click="requestLocation()"
                        :disabled="isRequesting || gpsState === 'unsupported'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-white shadow-xs transition active:scale-95 cursor-pointer disabled:opacity-60 inline-flex items-center gap-1.5"
                        :class="gpsState === 'denied' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-[#0066FF] hover:bg-[#0052cc]'">
                        <template x-if="isRequesting">
                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <span x-text="isRequesting ? 'Mengambil Lokasi...' : (gpsState === 'denied' ? 'Coba Aktifkan Lagi' : 'Aktifkan Lokasi Sekarang')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
