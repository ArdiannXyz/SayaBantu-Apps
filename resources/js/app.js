import './bootstrap';
import './push-notifications';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

// SayaBantu Modern Alert & Toast System
const toastIcons = {
    success: '<svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    error: '<svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    warning: '<svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>',
    info: '<svg class="w-5 h-5 text-[#0066FF] shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
};

const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
    customClass: {
        popup: 'sb-toast-popup',
        title: 'sb-toast-title',
        htmlContainer: 'sb-toast-html',
    }
});

window.sbToast = function(title, icon = 'success') {
    if (!title) return;

    let finalIcon = icon;
    const str = String(title).toLowerCase();
    if (icon === 'success') {
        if (/gagal|error|salah|rusak/i.test(str)) {
            finalIcon = 'error';
        } else if (/peringatan|perhatian|warning|kurang|harus|belum/i.test(str)) {
            finalIcon = 'warning';
        }
    }

    const adaptivePosition = window.innerWidth >= 768 ? 'bottom-end' : 'top';

    return Toast.fire({
        position: adaptivePosition,
        icon: finalIcon,
        iconHtml: toastIcons[finalIcon] || toastIcons.success,
        title: title
    });
};

// Override native Google Chrome window.alert
window.alert = function (message) {
    if (!message) return;
    const strMsg = String(message);

    // If it's a copy or success message, display sleek toast notification
    if (/disalin|tersalin|copied|copy|berhasil/i.test(strMsg) && strMsg.length < 100) {
        return window.sbToast(strMsg, 'success');
    }

    const isWarning = /peringatan|perhatian|warning|kurang|harus|belum/i.test(strMsg);
    const isError = /gagal|error|salah|batal|rusak|ditolak/i.test(strMsg);

    let icon = 'info';
    let iconColor = '#0066FF';
    let confirmBtnClass = 'bg-[#0066FF] hover:bg-[#0052cc] text-white';

    if (isError) {
        icon = 'error';
        iconColor = '#EF4444';
        confirmBtnClass = 'bg-red-600 hover:bg-red-700 text-white';
    } else if (isWarning) {
        icon = 'warning';
        iconColor = '#F59E0B';
        confirmBtnClass = 'bg-amber-600 hover:bg-amber-700 text-white';
    }

    return Swal.fire({
        text: strMsg,
        icon: icon,
        iconColor: iconColor,
        confirmButtonText: 'Oke, Mengerti',
        buttonsStyling: false,
        customClass: {
            popup: 'rounded-3xl shadow-2xl p-5 sm:p-6 border border-gray-100 font-sans !w-[calc(100vw-2rem)] sm:!w-full max-w-sm',
            confirmButton: `w-full py-3 px-5 rounded-xl font-bold text-xs ${confirmBtnClass} shadow-md transition active:scale-95 cursor-pointer mt-3`,
            htmlContainer: 'text-xs text-gray-600 mt-2 leading-relaxed font-medium break-words',
        }
    });
};

// Global helper for SweetAlert confirm dialogs
window.sbConfirm = function({
    title = 'Apakah Anda yakin?',
    text = 'Tindakan ini tidak dapat dibatalkan.',
    icon = 'warning',
    confirmButtonText = 'Ya, Lanjutkan',
    cancelButtonText = 'Batal',
    isDanger = false
} = {}) {
    if (Swal.isVisible()) {
        Swal.close();
    }
    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        buttonsStyling: false,
        customClass: {
            popup: 'rounded-3xl shadow-2xl p-5 sm:p-6 border border-gray-100 font-sans !w-[calc(100vw-2rem)] sm:!w-full max-w-sm',
            title: 'text-base font-extrabold text-gray-900',
            htmlContainer: 'text-xs text-gray-600 mt-1 leading-relaxed font-medium break-words',
            actions: 'flex flex-col-reverse sm:flex-row gap-2 sm:gap-2.5 w-full mt-4',
            confirmButton: `w-full sm:flex-1 py-3 px-4 rounded-xl font-bold text-xs ${isDanger ? 'bg-red-600 hover:bg-red-700' : 'bg-[#0066FF] hover:bg-[#0052cc]'} text-white shadow-md transition active:scale-95 cursor-pointer`,
            cancelButton: 'w-full sm:flex-1 py-3 px-4 rounded-xl font-semibold text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 transition active:scale-95 cursor-pointer'
        }
    });
};

