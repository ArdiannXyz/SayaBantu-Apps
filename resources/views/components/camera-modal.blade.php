{{-- Reusable Camera Capture Modal for KTP & Selfie --}}
<style>
    @keyframes ktpScanMove {
        0%, 100% { top: 6%; opacity: 0; }
        15% { opacity: 0.85; }
        50% { top: 92%; opacity: 0.85; }
        85% { opacity: 0.85; }
    }
    .ktp-scanner-laser {
        position: absolute;
        left: 3%;
        right: 3%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #34d399, #10b981, #34d399, transparent);
        box-shadow: 0 0 10px #34d399, 0 0 4px #10b981;
        animation: ktpScanMove 2.6s ease-in-out infinite;
    }

    /* Adaptasi Responsif Otomatis Ketika HP Dimiringkan Horizontal (Landscape) */
    @media (orientation: landscape) and (max-height: 560px) {
        #cameraCaptureModal {
            flex-direction: row !important;
            padding: 0.5rem !important;
            gap: 0.5rem !important;
        }
        
        #cameraHeaderBar {
            position: absolute !important;
            top: 0.5rem !important;
            left: 0.75rem !important;
            width: auto !important;
            max-width: 65% !important;
            background: rgba(0, 0, 0, 0.75) !important;
            backdrop-filter: blur(8px) !important;
            border-radius: 9999px !important;
            padding: 0.25rem 0.75rem !important;
            z-index: 30 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
        }

        #cameraModalSubtitle {
            display: none !important;
        }

        #cameraViewport {
            height: 100% !important;
            flex: 1 1 0% !important;
            max-width: none !important;
            margin: 0 !important;
            border-radius: 1rem !important;
        }

        #cameraControlsBar {
            width: 5.5rem !important;
            max-width: none !important;
            padding: 0 !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 30 !important;
        }

        #cameraControlsLive {
            flex-direction: column !important;
            gap: 1.25rem !important;
            padding: 0 !important;
            width: 100% !important;
            align-items: center !important;
        }

        #cameraControlsReview {
            flex-direction: column !important;
            gap: 0.5rem !important;
            padding: 0 !important;
            width: 100% !important;
        }

        #ktpCardFrame {
            max-height: 72vh !important;
            width: auto !important;
            max-width: 80vw !important;
        }

        #ktpBottomInstruction, #selfieBottomInstruction {
            display: none !important;
        }

        #selfieOvalFrame {
            max-height: 75vh !important;
            width: auto !important;
        }
    }
</style>

