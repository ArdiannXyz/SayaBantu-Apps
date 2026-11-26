<div class="space-y-4" x-data="{
    playNotif() { if (typeof window.playNotifChime === 'function') window.playNotifChime(true); },
    playRing() { if (typeof window.playRingChime === 'function') window.playRingChime(true); },
    vibrate() { if (typeof window.triggerVibrate === 'function') window.triggerVibrate(true); },
    pushSupported: false,
    pushPermission: 'default',
    pushSubscribed: false,
    loadingPush: false,

    async checkPushStatus() {
        if (!window.SayaBantuPush) return;
        this.pushSupported = window.SayaBantuPush.isSupported();
        this.pushPermission = window.SayaBantuPush.getPermissionState();
        const sub = await window.SayaBantuPush.getSubscription();
        this.pushSubscribed = !!sub;
    },

    async enablePush() {
        this.loadingPush = true;
        try {
            await window.SayaBantuPush.subscribe();
            await this.checkPushStatus();
            await window.SayaBantuPush.sendTest().catch(() => {});
            if (window.sbToast) {
                window.sbToast('Push notifikasi berhasil diaktifkan di perangkat ini!', 'success');
            }
        } catch (e) {
            if (window.sbToast) {
                window.sbToast(e.message, 'error');
            } else {
                alert(e.message);
            }
        } finally {
            this.loadingPush = false;
        }
    },

    async disablePush() {
        this.loadingPush = true;
        try {
            await window.SayaBantuPush.unsubscribe();
            await this.checkPushStatus();
            if (window.sbToast) {
                window.sbToast('Push notifikasi dinonaktifkan.', 'info');
            }
        } catch (e) {
            if (window.sbToast) {
                window.sbToast(e.message, 'error');
            }
        } finally {
            this.loadingPush = false;
        }
    },

    async testPush() {
        this.loadingPush = true;
        const sentAt = Date.now();
        try {
            const res = await window.SayaBantuPush.sendTest();
            // Tunggu sebentar jika Service Worker menampilkan kartu push foreground;
            // hanya tampilkan toast teks bila belum ada kartu push yang muncul.
            setTimeout(() => {
                const recentPushShown = window._sbLastPushToast && window._sbLastPushToast.time >= sentAt;
                if (!recentPushShown && window.sbToast) {
                    window.sbToast(res.message || 'Notifikasi tes berhasil dikirim!', 'success');
                }
            }, 1200);
        } catch (e) {
            if (window.sbToast) {
                window.sbToast(e.message, 'error');
            } else {
                alert(e.message);
            }
        } finally {
            this.loadingPush = false;
        }
    }
}"
x-init="checkPushStatus()"
@play-preview.window="
    if ($event.detail.setting === 'sound') playNotif();
    if ($event.detail.setting === 'soundCall') playRing();
    if ($event.detail.setting === 'vibrate') vibrate();
