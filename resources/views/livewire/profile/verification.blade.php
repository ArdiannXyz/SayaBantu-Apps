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

    <form wire:submit="save" class="space-y-6">
        <!-- KTP Upload -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-bold text-gray-900">Foto KTP</label>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openDirectCamera('ktp-upload', 'ktp')" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0066FF] hover:text-[#0052CC] bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Buka Kamera</span>
                    </button>
                    <label for="ktp-upload-gallery" class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Galeri</span>
                    </label>
                </div>
            </div>
            <div class="mt-1 flex flex-col justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-[#0066FF] hover:bg-blue-50/40 transition bg-gray-50 relative overflow-hidden group">
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

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-2 max-w-sm mx-auto">
                        <button type="button" onclick="openDirectCamera('ktp-upload', 'ktp')" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-[#0066FF] hover:bg-[#0052CC] text-white px-4 py-2 rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Ambil dari Kamera KTP</span>
                        </button>
                        <label for="ktp-upload-gallery" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 px-3.5 py-2 rounded-xl text-xs font-medium transition cursor-pointer">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Pilih dari Galeri</span>
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
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-bold text-gray-900">Foto Selfie (Wajah & KTP)</label>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openDirectCamera('selfie-upload', 'selfie')" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0066FF] hover:text-[#0052CC] bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Buka Kamera</span>
                    </button>
                    <label for="selfie-upload-gallery" class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Galeri</span>
                    </label>
                </div>
            </div>
            <div class="mt-1 flex flex-col justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-[#0066FF] hover:bg-blue-50/40 transition bg-gray-50 relative overflow-hidden group">
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

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-2 max-w-sm mx-auto">
                        <button type="button" onclick="openDirectCamera('selfie-upload', 'selfie')" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-[#0066FF] hover:bg-[#0052CC] text-white px-4 py-2 rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Ambil dari Kamera Selfie</span>
                        </button>
                        <label for="selfie-upload-gallery" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 px-3.5 py-2 rounded-xl text-xs font-medium transition cursor-pointer">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Pilih dari Galeri</span>
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

