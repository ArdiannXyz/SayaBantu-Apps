@if (session()->has('message') || session()->has('success') || session()->has('error') || session()->has('status'))
    @php
        $flashIsError = session()->has('error');
        $flashMessage = session('error') ?? session('message') ?? session('success') ?? session('status');
        $flashIconSetting = \App\Models\AppSetting::get('app_icon');
        $flashAppIconUrl = $flashIconSetting && \Illuminate\Support\Facades\Storage::disk('public')->exists($flashIconSetting)
            ? asset('storage/' . $flashIconSetting)
            : asset('icons/icon-192x192.png');
    @endphp
    <div id="flash-toast"
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => { show = false; }, 3500)"
        class="sb-push-dock transition-all duration-300">
        <div class="sb-push-card pointer-events-auto bg-white/95 backdrop-blur-md border {{ $flashIsError ? 'border-rose-200' : 'border-emerald-200' }} text-gray-900 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-3.5 pt-2 pb-1 flex items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/70">
                <div class="flex items-center gap-1.5 min-w-0">
                    <img src="{{ $flashAppIconUrl }}" alt="SayaBantu" class="w-4 h-4 rounded-md object-cover shrink-0">
                    <span class="text-[11px] font-bold text-slate-700 tracking-tight">SayaBantu</span>
                    <span class="text-[10px] text-slate-400">• Baru saja</span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold border {{ $flashIsError ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                        {{ $flashIsError ? 'Perhatian' : 'Berhasil' }}
                    </span>
                    <button type="button" @click="show = false; document.getElementById('flash-toast')?.remove();" class="text-gray-400 hover:text-gray-600 p-0.5 text-xs cursor-pointer" title="Tutup">✕</button>
                </div>
            </div>
            <div class="p-3.5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl {{ $flashIsError ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }} flex items-center justify-center flex-shrink-0 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if ($flashIsError)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        @endif
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold {{ $flashIsError ? 'text-rose-900' : 'text-emerald-900' }}">
                        {{ $flashIsError ? 'Perhatian' : 'Berhasil' }}
                    </div>
                    <div class="text-[11px] text-gray-600 leading-snug">{{ $flashMessage }}</div>
                </div>
            </div>
        </div>
    </div>
    <script>
        setTimeout(function() {
            var toast = document.getElementById('flash-toast');
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.4s ease';
                setTimeout(function() { toast.remove(); }, 400);
            }
        }, 3500);
    </script>
@endif
