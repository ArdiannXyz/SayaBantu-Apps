<div>
    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-xs flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if ($is_rejected && $rejection_reason)
        <div class="mb-5 p-4 rounded-xl border border-red-200 bg-red-50 text-red-800 text-sm shadow-sm">
            <div class="flex items-center gap-2 font-bold text-red-900 mb-1">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Verifikasi Identitas Sebelumnya Ditolak</span>
            </div>
            <div class="pl-7">
                <p class="font-semibold text-red-900 text-xs">Alasan Penolakan Admin:</p>
                <div class="bg-white/90 border border-red-200 rounded-lg p-2.5 my-1.5 text-xs text-red-800 font-medium leading-relaxed">
                    {{ $rejection_reason }}
                </div>
                <p class="text-[11px] text-red-700">
                    Silakan unggah ulang foto KTP atau Selfie yang sesuai dengan catatan di atas, lalu klik tombol <strong>Simpan Data</strong> untuk mengajukan verifikasi kembali.
                </p>
            </div>
        </div>
    @endif

    @php
        $verifUser = auth()->user();
        $isVerifCustomer = $verifUser && $verifUser->isCustomer();
        $regularFee = (float) \App\Models\AppSetting::get('customer_service_fee_percent', 10);
        $premiumFee = (float) \App\Models\AppSetting::get('customer_verified_service_fee_percent', 0);
    @endphp

    @if($isVerifCustomer)
        @if($isVerified)
            <div class="mb-5 p-4 rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 text-emerald-900 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-sm shrink-0">✓</span>
                    <div>
                        <p class="font-bold text-sm text-emerald-950">Akun Premium Aktif</p>
                        <p class="text-xs text-emerald-800">
                            KTP Anda telah terverifikasi. Biaya layanan aplikasi Anda saat ini <strong>{{ $premiumFee == 0 ? 'Rp 0 (Gratis)' : $premiumFee.'%' }}</strong>.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-5 p-4 rounded-2xl border border-indigo-200 bg-gradient-to-r from-indigo-50 via-blue-50 to-indigo-50 text-indigo-950 shadow-2xs">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0066FF] to-indigo-600 text-white flex items-center justify-center text-base shrink-0 shadow-xs">
                        ✨
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-extrabold text-sm text-indigo-950">Upgrade Akunmu Menjadi Premium</p>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">Promo KTP</span>
                        </div>
                        <p class="text-xs text-indigo-800 mt-1 leading-relaxed">
                            Lengkapi NIK (16 digit), foto KTP, dan selfie KTP sekarang untuk menikmati promo pengurangan biaya layanan aplikasi dari <strong>{{ $regularFee }}%</strong> menjadi <strong>{{ $premiumFee == 0 ? 'Rp 0 (Gratis)' : $premiumFee.'%' }}</strong>!
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- NIK Input -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="verification-nik" class="block text-sm font-bold text-gray-900">
                    NIK (Nomor Induk Kependudukan) <span class="text-red-500">*</span>
                </label>
                @if($isVerified)
                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">Terverifikasi</span>
                @endif
            </div>
            <input
                id="verification-nik"
                type="text"
                inputmode="numeric"
                maxlength="16"
                wire:model="nik"
                @if($isVerified) disabled readonly @endif
                oninput="this.value = this.value.replace(/\D/g, '').slice(0, 16)"
                placeholder="Masukkan 16 digit NIK sesuai KTP"
                class="w-full px-4 py-3 text-sm rounded-xl border border-gray-300 font-mono tracking-wider focus:border-[#0066FF] focus:ring-2 focus:ring-blue-100 transition {{ $isVerified ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-900' }}"
            />
            <p class="text-[11px] text-gray-500 mt-1">Pastikan 16 digit NIK sama persis dengan yang tertera pada foto KTP Anda.</p>
            @error('nik') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- KTP Upload -->
        <div>
            <div class="flex items-center justify-between gap-2 mb-2">
                <label class="block text-sm font-bold text-gray-900">Foto KTP <span class="text-red-500">*</span></label>
                <span class="text-[11px] font-medium text-gray-500">Pastikan teks KTP jelas</span>
            </div>
            <div class="mt-1 flex flex-col justify-center px-5 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-[#0066FF] hover:bg-blue-50/40 transition bg-gray-50 relative overflow-hidden group">
                <div class="space-y-3 text-center relative z-10">
                    @if ($ktp_photo)
                        <img src="{{ $ktp_photo->temporaryUrl() }}" alt="KTP Preview" class="mx-auto h-36 object-contain rounded-lg shadow-xs mb-2">
                    @elseif ($existing_ktp)
                        <img src="{{ Storage::url($existing_ktp) }}" alt="KTP Saat Ini" class="mx-auto h-36 object-contain rounded-lg shadow-xs mb-2">
                        <div class="inline-block bg-green-500 text-white text-[11px] px-2.5 py-0.5 rounded-full font-bold">
                            ✓ Terverifikasi / Tersimpan
                        </div>
                    @else
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-[#0066FF] transition" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    @endif

                    <div class="flex flex-wrap items-center justify-center gap-2.5">
                        <button type="button" onclick="openDirectCamera('ktp-upload', 'ktp')" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap bg-[#0066FF] hover:bg-[#0052CC] text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Kamera KTP</span>
                        </button>
                        <label for="ktp-upload-gallery" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 px-4 py-2.5 rounded-xl text-xs font-semibold transition cursor-pointer">
                            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Pilih Galeri</span>
                        </label>
                    </div>

                    <input id="ktp-upload" wire:model="ktp_photo" type="file" class="sr-only" accept="image/*" capture="environment">
                    <input id="ktp-upload-gallery" type="file" class="hidden" accept="image/*" onchange="transferGalleryFile(this, 'ktp-upload')">
                    <p class="text-[11px] text-gray-500">Format gambar JPG, JPEG, PNG (Maks 15MB)</p>
                </div>
                
                <div wire:loading wire:target="ktp_photo" class="absolute inset-0 bg-white/80 flex items-center justify-center z-20">
                    <svg class="animate-spin h-8 w-8 text-[#0066FF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            @error('ktp_photo') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Selfie Upload -->
        <div>
            <div class="flex items-center justify-between gap-2 mb-2">
                <label class="block text-sm font-bold text-gray-900">Foto Selfie (Wajah &amp; KTP) <span class="text-red-500">*</span></label>
                <span class="text-[11px] font-medium text-gray-500">Wajah &amp; KTP terlihat</span>
            </div>
            <div class="mt-1 flex flex-col justify-center px-5 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-[#0066FF] hover:bg-blue-50/40 transition bg-gray-50 relative overflow-hidden group">
                <div class="space-y-3 text-center relative z-10">
                    @if ($selfie_photo)
                        <img src="{{ $selfie_photo->temporaryUrl() }}" alt="Selfie Preview" class="mx-auto h-36 object-contain rounded-lg shadow-xs mb-2">
                    @elseif ($existing_selfie)
                        <img src="{{ Storage::url($existing_selfie) }}" alt="Selfie Saat Ini" class="mx-auto h-36 object-contain rounded-lg shadow-xs mb-2">
                        <div class="inline-block bg-green-500 text-white text-[11px] px-2.5 py-0.5 rounded-full font-bold">
                            ✓ Terverifikasi / Tersimpan
                        </div>
                    @else
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-[#0066FF] transition" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    @endif

                    <div class="flex flex-wrap items-center justify-center gap-2.5">
                        <button type="button" onclick="openDirectCamera('selfie-upload', 'selfie')" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap bg-[#0066FF] hover:bg-[#0052CC] text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Kamera Selfie</span>
                        </button>
                        <label for="selfie-upload-gallery" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 px-4 py-2.5 rounded-xl text-xs font-semibold transition cursor-pointer">
                            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Pilih Galeri</span>
                        </label>
                    </div>

                    <input id="selfie-upload" wire:model="selfie_photo" type="file" class="sr-only" accept="image/*" capture="user">
                    <input id="selfie-upload-gallery" type="file" class="hidden" accept="image/*" onchange="transferGalleryFile(this, 'selfie-upload')">
                    <p class="text-[11px] text-gray-500">Format gambar JPG, JPEG, PNG (Maks 15MB)</p>
                </div>
                
                <div wire:loading wire:target="selfie_photo" class="absolute inset-0 bg-white/80 flex items-center justify-center z-20">
                    <svg class="animate-spin h-8 w-8 text-[#0066FF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            @error('selfie_photo') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" onclick="sessionStorage.removeItem('dismissed_ktp_rejected_modal');" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gradient-to-r from-[#0066FF] to-[#003EA8] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0066FF] transition" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">Simpan Data</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </form>
</div>

