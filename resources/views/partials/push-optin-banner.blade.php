@auth
<div id="sb-push-optin-banner"
    x-data="{
        open: false,
        loading: false,
        checkEligibility() {
            if (!window.SayaBantuPush || typeof window.SayaBantuPush.canPromptOptIn !== 'function') {
                this.open = false;
                return;
            }
            this.open = window.SayaBantuPush.canPromptOptIn();
        },
        async activate() {
            if (this.loading || !window.SayaBantuPush) return;
            this.loading = true;
            try {
                await window.SayaBantuPush.subscribe();
                this.open = false;
                // Kirim 1 notifikasi tes native agar langsung muncul di notifikasi laptop/HP
                await window.SayaBantuPush.sendTest().catch(() => {});
                if (typeof window.sbToast === 'function') {
                    window.sbToast('Push notifikasi aktif! Update order, chat & saldo akan muncul langsung di notifikasi perangkat Anda.', 'success');
                }
            } catch (err) {
                this.checkEligibility();
                if (typeof window.sbToast === 'function') {
                    window.sbToast(err.message || 'Gagal mengaktifkan push notifikasi.', 'warning');
                }
            } finally {
                this.loading = false;
            }
        },
        dismiss() {
            if (window.SayaBantuPush && typeof window.SayaBantuPush.dismissPrompt === 'function') {
                window.SayaBantuPush.dismissPrompt();
            }
            this.open = false;
        }
    }"
    x-init="
        setTimeout(() => checkEligibility(), 1200);
        window.addEventListener('sb-push-status-changed', () => checkEligibility());
        document.addEventListener('livewire:navigated', () => setTimeout(() => checkEligibility(), 400));
    "
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed z-[9998] top-3 left-3 right-3 md:top-auto md:bottom-5 md:right-5 md:left-auto md:w-[380px] pointer-events-auto">
    <div class="bg-white/95 backdrop-blur-md border border-blue-200/90 rounded-2xl shadow-2xl p-3.5 flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0066FF] to-[#0052CC] text-white flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-extrabold text-slate-900 leading-tight">Aktifkan Notifikasi Perangkat</p>
            <p class="text-[11px] text-slate-600 leading-snug mt-0.5">
                Terima notif order, chat & saldo langsung di notifikasi laptop/HP meski web diminimize atau ditutup.
            </p>
            <div class="mt-2.5 flex items-center gap-2">
                <button type="button"
                    @click="activate()"
                    :disabled="loading"
                    class="px-3 py-1.5 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-[11px] font-bold shadow-xs transition active:scale-95 disabled:opacity-60 cursor-pointer inline-flex items-center gap-1.5">
                    <svg x-show="loading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="loading ? 'Mengaktifkan...' : 'Aktifkan Sekarang'"></span>
                </button>
                <button type="button"
                    @click="dismiss()"
                    class="px-2.5 py-1.5 rounded-xl text-[11px] font-semibold text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                    Nanti
                </button>
            </div>
        </div>
        <button type="button"
            @click="dismiss()"
            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer shrink-0"
            title="Tutup">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endauth