<div id="cameraCaptureModal" class="fixed inset-0 z-50 bg-black/95 backdrop-blur-md flex flex-col items-center justify-between p-3 sm:p-5 hidden select-none" style="z-index: 99999;">
    <!-- Top Header Bar -->
    <div id="cameraHeaderBar" class="w-full max-w-lg flex items-center justify-between text-white py-2 px-1 z-20">
        <div class="flex items-center gap-2.5">
            <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse shadow-sm shadow-red-500"></span>
            <div>
                <h3 id="cameraModalTitle" class="text-sm font-bold tracking-wide text-white leading-tight">Ambil Foto</h3>
                <p id="cameraModalSubtitle" class="text-[11px] text-gray-300">Posisikan dokumen atau wajah di dalam bingkai</p>
            </div>
        </div>

        <!-- Tombol Putar Posisi Bingkai (Khusus KTP: Horizontal <-> Vertikal) -->
        <button type="button" id="btnRotateKtpFrame" onclick="toggleKtpFrameOrientation()" class="inline-flex items-center gap-1.5 bg-white/15 hover:bg-white/25 active:scale-95 text-white text-xs font-semibold px-3 py-1.5 rounded-full transition cursor-pointer backdrop-blur-xs shadow-xs" title="Putar Posisi Bingkai KTP">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span id="txtRotateKtpFrame">Putar Posisi</span>
        </button>
    </div>

    <!-- Main Viewport Area -->
    <div id="cameraViewport" class="relative w-full max-w-lg flex-1 my-2 flex items-center justify-center overflow-hidden rounded-2xl bg-black border border-white/10 shadow-2xl">
        <!-- Live Video Stream -->
        <video id="cameraCaptureVideo" autoplay playsinline muted class="w-full h-full object-cover transition-transform duration-300"></video>
        
        <!-- Hidden Canvas for capture rendering -->
        <canvas id="cameraCaptureCanvas" class="hidden"></canvas>

        <!-- KTP Guideline (Rectangle Card) -->
        <div id="ktpCaptureGuideline" class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center p-3 sm:p-5">
            <!-- Card Frame with exact standard KTP proportion (1.586 : 1) -->
            <div id="ktpCardFrame" class="w-[92%] sm:w-[86%] max-w-[420px] aspect-[1.586/1] relative rounded-2xl border border-emerald-400/40 shadow-[0_0_0_9999px_rgba(0,0,0,0.68),0_0_25px_rgba(16,185,129,0.2)] flex flex-col justify-between p-3.5 sm:p-4 transition-all duration-300">
                
                <!-- Laser Scanning Animation Line -->
                <div class="ktp-scanner-laser pointer-events-none"></div>

                <!-- 4 Crisp Corner Targeting Brackets (perfectly hugging rounded-2xl) -->
                <div class="absolute -top-[2px] -left-[2px] w-8 sm:w-10 h-8 sm:h-10 border-t-[3.5px] border-l-[3.5px] border-emerald-400 rounded-tl-2xl pointer-events-none"></div>
                <div class="absolute -top-[2px] -right-[2px] w-8 sm:w-10 h-8 sm:h-10 border-t-[3.5px] border-r-[3.5px] border-emerald-400 rounded-tr-2xl pointer-events-none"></div>
                <div class="absolute -bottom-[2px] -left-[2px] w-8 sm:w-10 h-8 sm:h-10 border-b-[3.5px] border-l-[3.5px] border-emerald-400 rounded-bl-2xl pointer-events-none"></div>
                <div class="absolute -bottom-[2px] -right-[2px] w-8 sm:w-10 h-8 sm:h-10 border-b-[3.5px] border-r-[3.5px] border-emerald-400 rounded-br-2xl pointer-events-none"></div>

                <!-- Sleek Top Floating Badge (Unobtrusive) -->
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-emerald-500 text-slate-950 font-extrabold text-[10px] tracking-wider uppercase px-3 py-0.5 rounded-full shadow-lg flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-950 animate-ping"></span>
                    <span id="txtKtpBadge">Bingkai Kartu KTP</span>
                </div>

                <!-- Subtle Inner Watermark Guides (Keeps center 100% visible & clear) -->
                <div id="ktpInnerGuides" class="flex items-start justify-between w-full h-full pointer-events-none">
                    <!-- Left: Faint NIK & Text Guidelines -->
                    <div class="space-y-2 opacity-30 pt-1 max-w-[55%]">
                        <div class="h-2 w-16 sm:w-20 bg-emerald-300 rounded-full"></div>
                        <div class="h-1.5 w-24 sm:w-32 bg-emerald-300 rounded-full"></div>
                        <div class="h-1.5 w-20 sm:w-28 bg-emerald-300 rounded-full"></div>
                        <div class="h-1.5 w-16 sm:w-24 bg-emerald-300 rounded-full"></div>
                    </div>

                    <!-- Right: Faint Photo Box Guideline -->
                    <div class="w-[26%] h-[82%] border border-dashed border-emerald-400/40 rounded-xl bg-emerald-400/5 flex flex-col items-center justify-center p-1 self-center">
                        <svg class="w-6 h-6 text-emerald-300/50 mb-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-[8px] font-bold tracking-wider text-emerald-300/60 uppercase">Foto</span>
                    </div>
                </div>

                <!-- Bottom Watermark: Chip & e-KTP -->
                <div class="flex items-center gap-1.5 text-emerald-300/45 text-[10px] font-bold tracking-widest uppercase pointer-events-none pt-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="2"/>
                        <path d="M7 15h4M7 9h2" stroke-width="2"/>
                    </svg>
                    <span>e-KTP</span>
                </div>
            </div>

            <!-- Bottom Instruction Box -->
            <div id="ktpBottomInstruction" class="mt-4 bg-black/80 backdrop-blur-md border border-white/10 px-4 py-2 rounded-xl text-white text-xs font-medium text-center shadow-lg flex items-center gap-2 max-w-sm">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span>Posisikan fisik KTP pas di dalam bingkai & pastikan NIK terbaca jelas</span>
            </div>
        </div>

        <!-- Selfie Guideline (Oval Face Frame) -->
        <div id="selfieCaptureGuideline" class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center p-3 sm:p-5 hidden">
            <!-- Face Oval Frame -->
            <div id="selfieOvalFrame" class="w-[72%] sm:w-[65%] max-w-[260px] aspect-[3/4] relative rounded-[50%] border-2 border-blue-400/80 shadow-[0_0_0_9999px_rgba(0,0,0,0.68),0_0_25px_rgba(59,130,246,0.25)] flex flex-col items-center justify-between p-4 transition-all duration-300">
                
                <!-- Floating Top Badge -->
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-blue-500 text-white font-extrabold text-[10px] tracking-wider uppercase px-3 py-0.5 rounded-full shadow-lg flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                    <span>Posisi Wajah</span>
                </div>

                <!-- Floating KTP position hint -->
                <div class="absolute -bottom-2 right-0 sm:-right-2 bg-blue-600/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg backdrop-blur-xs shadow-md border border-white/20 flex items-center gap-1 whitespace-nowrap">
                    <span>🪪</span>
                    <span>Pegang KTP di samping</span>
                </div>
            </div>

            <!-- Bottom Instruction Box -->
            <div id="selfieBottomInstruction" class="mt-5 bg-black/80 backdrop-blur-md border border-white/10 px-4 py-2 rounded-xl text-white text-xs font-medium text-center shadow-lg flex items-center gap-2 max-w-sm">
                <svg class="w-4 h-4 text-blue-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span>Pegang KTP di samping wajah, pastikan wajah & KTP tidak buram</span>
            </div>
        </div>

        <!-- Captured Image Review Screen -->
        <div id="cameraCaptureReview" class="absolute inset-0 bg-black flex items-center justify-center hidden z-10">
            <img id="cameraCaptureReviewImg" src="" alt="Hasil Foto" class="w-full h-full object-contain">
            <div class="absolute top-3 left-3 bg-emerald-600/90 text-white text-[11px] font-bold px-3 py-1 rounded-full backdrop-blur-xs shadow-sm flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-white"></span>
                Foto Berhasil Ditangkap
            </div>
        </div>
    </div>

    <!-- Bottom Controls Bar -->
    <div id="cameraControlsBar" class="w-full max-w-lg py-3 px-2 z-20">
        <!-- Live Capture Controls -->
        <div id="cameraControlsLive" class="flex items-center justify-between gap-4 w-full px-4">
            <!-- Switch Camera Button -->
            <button type="button" onclick="switchCameraCaptureDevice()" id="btnSwitchCamera" class="w-12 h-12 rounded-full bg-white/15 hover:bg-white/25 active:scale-95 text-white flex items-center justify-center transition shadow-md cursor-pointer" title="Ganti Kamera (Depan / Belakang)">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>

            <!-- Big Shutter Button -->
            <button type="button" onclick="takeCameraSnapshot()" class="w-20 h-20 rounded-full bg-white p-1.5 shadow-2xl hover:scale-105 active:scale-90 transition cursor-pointer flex items-center justify-center" title="Ambil Foto">
                <div class="w-full h-full rounded-full border-4 border-emerald-600 bg-white hover:bg-gray-100 flex items-center justify-center transition">
                    <div class="w-4 h-4 rounded-full bg-emerald-600"></div>
                </div>
            </button>

            <!-- Close / Cancel Button -->
            <button type="button" onclick="closeCameraCaptureModal()" class="w-12 h-12 rounded-full bg-white/15 hover:bg-white/25 active:scale-90 text-white flex items-center justify-center transition shadow-md cursor-pointer text-xs font-semibold" title="Batal">
                Batal
            </button>
        </div>

        <!-- Review Controls (Ambil Ulang / Gunakan Foto) -->
        <div id="cameraControlsReview" class="flex items-center justify-between gap-3 w-full hidden">
            <button type="button" onclick="retakeCameraSnapshot()" class="flex-1 py-3 px-4 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Foto Ulang</span>
            </button>
            <button type="button" onclick="confirmCameraCapturedPhoto()" class="flex-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg transition active:scale-95 cursor-pointer flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Gunakan Foto Ini</span>
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        let activeStream = null;
        let currentTargetInputId = null;
        let currentCaptureType = 'ktp'; // 'ktp' or 'selfie'
        let currentFacingMode = 'environment'; // 'environment' (rear) or 'user' (front)
        let capturedBlob = null;
        let isKtpVertical = false;

        window.toggleKtpFrameOrientation = function() {
            isKtpVertical = !isKtpVertical;
            const frame = document.getElementById('ktpCardFrame');
            const btnText = document.getElementById('txtRotateKtpFrame');
            const badgeText = document.getElementById('txtKtpBadge');
            const innerGuides = document.getElementById('ktpInnerGuides');

            if (!frame) return;

            if (isKtpVertical) {
                // Ubah bingkai menjadi orientasi vertikal
                frame.classList.remove('aspect-[1.586/1]', 'w-[92%]', 'sm:w-[86%]', 'max-w-[420px]');
                frame.classList.add('aspect-[1/1.586]', 'w-[68%]', 'sm:w-[58%]', 'max-w-[280px]', 'max-h-[62vh]');
                if (btnText) btnText.textContent = 'Posisi: Vertikal';
                if (badgeText) badgeText.textContent = 'KTP Vertikal';
                if (innerGuides) innerGuides.classList.add('flex-col', 'items-center', 'justify-between');
            } else {
                // Kembalikan ke orientasi horizontal standar
                frame.classList.remove('aspect-[1/1.586]', 'w-[68%]', 'sm:w-[58%]', 'max-w-[280px]', 'max-h-[62vh]');
                frame.classList.add('aspect-[1.586/1]', 'w-[92%]', 'sm:w-[86%]', 'max-w-[420px]');
                if (btnText) btnText.textContent = 'Putar Posisi';
                if (badgeText) badgeText.textContent = 'Bingkai Kartu KTP';
                if (innerGuides) innerGuides.classList.remove('flex-col', 'items-center', 'justify-between');
            }
        };

        window.openDirectCamera = async function(targetInputId, type = 'ktp') {
            currentTargetInputId = targetInputId;
            currentCaptureType = type;
            currentFacingMode = (type === 'selfie') ? 'user' : 'environment';
            capturedBlob = null;

            const modal = document.getElementById('cameraCaptureModal');
            const title = document.getElementById('cameraModalTitle');
            const subtitle = document.getElementById('cameraModalSubtitle');
            const ktpGuide = document.getElementById('ktpCaptureGuideline');
            const selfieGuide = document.getElementById('selfieCaptureGuideline');
            const reviewBox = document.getElementById('cameraCaptureReview');
            const liveControls = document.getElementById('cameraControlsLive');
            const reviewControls = document.getElementById('cameraControlsReview');
            const rotateBtn = document.getElementById('btnRotateKtpFrame');

            if (type === 'selfie') {
                title.textContent = 'Ambil Foto Selfie Memegang KTP';
                subtitle.textContent = 'Posisikan wajah & KTP Anda di dalam panduan oval';
                ktpGuide.classList.add('hidden');
                selfieGuide.classList.remove('hidden');
                if (rotateBtn) rotateBtn.classList.add('hidden');
            } else if (type === 'before' || type === 'after' || type === 'work') {
                title.textContent = (type === 'before') ? 'Ambil Foto Kondisi Awal' : 'Ambil Foto Hasil Pekerjaan';
                subtitle.textContent = (type === 'before')
                    ? 'Arahkan kamera ke kondisi awal area kerja'
                    : 'Arahkan kamera ke hasil pekerjaan yang telah selesai';
                ktpGuide.classList.add('hidden');
                selfieGuide.classList.add('hidden');
                if (rotateBtn) rotateBtn.classList.add('hidden');
            } else {
                title.textContent = 'Ambil Foto KTP';
                subtitle.textContent = 'Posisikan fisik KTP Anda di dalam kotak panduan';
                ktpGuide.classList.remove('hidden');
                selfieGuide.classList.add('hidden');
                if (rotateBtn) rotateBtn.classList.remove('hidden');

                // Reset frame ke orientasi horizontal default
                isKtpVertical = false;
                const frame = document.getElementById('ktpCardFrame');
                const btnText = document.getElementById('txtRotateKtpFrame');
                const badgeText = document.getElementById('txtKtpBadge');
                const innerGuides = document.getElementById('ktpInnerGuides');
                if (frame) {
                    frame.classList.remove('aspect-[1/1.586]', 'w-[68%]', 'sm:w-[58%]', 'max-w-[280px]', 'max-h-[62vh]');
                    frame.classList.add('aspect-[1.586/1]', 'w-[92%]', 'sm:w-[86%]', 'max-w-[420px]');
                }
                if (btnText) btnText.textContent = 'Putar Posisi';
                if (badgeText) badgeText.textContent = 'Bingkai Kartu KTP';
                if (innerGuides) innerGuides.classList.remove('flex-col', 'items-center', 'justify-between');
            }

            reviewBox.classList.add('hidden');
            liveControls.classList.remove('hidden');
            reviewControls.classList.add('hidden');

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            await initCameraStream();
        };

        async function initCameraStream() {
            stopCurrentStream();

            const video = document.getElementById('cameraCaptureVideo');
            const constraints = {
                audio: false,
                video: {
                    facingMode: { ideal: currentFacingMode },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            };

            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Fitur kamera browser tidak didukung pada perangkat ini.');
                }

                activeStream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = activeStream;
                video.style.transform = (currentFacingMode === 'user') ? 'scaleX(-1)' : 'scaleX(1)';
                await video.play();
            } catch (err) {
                console.warn('Gagal membuka WebRTC camera stream:', err);
                
                // Jika akses kamera gagal atau ditolak, gunakan fallback input native kamera
                closeCameraCaptureModal();
                if (currentTargetInputId) {
                    const fallbackInput = document.getElementById(currentTargetInputId);
                    if (fallbackInput) {
                        fallbackInput.click();
                    }
                }
            }
        }

        window.switchCameraCaptureDevice = async function() {
            currentFacingMode = (currentFacingMode === 'user') ? 'environment' : 'user';
            await initCameraStream();
        };

        window.takeCameraSnapshot = function() {
            const video = document.getElementById('cameraCaptureVideo');
            const canvas = document.getElementById('cameraCaptureCanvas');
            const reviewImg = document.getElementById('cameraCaptureReviewImg');
            const reviewBox = document.getElementById('cameraCaptureReview');
            const liveControls = document.getElementById('cameraControlsLive');
            const reviewControls = document.getElementById('cameraControlsReview');

            if (!video || !video.videoWidth) {
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');

            // Jika selfie dengan kamera depan, balik gambar secara alami (mirror)
            if (currentFacingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function(blob) {
                if (!blob) return;
                capturedBlob = blob;
                const previewUrl = URL.createObjectURL(blob);
                reviewImg.src = previewUrl;

                reviewBox.classList.remove('hidden');
                liveControls.classList.add('hidden');
                reviewControls.classList.remove('hidden');

                const rotateBtn = document.getElementById('btnRotateKtpFrame');
                if (rotateBtn) rotateBtn.classList.add('hidden');
            }, 'image/jpeg', 0.92);
        };

        window.retakeCameraSnapshot = function() {
            const reviewBox = document.getElementById('cameraCaptureReview');
            const liveControls = document.getElementById('cameraControlsLive');
            const reviewControls = document.getElementById('cameraControlsReview');
            const reviewImg = document.getElementById('cameraCaptureReviewImg');
            const rotateBtn = document.getElementById('btnRotateKtpFrame');

            if (reviewImg.src) {
                URL.revokeObjectURL(reviewImg.src);
                reviewImg.src = '';
            }
            capturedBlob = null;

            reviewBox.classList.add('hidden');
            liveControls.classList.remove('hidden');
            reviewControls.classList.add('hidden');
            if (currentCaptureType === 'ktp' && rotateBtn) {
                rotateBtn.classList.remove('hidden');
            }
        };

        window.confirmCameraCapturedPhoto = function() {
            if (!capturedBlob || !currentTargetInputId) {
                closeCameraCaptureModal();
                return;
            }

            const prefix = currentCaptureType === 'ktp'
                ? 'ktp_camera_'
                : (currentCaptureType === 'selfie' ? 'selfie_camera_' : 'work_camera_');
            const fileName = prefix + Date.now() + '.jpg';
            const file = new File([capturedBlob], fileName, { type: 'image/jpeg', lastModified: Date.now() });

            const targetInput = document.getElementById(currentTargetInputId);
            if (targetInput) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                targetInput.files = dataTransfer.files;

                // Dispatch event input dan change agar Livewire dan JS Form mendeteksi file baru
                targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                targetInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            closeCameraCaptureModal();
        };

        function stopCurrentStream() {
            if (activeStream) {
                activeStream.getTracks().forEach(track => {
                    track.stop();
                });
                activeStream = null;
            }
            const video = document.getElementById('cameraCaptureVideo');
            if (video) {
                video.srcObject = null;
                video.style.transform = '';
            }
        }

        window.closeCameraCaptureModal = function() {
            stopCurrentStream();
            const modal = document.getElementById('cameraCaptureModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            document.body.style.overflow = '';
            capturedBlob = null;
        };

        // Utility: Transfer file dari input galeri ke input kamera target
        window.transferGalleryFile = function(galleryInput, targetInputId) {
            if (galleryInput.files && galleryInput.files[0]) {
                const target = document.getElementById(targetInputId);
                if (target) {
                    const dt = new DataTransfer();
                    dt.items.add(galleryInput.files[0]);
                    target.files = dt.files;
                    target.dispatchEvent(new Event('input', { bubbles: true }));
                    target.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        };
    })();
</script>
