    @php
        $pwaDynamicIcon = \App\Models\AppSetting::get('app_icon');
        $pwaBannerIconUrl = $pwaDynamicIcon && \Illuminate\Support\Facades\Storage::disk('public')->exists($pwaDynamicIcon)
            ? asset('storage/' . $pwaDynamicIcon)
            : '/icons/icon-192x192.png';
    @endphp

    <!-- Animated PWA Launch Splash Screen (tampil saat dibuka dari ikon aplikasi HP) -->
    <div id="sb-pwa-splash"
         aria-hidden="true"
         style="position:fixed;inset:0;z-index:2147483647;background:linear-gradient(180deg,#ffffff 0%,#f0f7ff 100%);flex-direction:column;align-items:center;justify-content:center;padding:24px;transition:opacity 0.32s ease,transform 0.32s ease;">
        <div style="position:relative;display:flex;align-items:center;justify-content:center;">
            <span class="sb-splash-ring" style="position:absolute;width:108px;height:108px;border-radius:9999px;background:rgba(2,132,199,0.2);"></span>
            <img src="{{ $pwaBannerIconUrl }}"
                 alt="SayaBantu"
                 class="sb-splash-logo"
                 style="position:relative;width:88px;height:88px;border-radius:9999px;object-fit:contain;filter:drop-shadow(0 10px 22px rgba(2,132,199,0.25));">
        </div>
        <div class="sb-splash-text" style="margin-top:18px;text-align:center;">
            <div style="font-size:24px;font-weight:900;letter-spacing:-0.02em;line-height:1.1;color:#0066FF;">
                sayabantu<span style="color:#f59e0b;font-weight:800;">.com</span>
            </div>
            <p style="margin-top:6px;font-size:12px;font-weight:600;color:#64748b;">
                Jasa Serabutan, Mudah, Cepat, Terpercaya
            </p>
        </div>
        <div style="margin-top:26px;width:96px;height:4px;border-radius:9999px;background:#dbeafe;overflow:hidden;">
            <div class="sb-splash-bar" style="width:45%;height:100%;border-radius:9999px;background:linear-gradient(90deg,#0284c7,#38bdf8);"></div>
        </div>
    </div>

    <script data-navigate-once>
        if (!window._pwaSplashHandled) {
            window._pwaSplashHandled = true;
            const splashEl = document.getElementById('sb-pwa-splash');
            const finishSplash = () => {
                try {
                    sessionStorage.setItem('sb_pwa_splash_shown', '1');
                } catch (e) {}
                if (splashEl && document.documentElement.classList.contains('sb-splash-active')) {
                    splashEl.style.opacity = '0';
                    splashEl.style.transform = 'scale(1.03)';
                    setTimeout(() => {
                        document.documentElement.classList.remove('sb-splash-active');
                    }, 320);
                } else {
                    document.documentElement.classList.remove('sb-splash-active');
                }
            };

            if (document.documentElement.classList.contains('sb-splash-active')) {
                setTimeout(finishSplash, 900);
                setTimeout(finishSplash, 1400);
            }
        }
    </script>

    @if($showPwaInstallBanner ?? true)
    <style>
        body.pwa-banner-open main {
            padding-bottom: calc(9.5rem + env(safe-area-inset-bottom, 0px));
        }
    </style>
    <!-- PWA Install Banner (Android Chromium prompt, Firefox/Mobile fallback & iOS Safari) -->
    <div id="pwa-install-banner" class="hidden fixed bottom-20 left-4 right-4 max-w-sm mx-auto bg-gray-900/95 backdrop-blur-md text-white p-4 rounded-2xl shadow-2xl border border-gray-700/80 z-[99999] flex items-center justify-between gap-3 animate-slide-up">
        <div class="flex items-center gap-2.5 min-w-0">
            <img src="{{ $pwaBannerIconUrl }}" alt="SayaBantu" class="w-9 h-9 rounded-xl shadow-xs shrink-0 object-contain bg-white">
            <div class="min-w-0">
                <h4 class="text-xs font-bold text-white leading-tight line-clamp-2">Pasang Aplikasi SayaBantu</h4>
                <p id="pwa-install-subtitle" class="text-[11px] text-gray-300 leading-tight mt-0.5">Akses cepat &amp; instan di HP Anda</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button id="pwa-install-btn" type="button" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                Install
            </button>
            <button id="pwa-dismiss-btn" type="button" aria-label="Tutup banner pasang aplikasi" class="p-1.5 text-gray-400 hover:text-white rounded-lg transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    <script data-navigate-once>
        if (!window._pwaScriptLoaded) {
            window._pwaScriptLoaded = true;
            let _fallbackTimer = null;

            // Registrasi PWA Service Worker
            if ('serviceWorker' in navigator) {
                const registerSw = () => {
                    navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
                };
                if (document.readyState === 'complete') {
                    registerSw();
                } else {
                    window.addEventListener('load', registerSw);
                }
            }

            const isStandaloneMode = () => (
                window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true ||
                document.referrer.startsWith('android-app://')
            );

            const isIosSafari = () => {
                const ua = window.navigator.userAgent || '';
                return /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            };

            const isMobileBrowser = () => {
                const ua = window.navigator.userAgent || '';
                return /android|iphone|ipad|ipod|mobile|firefox/i.test(ua) && (window.innerWidth <= 1024 || /android|iphone|ipad|ipod|mobile/i.test(ua));
            };

            const isFirefoxAndroid = () => /android/i.test(navigator.userAgent || '') && /firefox/i.test(navigator.userAgent || '');

            const isDismissedRecently = () => {
                try {
                    const dismissedAt = localStorage.getItem('pwa_dismissed_at');
                    return Boolean(dismissedAt && (Date.now() - parseInt(dismissedAt, 10)) < 86400000);
                } catch (e) {
                    return false;
                }
            };

            const hidePwaBanner = () => {
                const installBanner = document.getElementById('pwa-install-banner');
                if (installBanner) installBanner.classList.add('hidden');
                document.body.classList.remove('pwa-banner-open');
            };

            const showManualGuideBanner = () => {
                const installBanner = document.getElementById('pwa-install-banner');
                if (!installBanner || isStandaloneMode() || isDismissedRecently() || window._deferredPrompt) {
                    return;
                }

                const subtitleEl = document.getElementById('pwa-install-subtitle');
                const installBtn = document.getElementById('pwa-install-btn');

                if (isIosSafari()) {
                    if (subtitleEl) {
                        subtitleEl.textContent = 'Tap Bagikan → Tambah ke Layar Utama';
                    }
                    if (installBtn) {
                        installBtn.textContent = 'Panduan';
                        installBtn.dataset.mode = 'ios';
                        installBtn.classList.remove('hidden');
                    }
                } else if (window.isSecureContext === false) {
                    if (subtitleEl) {
                        subtitleEl.textContent = 'Buka lewat HTTPS, lalu tap menu (⋮) → Install';
                    }
                    if (installBtn) {
                        installBtn.textContent = 'Panduan';
                        installBtn.dataset.mode = 'manual';
                        installBtn.classList.remove('hidden');
                    }
                } else {
                    if (subtitleEl) {
                        subtitleEl.textContent = 'Tap menu (⋮) → Tambah ke layar utama';
                    }
                    if (installBtn) {
                        installBtn.textContent = 'Panduan';
                        installBtn.dataset.mode = 'manual';
                        installBtn.classList.remove('hidden');
                    }
                }

                installBanner.classList.remove('hidden');
                document.body.classList.add('pwa-banner-open');
            };

            const syncPwaBanner = () => {
                const installBanner = document.getElementById('pwa-install-banner');
                if (!installBanner) {
                    document.body.classList.remove('pwa-banner-open');
                    return;
                }

                if (_fallbackTimer) {
                    clearTimeout(_fallbackTimer);
                    _fallbackTimer = null;
                }

                if (isStandaloneMode() || isDismissedRecently()) {
                    hidePwaBanner();
                    return;
                }

                const subtitleEl = document.getElementById('pwa-install-subtitle');
                const installBtn = document.getElementById('pwa-install-btn');

                if (window._deferredPrompt) {
                    if (subtitleEl) subtitleEl.textContent = 'Akses cepat & instan di HP Anda';
                    if (installBtn) {
                        installBtn.textContent = 'Install';
                        installBtn.dataset.mode = 'prompt';
                        installBtn.classList.remove('hidden');
                    }
                    installBanner.classList.remove('hidden');
                    document.body.classList.add('pwa-banner-open');
                    return;
                }

                if (isIosSafari() || isFirefoxAndroid()) {
                    showManualGuideBanner();
                    return;
                }

                if (isMobileBrowser()) {
                    // Beri jeda singkat untuk Chrome Android jika beforeinstallprompt sedang diproses;
                    // jika tidak muncul otomatis (mis. browser non-Chrome/HTTP/throttled), tampilkan panduan manual.
                    _fallbackTimer = setTimeout(showManualGuideBanner, 1200);
                    return;
                }

                hidePwaBanner();
            };

            document.addEventListener('click', async (event) => {
                const installBtnEl = event.target.closest('#pwa-install-btn');
                if (installBtnEl) {
                    if (window._deferredPrompt) {
                        hidePwaBanner();
                        try {
                            await window._deferredPrompt.prompt();
                            await window._deferredPrompt.userChoice;
                        } catch (e) {}
                        window._deferredPrompt = null;
                        return;
                    }

                    const mode = installBtnEl.dataset.mode || 'manual';
                    const stepRow = (num, text) => `
                        <div style="display:flex;align-items:flex-start;gap:10px;text-align:left;">
                            <span style="width:20px;height:20px;border-radius:9999px;background:#0066FF;color:#fff;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">${num}</span>
                            <span style="font-size:12.5px;line-height:1.45;color:#334155;">${text}</span>
                        </div>
                    `;
                    const insecureNote = window.isSecureContext === false
                        ? '<p style="margin-top:10px;padding:8px 10px;border-radius:10px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:11px;line-height:1.4;text-align:left;">Catatan: Pastikan halaman dibuka melalui alamat <strong>HTTPS</strong> agar aplikasi dapat dipasang.</p>'
                        : '';

                    const guideHtml = mode === 'ios'
                        ? `<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px;display:flex;flex-direction:column;gap:10px;">
                               ${stepRow(1, 'Tap ikon <strong>Bagikan (Share)</strong> di bagian bawah browser Safari.')}
                               ${stepRow(2, 'Gulir ke bawah lalu pilih <strong>Tambah ke Layar Utama</strong>.')}
                               ${stepRow(3, 'Tap <strong>Tambah</strong> di pojok kanan atas untuk menyimpan ikon aplikasi.')}
                           </div>`
                        : `<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px;display:flex;flex-direction:column;gap:10px;">
                               ${stepRow(1, 'Tap ikon menu titik tiga <strong>(⋮)</strong> di pojok browser HP Anda.')}
                               ${stepRow(2, 'Pilih <strong>Instal aplikasi</strong> atau <strong>Tambah ke layar utama</strong>.')}
                               ${stepRow(3, 'Tap <strong>Pasang / Tambah</strong> agar ikon SayaBantu muncul di beranda HP.')}
                           </div>${insecureNote}`;

                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            title: 'Cara Pasang Aplikasi',
                            html: guideHtml,
                            icon: 'info',
                            iconColor: '#0066FF',
                            confirmButtonText: 'Mengerti',
                            buttonsStyling: false,
                            didOpen: () => {
                                const container = window.Swal.getContainer();
                                if (container) container.style.zIndex = '100000';
                            },
                            customClass: {
                                popup: 'rounded-3xl shadow-2xl p-6 border border-gray-100 font-sans max-w-sm',
                                title: 'text-base font-extrabold text-gray-900',
                                htmlContainer: 'mt-3',
                                actions: 'w-full mt-4',
                                confirmButton: 'w-full py-3 px-5 rounded-xl font-bold text-xs bg-[#0066FF] hover:bg-[#0052cc] text-white shadow-md transition active:scale-95 cursor-pointer',
                            }
                        });
                    }
                    return;
                }

                if (event.target.closest('#pwa-dismiss-btn')) {
                    hidePwaBanner();
                    try {
                        localStorage.setItem('pwa_dismissed_at', Date.now().toString());
                    } catch (e) {}
                }
            });

            window.addEventListener('sb-pwa-prompt-ready', syncPwaBanner);
            window.addEventListener('sb-pwa-installed', () => {
                hidePwaBanner();
                try {
                    localStorage.setItem('pwa_installed_notified', '1');
                } catch (e) {}

                const showInstalledToast = () => {
                    const msg = 'Aplikasi SayaBantu berhasil ditambahkan ke layar beranda.';
                    if (typeof window.sbToast === 'function') {
                        window.sbToast(msg, 'success');
                    } else if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            title: 'Berhasil Dipasang!',
                            text: msg,
                            icon: 'success',
                            timer: 3500,
                            showConfirmButton: false
                        });
                    }
                };

                setTimeout(showInstalledToast, 250);
            });
            document.addEventListener('DOMContentLoaded', syncPwaBanner);
            document.addEventListener('livewire:navigated', syncPwaBanner);

            if (isStandaloneMode()) {
                try {
                    if (localStorage.getItem('pwa_standalone_welcomed') !== '1') {
                        localStorage.setItem('pwa_standalone_welcomed', '1');
                        if (localStorage.getItem('pwa_installed_notified') !== '1') {
                            localStorage.setItem('pwa_installed_notified', '1');
                            setTimeout(() => {
                                if (typeof window.sbToast === 'function') {
                                    window.sbToast('Aplikasi SayaBantu berhasil dipasang di layar beranda!', 'success');
                                }
                            }, 500);
                        }
                    }
                } catch (e) {}
            }

            if (document.readyState !== 'loading') {
                syncPwaBanner();
            }
        }
    </script>
