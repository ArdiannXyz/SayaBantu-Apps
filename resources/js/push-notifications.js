// =========================================================================
// SayaBantu Web Push Notification Manager (VAPID / RFC 8292)
// =========================================================================

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

export const SayaBantuPush = {
    isSupported() {
        return (
            window.isSecureContext !== false &&
            'serviceWorker' in navigator &&
            'PushManager' in window &&
            'Notification' in window
        );
    },

    isAuthenticated() {
        if (document.getElementById('sb-push-optin-banner')) {
            return true;
        }
        const authMeta = document.querySelector('meta[name="sb-authenticated"]');
        return authMeta ? authMeta.getAttribute('content') === '1' : false;
    },

    canPromptOptIn() {
        if (!this.isSupported() || !this.isAuthenticated()) return false;
        if (Notification.permission !== 'default') return false;
        try {
            const dismissedAt = localStorage.getItem('sb_push_optin_dismissed_at');
            if (dismissedAt && (Date.now() - parseInt(dismissedAt, 10)) < 86400000) {
                return false;
            }
        } catch (e) {}
        return true;
    },

    dismissPrompt() {
        try {
            localStorage.setItem('sb_push_optin_dismissed_at', String(Date.now()));
        } catch (e) {}
    },

    getPermissionState() {
        if (!('Notification' in window)) return 'unsupported';
        return Notification.permission; // 'default', 'granted', 'denied'
    },

    async registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return null;
        try {
            return await navigator.serviceWorker.register('/sw.js');
        } catch (e) {
            console.warn('[SayaBantuPush] Gagal mendaftarkan sw.js:', e);
            return null;
        }
    },

    async getRegistration() {
        if (!('serviceWorker' in navigator)) return null;
        const existing = await navigator.serviceWorker.getRegistration('/');
        if (!existing) {
            await this.registerServiceWorker();
        }
        return await navigator.serviceWorker.ready;
    },

    async getSubscription() {
        try {
            const reg = await this.getRegistration();
            if (!reg || !reg.pushManager) return null;
            return await reg.pushManager.getSubscription();
        } catch (e) {
            console.warn('[SayaBantuPush] Gagal mengecek subscription:', e);
            return null;
        }
    },

    getEndpoint(metaName, fallbackPath) {
        const val = document.querySelector(`meta[name="${metaName}"]`)?.getAttribute('content');
        return val && val.trim() ? val.trim() : fallbackPath;
    },

    getMetaVapidKey() {
        const key = document.querySelector('meta[name="sb-vapid-key"]')?.getAttribute('content');
        return key && key.trim() ? key.trim() : null;
    },

    async getPublicKey() {
        const endpoint = this.getEndpoint('sb-push-key-url', '/api/push/key');
        try {
            const res = await fetch(endpoint, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (res.ok) {
                const data = await res.json();
                if (data && data.publicKey) {
                    return data.publicKey;
                }
            } else {
                console.warn(`[SayaBantuPush] Endpoint ${endpoint} merespons HTTP ${res.status}`);
            }
        } catch (e) {
            console.warn('[SayaBantuPush] Gagal mengambil VAPID Public Key dari endpoint:', e);
        }

        return this.getMetaVapidKey();
    },

    async subscribe() {
        if (!this.isSupported()) {
            throw new Error('Browser atau perangkat Anda tidak mendukung Web Push Notification.');
        }

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            throw new Error(
                permission === 'denied'
                    ? 'Izin notifikasi diblokir pada browser Anda. Silakan buka pengaturan browser / info situs dan aktifkan izin notifikasi.'
                    : 'Izin notifikasi belum disetujui.'
            );
        }

        this.listenForegroundPush();
        await this.registerServiceWorker();
        const reg = await this.getRegistration();
        if (!reg) throw new Error('Service Worker belum siap.');

        const publicKey = await this.getPublicKey();
        if (!publicKey) throw new Error('Gagal mendapatkan VAPID Public Key dari server.');

        const applicationServerKey = urlBase64ToUint8Array(publicKey);

        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            });
        }

        await this.sendSubscriptionToServer(sub);
        window.dispatchEvent(new CustomEvent('sb-push-status-changed', {
            detail: { subscribed: true, permission }
        }));
        return sub;
    },

    async parseJsonResponse(response, fallbackError) {
        const rawText = await response.text();
        let data = null;

        try {
            data = rawText ? JSON.parse(rawText) : {};
        } catch (e) {
            throw new Error(fallbackError || `Respons server tidak valid (HTTP ${response.status}). Silakan muat ulang halaman.`);
        }

        if (!response.ok || (data && data.success === false && !data.subscription_id && !data.publicKey)) {
            throw new Error((data && data.message) || fallbackError || `Permintaan gagal (HTTP ${response.status}).`);
        }

        return data;
    },

    async sendSubscriptionToServer(subscription) {
        const subData = subscription.toJSON();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const endpoint = this.getEndpoint('sb-push-subscribe-url', '/api/push/subscribe');

        const response = await fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify(subData)
        });

        return await this.parseJsonResponse(response, 'Gagal menyimpan subscription ke server.');
    },

    async unsubscribe() {
        const sub = await this.getSubscription();
        if (sub) {
            const subData = sub.toJSON();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const endpoint = this.getEndpoint('sb-push-unsubscribe-url', '/api/push/unsubscribe');

            try {
                await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                    body: JSON.stringify({ endpoint: sub.endpoint })
                });
            } catch (e) {}

            await sub.unsubscribe();
        }
        window.dispatchEvent(new CustomEvent('sb-push-status-changed', {
            detail: { subscribed: false, permission: this.getPermissionState() }
        }));
        return true;
    },

    async sendTest() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const endpoint = this.getEndpoint('sb-push-test-url', '/api/push/test');
        const res = await fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            }
        });
        return await this.parseJsonResponse(res, 'Gagal mengirim notifikasi tes.');
    },

    // Inisialisasi otomatis jika izin sudah diberikan sebelumnya
    async autoSync() {
        this.listenForegroundPush();
        if (!this.isSupported() || !this.isAuthenticated()) return;
        if (Notification.permission === 'granted') {
            try {
                const reg = await this.getRegistration();
                if (!reg || !reg.pushManager) return;

                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    const publicKey = await this.getPublicKey();
                    if (publicKey) {
                        sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(publicKey)
                        });
                    }
                }
                if (sub) {
                    await this.sendSubscriptionToServer(sub);
                    window.dispatchEvent(new CustomEvent('sb-push-status-changed', {
                        detail: { subscribed: true, permission: 'granted' }
                    }));
                }
            } catch (e) {
                // Silent catch untuk auto sync
            }
        }
    },

    // Sinkronisasi event push Service Worker ke halaman aktif tanpa menduplikasi notifikasi native OS
    listenForegroundPush() {
        if (window._sbSwPushListenerBound || !('serviceWorker' in navigator)) return;
        window._sbSwPushListenerBound = true;

        navigator.serviceWorker.addEventListener('message', (event) => {
            if (!event || !event.data) return;

            const { type, payload = {} } = event.data;

            if (type === 'SAYABANTU_PUSH_SYNC') {
                window.dispatchEvent(new CustomEvent('sb-push-notification-sync', { detail: payload }));
                if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                    try {
                        window.Livewire.dispatch('refreshNotifications');
                    } catch (e) {}
                }
                return;
            }

            if (type === 'SAYABANTU_PUSH_RECEIVED' && document.visibilityState === 'visible') {
                if (typeof window.sbShowPushToast === 'function') {
                    window.sbShowPushToast({
                        title: payload.title || 'SayaBantu',
                        message: payload.body || 'Anda memiliki notifikasi baru.',
                        url: payload.url || '#',
                        type: payload.type || 'info',
                        timeout: 6500,
                        sound: true,
                    });
                }
            }
        });
    }
};

window.SayaBantuPush = SayaBantuPush;
export default SayaBantuPush;
