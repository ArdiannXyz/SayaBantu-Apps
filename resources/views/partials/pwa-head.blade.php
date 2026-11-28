    <!-- PWA & Favicon Settings -->
    <link rel="manifest" href="/manifest.json" crossorigin="use-credentials">
    @php
        $dynamicAppIcon = \App\Models\AppSetting::get('app_icon');
        $dynamicAppIconUrl = $dynamicAppIcon && \Illuminate\Support\Facades\Storage::disk('public')->exists($dynamicAppIcon)
            ? asset('storage/' . $dynamicAppIcon)
            : null;
    @endphp
    @if($dynamicAppIconUrl)
        <link rel="icon" href="{{ $dynamicAppIconUrl }}">
        <link rel="apple-touch-icon" href="{{ $dynamicAppIconUrl }}">
    @else
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/icon-512x512.png') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('icons/icon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/apple-touch-icon.png') }}">
    @endif
    <meta name="theme-color" content="{{ $pwaThemeColor ?? '#ffffff' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SayaBantu">
    <style>
        #sb-pwa-splash {
            display: none;
        }
        html.sb-splash-active #sb-pwa-splash {
            display: flex !important;
        }
        @keyframes sbSplashPop {
            0% { opacity: 0; transform: scale(0.72) translateY(10px); }
            65% { opacity: 1; transform: scale(1.06) translateY(-2px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes sbSplashRing {
            0% { transform: scale(0.85); opacity: 0.55; }
            100% { transform: scale(1.55); opacity: 0; }
        }
        @keyframes sbSplashFadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes sbSplashBar {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(220%); }
        }
        .sb-splash-logo {
            animation: sbSplashPop 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        .sb-splash-ring {
            animation: sbSplashRing 1.3s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        }
        .sb-splash-text {
            opacity: 0;
            animation: sbSplashFadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.18s forwards;
        }
        .sb-splash-bar {
            animation: sbSplashBar 1s ease-in-out infinite;
        }
    </style>
    <script data-navigate-once>
        if (!window._pwaEarlyCapture) {
            window._pwaEarlyCapture = true;
            window._deferredPrompt = window._deferredPrompt || null;

            try {
                const isStandaloneLaunch = window.matchMedia('(display-mode: standalone)').matches
                    || window.navigator.standalone === true
                    || document.referrer.startsWith('android-app://');
                if (isStandaloneLaunch && sessionStorage.getItem('sb_pwa_splash_shown') !== '1') {
                    document.documentElement.classList.add('sb-splash-active');
                }
            } catch (e) {}

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                window._deferredPrompt = e;
                window.dispatchEvent(new CustomEvent('sb-pwa-prompt-ready'));
            });

            window.addEventListener('appinstalled', () => {
                window._deferredPrompt = null;
                window.dispatchEvent(new CustomEvent('sb-pwa-installed'));
            });
        }
    </script>
