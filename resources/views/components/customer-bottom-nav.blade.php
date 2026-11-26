@php
    $unreadCustomerChats = 0;
    if (auth()->check()) {
        try {
            $unreadCustomerChats = \App\Models\Chat::where('customer_id', auth()->id())
                ->whereNull('read_at')
                ->where('sender_type', 'mitra')
                ->count();
        } catch (\Throwable $e) {}
    }

    $u = auth()->user();
    $needsProfileAttention = $u && (
        !empty($u->getMissingBiodataFields()) ||
        !$u->hasVerifiedEmail()
    );

    $isHomeActive = request()->routeIs('customer.dashboard') || request()->routeIs('public.helps');
    $isCreateActive = request()->routeIs('customer.helps.create');
    $isOrdersActive = request()->routeIs('customer.helps.*') && !request()->routeIs('customer.helps.create');
    $isChatActive = request()->routeIs('customer.chat*') || request()->routeIs('chat.*');
    $isProfileActive = request()->routeIs('profile.*') || request()->routeIs('profile') || request()->routeIs('customer.addresses*');
@endphp

<!-- Bottom Navigation Bar: Fluid full width on mobile, max-w-md centered on desktop -->
<nav id="bottom-nav" class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white border-t border-gray-200/90 shadow-2xl z-40 sm:rounded-b-[36px]"
    style="padding-bottom: calc(0.65rem + env(safe-area-inset-bottom, 0px));">
    <div class="w-full flex items-center justify-around px-2 pt-2.5 pb-1">
        <!-- 1. Beranda -->
        <a href="{{ auth()->check() ? (auth()->user()->isMitra() ? route('mitra.dashboard') : route('customer.dashboard')) : route('public.helps') }}" wire:navigate
            class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors {{ $isHomeActive ? 'text-[#0066FF] font-bold active' : 'text-gray-400 hover:text-gray-700' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $isHomeActive ? '2.5' : '1.8' }}"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="nav-label text-[11px] tracking-tight truncate max-w-full text-center mt-1">Beranda</span>
        </a>

        <!-- 2. Buat Order -->
        <a href="{{ route('customer.helps.create') }}" wire:navigate
            class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors {{ $isCreateActive ? 'text-[#0066FF] font-bold active' : 'text-gray-400 hover:text-gray-700' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $isCreateActive ? '2.5' : '1.8' }}"
                    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="nav-label text-[11px] tracking-tight truncate max-w-full text-center mt-1">Buat Order</span>
        </a>

        <!-- 3. Pesanan -->
        <a href="{{ route('customer.helps.index') }}" wire:navigate
            class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors {{ $isOrdersActive ? 'text-[#0066FF] font-bold active' : 'text-gray-400 hover:text-gray-700' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $isOrdersActive ? '2.5' : '1.8' }}"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span class="nav-label text-[11px] tracking-tight truncate max-w-full text-center mt-1">Pesanan</span>
        </a>

        <!-- 4. Chat -->
        <a href="{{ route('customer.chat') }}" wire:navigate
            class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors {{ $isChatActive ? 'text-[#0066FF] font-bold active' : 'text-gray-400 hover:text-gray-700' }}">
            <div class="relative inline-flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $isChatActive ? '2.5' : '1.8' }}"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                @if($unreadCustomerChats > 0)
                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5 items-center justify-center pointer-events-none">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 ring-1.5 ring-white"></span>
                    </span>
                @endif
            </div>
            <span class="nav-label text-[11px] tracking-tight truncate max-w-full text-center mt-1">Chat</span>
        </a>

        <!-- 5. Profil -->
        <a href="{{ route('profile') }}" wire:navigate
            class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-0.5 min-w-0 transition-colors {{ $isProfileActive ? 'text-[#0066FF] font-bold active' : 'text-gray-400 hover:text-gray-700' }}">
            <div class="relative inline-flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $isProfileActive ? '2.5' : '1.8' }}"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                @if($needsProfileAttention)
                    <span class="absolute bottom-0.5 -right-0.5 flex h-2 w-2 items-center justify-center pointer-events-none">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 ring-1.5 ring-white"></span>
                    </span>
                @endif
            </div>
            <span class="nav-label text-[11px] tracking-tight truncate max-w-full text-center mt-1">Profil</span>
        </a>
    </div>
</nav>