">
    @if(session()->has('message'))
        <div class="p-3.5 bg-green-50 border border-green-200 rounded-xl text-xs font-semibold text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- Card Push Notifikasi Perangkat (HP & Browser) -->
    <div class="bg-gradient-to-br from-blue-50/70 to-indigo-50/40 border border-blue-200/80 rounded-2xl p-4 transition-all shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#0066FF] text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <h3 class="font-bold text-sm text-slate-900 leading-tight">Push Notifikasi HP & Web</h3>
                    <!-- Status Badge -->
                    <template x-if="pushPermission === 'granted' && pushSubscribed">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Aktif
                        </span>
                    </template>
                    <template x-if="pushPermission === 'denied'">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                            Diblokir Browser
                        </span>
                    </template>
                    <template x-if="pushPermission !== 'denied' && !(pushPermission === 'granted' && pushSubscribed)">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Belum Aktif
                        </span>
                    </template>
                </div>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    Terima info pesanan, chat, dan penarikan saldo secara langsung di layar HP Anda meskipun web sedang ditutup.
                </p>

                <!-- Action Buttons -->
                <div class="mt-3 flex items-center gap-2 flex-wrap">
                    <template x-if="pushPermission !== 'granted' || !pushSubscribed">
                        <button type="button"
                            @click="enablePush()"
                            :disabled="loadingPush || !pushSupported || pushPermission === 'denied'"
                            class="px-3.5 py-1.5 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white text-xs font-bold shadow-xs active:scale-95 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1.5">
                            <template x-if="loadingPush">
                                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </template>
                            <span>Aktifkan di Perangkat Ini</span>
                        </button>
                    </template>

                    <template x-if="pushPermission === 'granted' && pushSubscribed">
                        <div class="flex items-center gap-2 flex-wrap">
                            @if(in_array(auth()->user()?->role, ['super_admin', 'superadmin', 'admin', 'admin_wilayah']))
                                <button type="button"
                                    @click="testPush()"
                                    :disabled="loadingPush"
                                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-[#0066FF] border border-blue-200 text-xs font-bold shadow-2xs active:scale-95 transition disabled:opacity-50 flex items-center gap-1.5">
                                    <template x-if="loadingPush">
                                        <svg class="animate-spin w-3 h-3 text-[#0066FF]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </template>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>Kirim Tes Notifikasi</span>
                                </button>
                            @endif
                            <button type="button"
                                @click="disablePush()"
                                :disabled="loadingPush"
                                class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-rose-600 font-medium transition">
                                Nonaktifkan
                            </button>
                        </div>
                    </template>
                </div>

                <template x-if="pushPermission === 'denied'">
                    <p class="text-[11px] text-rose-600 mt-2 bg-rose-50/80 p-2 rounded-lg border border-rose-100">
                        ⚠️ Izin notifikasi diblokir oleh browser. Silakan klik ikon gembok / info di bilah alamat browser, lalu ubah <strong>Notifikasi</strong> menjadi <strong>Izinkan</strong>.
                    </p>
                </template>
            </div>
        </div>
    </div>

    <!-- Notifikasi Umum -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <div class="pr-4">
            <span class="font-semibold text-sm text-gray-900 block">Notifikasi Umum</span>
            <span class="text-xs text-gray-400">Pemberitahuan bantuan baru dan info penting</span>
        </div>
        <button type="button" wire:click="updateSetting('generalNotification')"
            onclick="if('Notification' in window && Notification.permission !== 'granted') Notification.requestPermission();"
            style="background-color: {{ $generalNotification ? '#0066FF' : '#cbd5e1' }}; width: 44px; height: 24px;"
            class="relative inline-flex flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer focus:outline-none shadow-inner">
            <span style="transform: translateX({{ $generalNotification ? '22px' : '2px' }});"
                class="inline-block h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200"></span>
        </button>
    </div>

    <!-- Suara Notifikasi -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <div class="pr-4">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-sm text-gray-900 block">Suara Notifikasi</span>
                <button type="button" @click="playNotif()" class="px-2 py-0.5 text-[11px] bg-blue-50 text-[#0066FF] rounded-md font-medium hover:bg-blue-100 transition flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                    <span>Uji Suara</span>
                </button>
            </div>
            <span class="text-xs text-gray-400">Bunyikan nada saat ada notifikasi masuk</span>
        </div>
        <button type="button" wire:click="updateSetting('sound')"
            style="background-color: {{ $sound ? '#0066FF' : '#cbd5e1' }}; width: 44px; height: 24px;"
            class="relative inline-flex flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer focus:outline-none shadow-inner">
            <span style="transform: translateX({{ $sound ? '22px' : '2px' }});"
                class="inline-block h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200"></span>
        </button>
    </div>

    <!-- Nada Dering Bantuan Urgent -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <div class="pr-4">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-sm text-gray-900 block">Nada Dering Bantuan Urgent</span>
                <button type="button" @click="playRing()" class="px-2 py-0.5 text-[11px] bg-blue-50 text-[#0066FF] rounded-md font-medium hover:bg-blue-100 transition flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>Uji Dering</span>
                </button>
            </div>
            <span class="text-xs text-gray-400">Bunyi dering khusus saat ada permintaan bantuan prioritas / urgent masuk</span>
        </div>
        <button type="button" wire:click="updateSetting('soundCall')"
            style="background-color: {{ $soundCall ? '#0066FF' : '#cbd5e1' }}; width: 44px; height: 24px;"
            class="relative inline-flex flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer focus:outline-none shadow-inner">
            <span style="transform: translateX({{ $soundCall ? '22px' : '2px' }});"
                class="inline-block h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200"></span>
        </button>
    </div>

    <!-- Getar (Vibrate) -->
    <div class="flex items-center justify-between py-3 {{ !in_array(auth()->user()?->role, ['super_admin', 'superadmin', 'admin', 'admin_wilayah']) ? 'border-b border-gray-100' : '' }}">
        <div class="pr-4">
            <span class="font-semibold text-sm text-gray-900 block">Getar (Vibrate)</span>
            <span class="text-xs text-gray-400">Getarkan perangkat saat ada pesan atau bantuan baru</span>
        </div>
        <button type="button" wire:click="updateSetting('vibrate')"
            style="background-color: {{ $vibrate ? '#0066FF' : '#cbd5e1' }}; width: 44px; height: 24px;"
            class="relative inline-flex flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer focus:outline-none shadow-inner">
            <span style="transform: translateX({{ $vibrate ? '22px' : '2px' }});"
                class="inline-block h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200"></span>
        </button>
    </div>

    @if(!in_array(auth()->user()?->role, ['super_admin', 'superadmin', 'admin', 'admin_wilayah']))
    <!-- Update Transaksi & Saldo -->
    <div class="flex items-center justify-between py-3">
        <div class="pr-4">
            <span class="font-semibold text-sm text-gray-900 block">Update Transaksi &amp; Saldo</span>
            <span class="text-xs text-gray-400">Notifikasi top up, penarikan saldo, &amp; pencairan</span>
        </div>
        <button type="button" wire:click="updateSetting('transactionUpdate')"
            style="background-color: {{ $transactionUpdate ? '#0066FF' : '#cbd5e1' }}; width: 44px; height: 24px;"
            class="relative inline-flex flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer focus:outline-none shadow-inner">
            <span style="transform: translateX({{ $transactionUpdate ? '22px' : '2px' }});"
                class="inline-block h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200"></span>
        </button>
    </div>
    @endif
</div>