// Global listener for non-form elements with data-confirm (e.g. <a> links, standalone action buttons)
document.addEventListener('click', function(e) {
    const el = e.target.closest('[data-confirm]');
    if (!el) return;

    // If the element is a FORM, or inside a FORM with data-confirm, or is a form submit button:
    // Do NOT intercept here. Let the 'submit' event handler below handle it cleanly so that
    // forms are reliably submitted without duplicate alerts or missing submissions.
    if (el.tagName === 'FORM' || el.closest('form[data-confirm]') || (el.form && (el.type === 'submit' || !el.type))) {
        return;
    }

    e.preventDefault();
    const msg = el.getAttribute('data-confirm') || 'Apakah Anda yakin?';
    const isDangerAttr = el.getAttribute('data-confirm-danger');
    const isDanger = isDangerAttr === 'true' || 
        (isDangerAttr !== 'false' && (el.classList.contains('text-red-600') || el.classList.contains('bg-red-600') || el.classList.contains('bg-rose-600') || /hapus|delete|sanksi|nonaktif|keluar/i.test(msg)));

    window.sbConfirm({
        title: 'Konfirmasi',
        text: msg,
        isDanger: isDanger,
        confirmButtonText: el.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan'
    }).then((result) => {
        if (result.isConfirmed) {
            if (el.tagName === 'A' && el.href) {
                window.location.href = el.href;
            } else if (typeof el.onclick === 'function') {
                el.onclick();
            } else {
                const origConfirm = el.getAttribute('data-confirm');
                el.removeAttribute('data-confirm');
                el.click();
                setTimeout(() => {
                    el.setAttribute('data-confirm', origConfirm);
                }, 100);
            }
        }
    });
});

// Global listener for forms with data-confirm or submitted by buttons with data-confirm
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.__sbConfirmed) {
        delete form.__sbConfirmed;
        return;
    }

    const submitter = e.submitter || document.activeElement;
    const confirmMsg = form.getAttribute('data-confirm') || submitter?.getAttribute('data-confirm');
    if (confirmMsg) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const dangerAttr = form.getAttribute('data-confirm-danger') || submitter?.getAttribute('data-confirm-danger');
        let isDanger = false;
        if (dangerAttr === 'true') {
            isDanger = true;
        } else if (dangerAttr === 'false') {
            isDanger = false;
        } else {
            isDanger = form.querySelector('.bg-red-600, .text-red-600, .bg-rose-600') !== null ||
                submitter?.classList.contains('bg-red-600') ||
                submitter?.classList.contains('text-red-600') ||
                /hapus|delete|sanksi|nonaktif|keluar/i.test(confirmMsg);
        }

        const confirmBtn = form.getAttribute('data-confirm-btn') || submitter?.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';

        window.sbConfirm({
            title: 'Konfirmasi',
            text: confirmMsg,
            isDanger: isDanger,
            confirmButtonText: confirmBtn
        }).then((result) => {
            if (result.isConfirmed) {
                form.__sbConfirmed = true;
                if (submitter && submitter.name) {
                    let hidden = form.querySelector(`input[type="hidden"][name="${submitter.name}"]`);
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = submitter.name;
                        form.appendChild(hidden);
                    }
                    hidden.value = submitter.value;
                }
                form.submit();
            }
        });
    }
}, true);

// Otomatis ubah onsubmit/onclick confirm bawaan browser menjadi modal modern SayaBantu
function convertLegacyBrowserAlerts() {
    document.querySelectorAll('form[onsubmit*="confirm("]').forEach(function(form) {
        const onsubmitStr = form.getAttribute('onsubmit') || '';
        const match = onsubmitStr.match(/confirm\s*\(\s*['"`](.*?)['"`]\s*\)/);
        if (match && match[1]) {
            form.removeAttribute('onsubmit');
            form.setAttribute('data-confirm', match[1]);
            if (/hapus|delete|sanksi|nonaktif|keluar|ekstrem/i.test(match[1])) {
                form.setAttribute('data-confirm-danger', 'true');
            }
        }
    });

    document.querySelectorAll('[onclick*="confirm("]').forEach(function(el) {
        const onclickStr = el.getAttribute('onclick') || '';
        const match = onclickStr.match(/confirm\s*\(\s*['"`](.*?)['"`]\s*\)/);
        if (match && match[1]) {
            el.removeAttribute('onclick');
            el.setAttribute('data-confirm', match[1]);
            if (/hapus|delete|sanksi|nonaktif|keluar|ekstrem/i.test(match[1])) {
                el.setAttribute('data-confirm-danger', 'true');
            }
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', convertLegacyBrowserAlerts);
} else {
    convertLegacyBrowserAlerts();
}
document.addEventListener('livewire:navigated', convertLegacyBrowserAlerts);
document.addEventListener('livewire:init', convertLegacyBrowserAlerts);

// Intercept Livewire 3 wire:confirm and wire:confirm.prompt globally using SayaBantu SweetAlert2 dialogs
function wrapLivewireConfirmElement(el, expr, modifiers = []) {
    if (!el || typeof el !== 'object') return;

    const rawMsg = expr || el.getAttribute('wire:confirm') || 'Apakah Anda yakin?';
    const msg = String(rawMsg).replaceAll('\\n', '\n');
    const isPrompt = (Array.isArray(modifiers) && modifiers.includes('prompt')) || el.hasAttribute('wire:confirm.prompt');

    el.__livewire_confirm = (action, instead) => {
        if (isPrompt) {
            const parts = msg.split('|');
            const question = parts[0] || 'Ketik konfirmasi untuk melanjutkan:';
            const expected = parts[1] || '';

            Swal.fire({
                title: 'Konfirmasi Diperlukan',
                text: question,
                input: 'text',
                inputPlaceholder: expected,
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Konfirmasi',
                cancelButtonText: 'Batal',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-3xl shadow-2xl p-5 sm:p-6 border border-gray-100 font-sans !w-[calc(100vw-2rem)] sm:!w-full max-w-sm',
                    title: 'text-base font-extrabold text-gray-900',
                    htmlContainer: 'text-xs text-gray-600 mt-1 leading-relaxed font-medium break-words',
                    input: 'w-full px-3 py-2 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 my-3',
                    actions: 'flex flex-col-reverse sm:flex-row gap-2 sm:gap-2.5 w-full mt-2',
                    confirmButton: 'w-full sm:flex-1 py-3 px-4 rounded-xl font-bold text-xs bg-red-600 hover:bg-red-700 text-white shadow-md transition active:scale-95 cursor-pointer',
                    cancelButton: 'w-full sm:flex-1 py-3 px-4 rounded-xl font-semibold text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 transition active:scale-95 cursor-pointer'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!expected || result.value === expected) {
                        action();
                    } else {
                        if (window.sbToast) {
                            window.sbToast('Teks konfirmasi tidak sesuai!', 'error');
                        }
                        if (typeof instead === 'function') instead();
                    }
                } else {
                    if (typeof instead === 'function') instead();
                }
            });
        } else {
            const isDanger = /hapus|delete|sanksi|nonaktif|keluar|ekstrem|blokir|peringatan|shadow ban/i.test(msg) ||
                el.classList.contains('bg-red-600') ||
                el.classList.contains('text-red-600') ||
                el.classList.contains('border-red-200') ||
                /7e22ce|6b21a8/i.test(el.getAttribute('style') || '');

            window.sbConfirm({
                title: 'Konfirmasi',
                text: msg,
                isDanger: isDanger,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    action();
                } else {
                    if (typeof instead === 'function') instead();
                }
            });
        }
    };
}

// 1. Hook into Livewire directive lifecycle when directives are initialized
function registerLivewireConfirmHooks() {
    if (window.Livewire && typeof window.Livewire.hook === 'function') {
        window.Livewire.hook('directive.init', ({ el, directive }) => {
            if (directive && (directive.value === 'confirm' || directive.name === 'confirm')) {
                wrapLivewireConfirmElement(el, directive.expression, directive.modifiers);
            }
        });
    }
}

if (window.Livewire && typeof window.Livewire.hook === 'function') {
    registerLivewireConfirmHooks();
} else {
    document.addEventListener('livewire:init', registerLivewireConfirmHooks);
}

// 2. Global capture-phase listener to catch any click on wire:confirm elements (works for newly injected DOM / modals)
document.addEventListener('click', function(e) {
    let target = e.target;
    let el = null;
    while (target && target !== document.body && target !== document.documentElement) {
        if (target.hasAttribute && (target.hasAttribute('wire:confirm') || target.hasAttribute('wire:confirm.prompt'))) {
            el = target;
            break;
        }
        target = target.parentElement;
    }

    if (el) {
        wrapLivewireConfirmElement(el, el.getAttribute('wire:confirm') || el.getAttribute('wire:confirm.prompt'));
    }
}, true);

// Pastikan submit form (misalnya via tombol Enter) juga memakai SweetAlert2 sbConfirm
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form && form.hasAttribute && (form.hasAttribute('wire:confirm') || form.hasAttribute('wire:confirm.prompt'))) {
        wrapLivewireConfirmElement(form, form.getAttribute('wire:confirm') || form.getAttribute('wire:confirm.prompt'));
    }
}, true);

// 3. Auto-sync push notification subscription jika izin sudah diberikan sebelumnya
if (typeof window !== 'undefined') {
    const triggerPushSync = () => {
        if (window.SayaBantuPush && typeof window.SayaBantuPush.autoSync === 'function') {
            window.SayaBantuPush.autoSync();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', triggerPushSync);
    } else {
        triggerPushSync();
    }

    document.addEventListener('livewire:navigated', triggerPushSync);
}

// 4. Global Rupiah compact formatter (>= 1.000.000 tanpa pembulatan => 1 jt / 1,5 jt, ganjil tetap utuh)
window.formatNominal = function (amount) {
    const num = Number(amount) || 0;
    const sign = num < 0 ? '-' : '';
    const abs = Math.abs(num);

    if (abs >= 1000000 && Number.isInteger(abs) && abs % 10000 === 0) {
        const millions = abs / 1000000;
        const parts = millions.toFixed(2).split('.');
        const intFormatted = Number(parts[0]).toLocaleString('id-ID');
        const decTrimmed = parts[1].replace(/0+$/, '');
        return sign + (decTrimmed ? `${intFormatted},${decTrimmed}` : intFormatted) + ' jt';
    }

    return sign + Math.round(abs).toLocaleString('id-ID');
};

window.formatRupiah = function (amount, withSpace = true) {
    const num = Number(amount) || 0;
    const sign = num < 0 ? '-' : '';
    const prefix = withSpace ? 'Rp ' : 'Rp';
    return sign + prefix + window.formatNominal(Math.abs(num));
};

// 5. Native App-Style Push Notification Banner
// Laptop (>= 768px): Pojok kanan bawah | HP (< 768px): Atas layar
window._sbLastPushToast = window._sbLastPushToast || { key: '', time: 0 };
window._sbActivePushTimer = null;

function sbEscapeHtml(val) {
    return String(val ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getSbAppIconUrl() {
    const appleIcon = document.querySelector('link[rel="apple-touch-icon"]');
    const relIcon = document.querySelector('link[rel="icon"]');
    return (appleIcon && appleIcon.href) || (relIcon && relIcon.href) || '/icons/icon-192x192.png';
}

window.sbShowPushToast = function (argObj) {
    try {
        let options = argObj || {};
        if (Array.isArray(options)) {
            options = options[0] || {};
        } else if (options && typeof options === 'object' && options.detail) {
            options = Array.isArray(options.detail) ? (options.detail[0] || {}) : options.detail;
        }

        const title = String(options.title || 'SayaBantu').trim();
        const message = String(options.message || options.msg || options.body || '').trim();
        const url = options.url || '#';
        const rawType = String(options.type || 'info').toLowerCase();
        const lowerTitle = title.toLowerCase();

        // Deduplikasi notifikasi yang sama dalam 4 detik
        const dedupeKey = options.dedupeKey || `${title}:::${message}`;
        const now = Date.now();
        if (window._sbLastPushToast.key === dedupeKey && (now - window._sbLastPushToast.time) < 4000) {
            return;
        }
        window._sbLastPushToast = { key: dedupeKey, time: now };

        const isError = rawType === 'error' || rawType === 'danger' || rawType === 'rejected' || lowerTitle.includes('ditolak') || lowerTitle.includes('gagal');
        const isWarning = !isError && (rawType === 'warning' || lowerTitle.includes('peringatan') || lowerTitle.includes('perhatian') || lowerTitle.includes('belum berangkat'));
        const isSuccess = !isError && !isWarning && (rawType === 'success' || rawType === 'approved' || rawType === 'taken' || rawType === 'completed' || lowerTitle.includes('disetujui') || lowerTitle.includes('selesai') || lowerTitle.includes('berhasil') || lowerTitle.includes('diambil'));

        let accentBg = 'bg-[#0066FF]';
        let accentLight = 'bg-blue-50 text-[#0066FF] border-blue-200';
        let badgeLabel = options.badge || 'Notifikasi';
        let badgeClasses = 'bg-blue-50 text-[#0066FF] border-blue-100';
        let borderClass = 'border-slate-200/90';
        let iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>';

        if (isError) {
            accentBg = 'bg-rose-600';
            accentLight = 'bg-rose-50 text-rose-600 border-rose-200';
            badgeLabel = options.badge || 'Perhatian';
            badgeClasses = 'bg-rose-50 text-rose-700 border-rose-200';
            borderClass = 'border-rose-200/90';
            iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M6 18L18 6M6 6l12 12"/>';
        } else if (isWarning) {
            accentBg = 'bg-amber-500';
            accentLight = 'bg-amber-50 text-amber-600 border-amber-200';
            badgeLabel = options.badge || 'Peringatan';
            badgeClasses = 'bg-amber-50 text-amber-800 border-amber-200';
            borderClass = 'border-amber-200/90';
            iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
        } else if (isSuccess) {
            accentBg = 'bg-emerald-600';
            accentLight = 'bg-emerald-50 text-emerald-600 border-emerald-200';
            badgeLabel = options.badge || 'Sukses';
            badgeClasses = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            borderClass = 'border-emerald-200/90';
            iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/>';
        } else if (rawType === 'message' || rawType === 'chat') {
            badgeLabel = options.badge || 'Pesan Baru';
            iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>';
        }

        let actionText = 'Ketuk untuk lihat detail →';
        if (url.includes('topup')) actionText = 'Lihat Riwayat Top-Up →';
        else if (url.includes('withdraw')) actionText = 'Lihat Riwayat Penarikan →';
        else if (url.includes('chat')) actionText = 'Buka Pesan →';

        let dock = document.getElementById('sb-push-dock');
        if (!dock) {
            dock = document.createElement('div');
            dock.id = 'sb-push-dock';
            dock.className = 'sb-push-dock';
            dock.setAttribute('aria-live', 'polite');
            document.body.appendChild(dock);
        }

        if (window._sbActivePushTimer) {
            clearInterval(window._sbActivePushTimer);
            window._sbActivePushTimer = null;
        }
        dock.innerHTML = '';

        const hasUrl = Boolean(url && url !== '#');
        const duration = (isError || isWarning) ? Math.max(Number(options.timeout) || 5500, 8000) : (Number(options.timeout) || 5500);

        const card = document.createElement('div');
        card.className = `sb-push-card pointer-events-auto relative bg-white/95 backdrop-blur-md rounded-2xl border ${borderClass} shadow-2xl overflow-hidden transition-all duration-200 ${hasUrl ? 'cursor-pointer hover:bg-white' : ''}`;
        card.style.boxShadow = '0 16px 36px -8px rgba(15, 23, 42, 0.22), 0 4px 12px -2px rgba(15, 23, 42, 0.08)';

        const iconSrc = getSbAppIconUrl();
        card.innerHTML = `
            <div class="px-3.5 pt-2.5 pb-1 flex items-center justify-between gap-2 border-b border-slate-100/80 bg-slate-50/70">
                <div class="flex items-center gap-1.5 min-w-0">
                    <img src="${sbEscapeHtml(iconSrc)}" alt="SayaBantu" class="w-4 h-4 rounded-md object-cover shadow-2xs shrink-0">
                    <span class="text-[11px] font-bold text-slate-700 tracking-tight">SayaBantu</span>
                    <span class="text-[10px] text-slate-400">• Baru saja</span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold border ${badgeClasses}">${sbEscapeHtml(badgeLabel)}</span>
                    <button type="button" class="sb-push-close text-slate-400 hover:text-slate-600 p-0.5 rounded-md hover:bg-slate-200/60 transition cursor-pointer" title="Tutup" aria-label="Tutup notifikasi">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="p-3.5 flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl ${accentLight} border flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">${iconSvg}</svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-bold text-slate-900 leading-snug line-clamp-1">${sbEscapeHtml(title)}</div>
                    ${message ? `<p class="text-xs text-slate-600 mt-0.5 leading-relaxed line-clamp-2">${sbEscapeHtml(message)}</p>` : ''}
                    ${hasUrl ? `<div class="mt-1.5 text-[11px] font-bold text-[#0066FF]">${sbEscapeHtml(actionText)}</div>` : ''}
                </div>
            </div>
            <div class="h-0.5 w-full bg-slate-100 overflow-hidden">
                <div class="sb-push-progress h-full ${accentBg} transition-all duration-100 ease-linear" style="width: 100%;"></div>
            </div>
        `;

        const dismissCard = () => {
            if (window._sbActivePushTimer) {
                clearInterval(window._sbActivePushTimer);
                window._sbActivePushTimer = null;
            }
            card.style.opacity = '0';
            card.style.transform = window.innerWidth >= 768 ? 'translate3d(20px, 8px, 0)' : 'translate3d(0, -12px, 0)';
            setTimeout(() => {
                if (card.parentNode === dock) dock.innerHTML = '';
            }, 220);
        };

        const closeBtn = card.querySelector('.sb-push-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dismissCard();
            });
        }

        if (hasUrl) {
            card.addEventListener('click', (e) => {
                if (e.target && e.target.closest('.sb-push-close')) return;
                dismissCard();
                window.location.href = url;
            });
        }

        let paused = false;
        let elapsed = 0;
        const stepMs = 100;
        const progressEl = card.querySelector('.sb-push-progress');
        card.addEventListener('mouseenter', () => { paused = true; });
        card.addEventListener('mouseleave', () => { paused = false; });

        dock.appendChild(card);

        if (options.sound !== false) {
            if (options.isUrgent && window.sbNotifSettings?.soundCall && typeof window.playRingChime === 'function') {
                window.playRingChime();
            } else if (typeof window.playNotifChime === 'function') {
                window.playNotifChime();
            }
        }
        if (typeof window.triggerVibrate === 'function') {
            window.triggerVibrate();
        }

        window._sbActivePushTimer = setInterval(() => {
            if (!paused) {
                elapsed += stepMs;
                const pct = Math.max(0, 100 - (elapsed / duration) * 100);
                if (progressEl) progressEl.style.width = `${pct}%`;
                if (elapsed >= duration) {
                    dismissCard();
                }
            }
        }, stepMs);
    } catch (err) {
        console.error('sbShowPushToast error', err);
    }
};





