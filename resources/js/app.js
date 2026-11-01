import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

const jalaliMonthNames = [
    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
];

window.jalaliDatePicker = (initialGregorian = '') => ({
    open: false,
    year: 1400,
    month: 1,
    selectedDate: null,
    gregorianValue: initialGregorian || '',
    displayValue: '',
    time: '09:00',
    weekdays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
    monthNames: jalaliMonthNames,

    init() {
        const today = new Date();
        const current = this.toJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
        this.year = current[0];
        this.month = current[1];

        const value = this.normalizeDigits(this.gregorianValue);
        const match = value.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T\s](\d{2}):(\d{2}))?/);

        if (!match) {
            this.gregorianValue = '';
            return;
        }

        const converted = this.toJalali(Number(match[1]), Number(match[2]), Number(match[3]));
        this.year = converted[0];
        this.month = converted[1];
        this.selectedDate = { year: converted[0], month: converted[1], day: converted[2] };
        this.time = match[4] && match[5] ? match[4] + ':' + match[5] : '09:00';
        this.updateValue();
    },

    get monthTitle() {
        return this.persianDigits(this.monthNames[this.month - 1] + ' ' + this.year);
    },

    get calendarDays() {
        const parts = this.toGregorian(this.year, this.month, 1);
        const gy = parts[0], gm = parts[1], gd = parts[2];
        const firstWeekday = (new Date(Date.UTC(gy, gm - 1, gd)).getUTCDay() + 1) % 7;
        const nextYear = this.month === 12 ? this.year + 1 : this.year;
        const nextMonth = this.month === 12 ? 1 : this.month + 1;
        const next = this.toGregorian(nextYear, nextMonth, 1);
        const daysInMonth = Math.round(
            (Date.UTC(next[0], next[1] - 1, next[2]) - Date.UTC(gy, gm - 1, gd)) / 86400000,
        );

        return [
            ...Array(firstWeekday).fill(null),
            ...Array.from({ length: daysInMonth }, (_, index) => index + 1),
        ];
    },

    changeMonth(delta) {
        const next = this.month + delta;
        if (next < 1) {
            this.month = 12;
            this.year -= 1;
        } else if (next > 12) {
            this.month = 1;
            this.year += 1;
        } else {
            this.month = next;
        }
    },

    selectDay(day) {
        if (!day) return;
        this.selectedDate = { year: this.year, month: this.month, day: day };
        this.updateValue();
        this.open = false;
    },

    selectToday() {
        const today = new Date();
        const current = this.toJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
        this.year = current[0];
        this.month = current[1];
        this.selectedDate = { year: current[0], month: current[1], day: current[2] };
        this.updateValue();
        this.open = false;
    },

    clearDate() {
        this.selectedDate = null;
        this.displayValue = '';
        this.gregorianValue = '';
        this.open = false;
    },

    updateValue() {
        if (!this.selectedDate) {
            this.gregorianValue = '';
            this.displayValue = '';
            return;
        }

        const selected = this.selectedDate;
        const parts = this.toGregorian(selected.year, selected.month, selected.day);
        const pad = value => String(value).padStart(2, '0');

        this.gregorianValue = parts[0] + '-' + pad(parts[1]) + '-' + pad(parts[2]) + 'T' + (this.time || '09:00');
        this.displayValue = this.persianDigits(
            selected.year + '/' + pad(selected.month) + '/' + pad(selected.day),
        );
    },

    isSelected(day) {
        return Boolean(
            day
            && this.selectedDate
            && this.selectedDate.year === this.year
            && this.selectedDate.month === this.month
            && this.selectedDate.day === day
        );
    },

    isToday(day) {
        if (!day) return false;
        const today = new Date();
        const current = this.toJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
        return current[0] === this.year && current[1] === this.month && current[2] === day;
    },

    normalizeDigits(value) {
        return String(value).replace(/[۰-۹٠-٩]/g, digit => {
            const code = digit.charCodeAt(0);
            return String(code >= 1776 && code <= 1785 ? code - 1776 : code - 1632);
        });
    },

    persianDigits(value) {
        return String(value).replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
    },

    toJalali(gy, gm, gd) {
        const gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        const jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        const leap = year => year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0);

        gy -= 1600;
        gm -= 1;
        gd -= 1;

        let gDayNo = 365 * gy
            + Math.floor((gy + 3) / 4)
            - Math.floor((gy + 99) / 100)
            + Math.floor((gy + 399) / 400);

        for (let index = 0; index < gm; index += 1) gDayNo += gDaysInMonth[index];
        if (gm > 1 && leap(gy + 1600)) gDayNo += 1;
        gDayNo += gd;

        let jDayNo = gDayNo - 79;
        const jNp = Math.floor(jDayNo / 12053);
        jDayNo %= 12053;

        let jy = 979 + (33 * jNp) + (4 * Math.floor(jDayNo / 1461));
        jDayNo %= 1461;

        if (jDayNo >= 366) {
            jy += Math.floor((jDayNo - 1) / 365);
            jDayNo = (jDayNo - 1) % 365;
        }

        let index = 0;
        for (; index < 11 && jDayNo >= jDaysInMonth[index]; index += 1) {
            jDayNo -= jDaysInMonth[index];
        }

        return [jy, index + 1, jDayNo + 1];
    },

    toGregorian(jy, jm, jd) {
        jy += 1595;
        let days = -355668
            + (365 * jy)
            + (Math.floor(jy / 33) * 8)
            + Math.floor(((jy % 33) + 3) / 4)
            + jd;

        days += jm < 7 ? ((jm - 1) * 31) : (((jm - 7) * 30) + 186);

        let gy = 400 * Math.floor(days / 146097);
        days %= 146097;

        if (days > 36524) {
            gy += 100 * Math.floor(--days / 36524);
            days %= 36524;
            if (days >= 365) days += 1;
        }

        gy += 4 * Math.floor(days / 1461);
        days %= 1461;

        if (days > 365) {
            gy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }

        let gd = days + 1;
        const leap = gy % 4 === 0 && (gy % 100 !== 0 || gy % 400 === 0);
        const gDaysInMonth = [31, leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        let gm = 1;

        for (; gm <= 12 && gd > gDaysInMonth[gm - 1]; gm += 1) {
            gd -= gDaysInMonth[gm - 1];
        }

        return [gy, gm, gd];
    },
});

window.richTextEditor = (uploadUrl) => ({
    wordCount: 0,
    message: '',
    init() {
        this.sync();
        this.$refs.editor.addEventListener('blur', () => this.sync());
        this.$root.closest('form')?.addEventListener('submit', () => this.sync());
    },
    sync() {
        this.$refs.output.value = this.$refs.editor.innerHTML.trim();
        const text = this.$refs.editor.innerText.trim();
        this.wordCount = text ? text.split(/\s+/u).filter(Boolean).length : 0;
    },
    command(name, value = null) {
        this.$refs.editor.focus();
        document.execCommand(name, false, value);
        this.sync();
    },
    addLink() {
        const url = window.prompt('آدرس لینک را وارد کنید');
        if (!url) return;
        try {
            const parsed = new URL(url, window.location.origin);
            if (!['http:', 'https:'].includes(parsed.protocol) && !url.startsWith('/')) throw new Error();
            this.command('createLink', url);
        } catch (_) {
            this.message = 'آدرس لینک معتبر نیست.';
        }
    },
    onKeydown(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            this.$root.closest('form')?.requestSubmit();
        }
    },
    pastePlain(event) {
        event.preventDefault();
        document.execCommand('insertText', false, event.clipboardData?.getData('text/plain') || '');
        this.sync();
    },
    async uploadImage(event) {
        let file = event.target.files?.[0];
        event.target.value = '';
        if (!file) return;
        if (!/^image\/(jpeg|png|webp)$/i.test(file.type) || file.size > 10 * 1024 * 1024) {
            this.message = 'فقط تصویر JPG، PNG یا WEBP تا ۱۰ مگابایت مجاز است.';
            return;
        }
        try {
            file = await window.SillaImageCropper.crop(file, 16 / 9);
        } catch (_) {
            this.message = 'برش تصویر لغو شد.';
            return;
        }
        const data = new FormData();
        data.append('image', file);
        this.message = 'در حال بارگذاری تصویر…';
        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
            });
            const result = await response.json();
            if (!response.ok || !result.url) throw new Error(result.message || 'بارگذاری تصویر انجام نشد.');
            this.$refs.editor.focus();
            const safeUrl = result.url.replace(/&/g, '&amp;').replace(/"/g, '&quot;');
            document.execCommand('insertHTML', false, '<figure><img src="' + safeUrl + '" alt="" loading="lazy" decoding="async"><figcaption>توضیح تصویر</figcaption></figure><p></p>');
            this.message = 'تصویر اضافه شد.';
            this.sync();
        } catch (error) {
            this.message = error.message || 'خطا در بارگذاری تصویر.';
        }
    },
});


(() => {
    const processedInputs = new WeakSet();
    let activeDialog = null;
    const ratioFor = input => {
        const explicit = Number(input.dataset.cropRatio);
        if (Number.isFinite(explicit) && explicit > 0) return explicit;
        const key = ((input.name || '') + ' ' + (input.id || '') + ' ' + (input.closest('form')?.action || '')).toLowerCase();
        if (/category|logo|avatar|profile/.test(key)) return 1;
        if (/og_image/.test(key)) return 1200 / 630;
        if (/featured|blog|banner|hero|editor|pages/.test(key)) return 16 / 9;
        return 4 / 3;
    };
    const makeDialog = () => {
        const root = document.createElement('div');
        root.className = 'sg-crop-backdrop';
        root.hidden = true;
        root.innerHTML = '<section class="sg-crop-dialog" role="dialog" aria-modal="true" aria-labelledby="sg-crop-title" dir="rtl"><header class="sg-crop-header"><div><h2 id="sg-crop-title">تنظیم و برش تصویر</h2><p>تصویر را با کشیدن جابه‌جا کنید و کادر نهایی را تنظیم کنید.</p></div><button type="button" class="sg-crop-close" data-crop-cancel aria-label="بستن">×</button></header><div class="sg-crop-body"><div class="sg-crop-stage" data-crop-stage><img alt="پیش‌نمایش برش تصویر" draggable="false" data-crop-image></div><div class="sg-crop-tools"><label for="sg-crop-zoom">بزرگ‌نمایی</label><input id="sg-crop-zoom" type="range" min="1" max="3" step=".01" value="1" data-crop-zoom><span data-crop-zoom-value>۱×</span></div><p class="sg-crop-hint">برای جابه‌جایی، تصویر را با ماوس یا لمس بکشید. قاب خروجی ثابت می‌ماند.</p></div><footer class="sg-crop-footer"><button type="button" class="admin-btn admin-btn-secondary" data-crop-cancel>انصراف</button><button type="button" class="admin-btn admin-btn-primary" data-crop-apply>تأیید برش و استفاده از تصویر</button></footer></section>';
        document.body.append(root);
        return root;
    };
    const getDialog = () => activeDialog || (activeDialog = makeDialog());
    const cropFile = (file, ratio) => new Promise((resolve, reject) => {
        if (!file || !file.type || !file.type.startsWith('image/') || file.type === 'image/gif') return resolve(file);
        const root = getDialog();
        const stage = root.querySelector('[data-crop-stage]');
        const image = root.querySelector('[data-crop-image]');
        const zoom = root.querySelector('[data-crop-zoom]');
        const zoomValue = root.querySelector('[data-crop-zoom-value]');
        const cancelButtons = root.querySelectorAll('[data-crop-cancel]');
        const applyButton = root.querySelector('[data-crop-apply]');
        const url = URL.createObjectURL(file);
        let scale = 1, baseScale = 1, x = 0, y = 0, startX = 0, startY = 0, pointerId = null, settled = false;
        const cleanup = () => {
            URL.revokeObjectURL(url); image.removeAttribute('src'); root.hidden = true;
            document.body.classList.remove('sg-crop-open');
            cancelButtons.forEach(button => button.removeEventListener('click', onCancel));
            applyButton.removeEventListener('click', onApply);
            stage.removeEventListener('pointerdown', onPointerDown);
            stage.removeEventListener('pointermove', onPointerMove);
            stage.removeEventListener('pointerup', onPointerUp);
            stage.removeEventListener('pointercancel', onPointerUp);
            zoom.removeEventListener('input', onZoom);
            document.removeEventListener('keydown', onKeydown);
        };
        const finish = (callback, value) => { if (settled) return; settled = true; cleanup(); callback(value); };
        const clamp = () => {
            const w = image.naturalWidth * baseScale * scale, h = image.naturalHeight * baseScale * scale;
            x = Math.min(0, Math.max(stage.clientWidth - w, x));
            y = Math.min(0, Math.max(stage.clientHeight - h, y));
            image.style.transform = 'translate(' + x + 'px,' + y + 'px)';
        };
        const render = () => {
            image.style.width = (image.naturalWidth * baseScale * scale) + 'px';
            image.style.height = (image.naturalHeight * baseScale * scale) + 'px';
            clamp();
            zoomValue.textContent = Number(scale.toFixed(2)).toLocaleString('fa-IR') + '×';
        };
        const onCancel = () => finish(reject, new Error('Crop cancelled'));
        const onKeydown = event => { if (event.key === 'Escape') onCancel(); };
        const onPointerDown = event => { pointerId = event.pointerId; startX = event.clientX - x; startY = event.clientY - y; stage.setPointerCapture(pointerId); };
        const onPointerMove = event => { if (pointerId !== event.pointerId) return; x = event.clientX - startX; y = event.clientY - startY; clamp(); };
        const onPointerUp = event => { if (pointerId !== event.pointerId) return; pointerId = null; };
        const onZoom = () => { scale = Number(zoom.value); render(); };
        const onApply = () => {
            const factor = baseScale * scale;
            const sx = Math.max(0, -x / factor), sy = Math.max(0, -y / factor);
            const sw = stage.clientWidth / factor, sh = stage.clientHeight / factor;
            const outW = Math.min(2400, Math.max(800, Math.round(sw))), outH = Math.round(outW / ratio);
            const canvas = document.createElement('canvas'); canvas.width = outW; canvas.height = outH;
            const context = canvas.getContext('2d');
            if (!context) return finish(reject, new Error('Canvas is unavailable'));
            context.imageSmoothingEnabled = true; context.imageSmoothingQuality = 'high';
            context.drawImage(image, sx, sy, sw, sh, 0, 0, outW, outH);
            const mime = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ? file.type : 'image/jpeg';
            canvas.toBlob(blob => {
                if (!blob) return finish(reject, new Error('Image crop failed'));
                const ext = mime === 'image/png' ? 'png' : (mime === 'image/webp' ? 'webp' : 'jpg');
                const output = new File([blob], file.name.replace(/\.[^.]+$/, '') + '-cropped.' + ext, {type:mime,lastModified:Date.now()});
                finish(resolve, output);
            }, mime, mime === 'image/png' ? undefined : .92);
        };
        image.onload = () => {
            root.hidden = false; document.body.classList.add('sg-crop-open');
            stage.style.aspectRatio = String(ratio);
            stage.style.width = Math.min(768, window.innerHeight * .58 * ratio, root.querySelector('.sg-crop-body').clientWidth) + 'px';
            baseScale = Math.max(stage.clientWidth / image.naturalWidth, stage.clientHeight / image.naturalHeight);
            scale = 1; x = (stage.clientWidth - image.naturalWidth * baseScale) / 2;
            y = (stage.clientHeight - image.naturalHeight * baseScale) / 2; zoom.value = '1'; render();
            cancelButtons.forEach(button => button.addEventListener('click', onCancel));
            applyButton.addEventListener('click', onApply);
            stage.addEventListener('pointerdown', onPointerDown); stage.addEventListener('pointermove', onPointerMove);
            stage.addEventListener('pointerup', onPointerUp); stage.addEventListener('pointercancel', onPointerUp);
            zoom.addEventListener('input', onZoom); document.addEventListener('keydown', onKeydown);
        };
        image.onerror = () => finish(reject, new Error('Image could not be read'));
        image.src = url;
    });
    window.SillaImageCropper = { crop: (file, ratio = 4 / 3) => cropFile(file, ratio) };
    const attachInput = input => {
        if (input.dataset.cropperReady === '1' || input.getAttribute('x-ref') === 'imageInput') return;
        input.dataset.cropperReady = '1';
        const form = input.closest('form');
        if (!form || !/\/admin(?:\/|$)/.test(new URL(form.action, location.href).pathname)) return;
        const preview = document.createElement('div');
        preview.className = 'sg-upload-preview'; preview.hidden = true;
        preview.innerHTML = '<img alt="پیش‌نمایش تصویر نهایی"><div><strong>پیش‌نمایش تصویر</strong><span>تصویر پس از برش آماده ذخیره است.</span><button type="button" class="admin-btn admin-btn-secondary sg-recrop">تنظیم مجدد برش</button></div>';
        input.insertAdjacentElement('afterend', preview);
        const previewImage = preview.querySelector('img'); previewImage.style.aspectRatio = String(ratioFor(input)); let previewUrl = null;
        form.addEventListener('submit', event => {
            if (input.dataset.cropping === '1') {
                event.preventDefault();
                window.alert('لطفاً ابتدا برش تصویر را تأیید کنید.');
            }
        });
        input.addEventListener('change', async () => {
            if (processedInputs.has(input)) return;
            const selected = Array.from(input.files || []);
            if (!selected.some(file => file.type.startsWith('image/'))) return;
            if (selected.some(file => file.type.startsWith('image/') && file.size > 10 * 1024 * 1024)) {
                input.value = '';
                window.alert('حجم هر تصویر نباید بیشتر از ۱۰ مگابایت باشد.');
                return;
            }
            processedInputs.add(input); input.dataset.cropping = '1'; input.disabled = true;
            try {
                const result = [];
                for (const file of selected) result.push(file.type.startsWith('image/') && file.type !== 'image/gif' ? await cropFile(file, ratioFor(input)) : file);
                const transfer = new DataTransfer(); result.forEach(file => transfer.items.add(file)); input.files = transfer.files;
                const firstImage = result.find(file => file.type.startsWith('image/'));
                if (firstImage) { if (previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = URL.createObjectURL(firstImage); previewImage.src = previewUrl; preview.hidden = false; preview.querySelector('.sg-recrop').hidden = firstImage.type === 'image/gif'; }
                input.disabled = false; input.dataset.cropping = '0'; processedInputs.delete(input);
                input.dispatchEvent(new Event('input', {bubbles:true}));
                processedInputs.add(input); input.dispatchEvent(new Event('change', {bubbles:true})); processedInputs.delete(input);
            } catch (error) {
                input.value = ''; input.disabled = false; input.dataset.cropping = '0'; processedInputs.delete(input);
                if (error?.message !== 'Crop cancelled') console.error('Image crop failed:', error);
            }
        });
        preview.querySelector('.sg-recrop').addEventListener('click', async () => {
            const file = input.files && input.files[0]; if (!file) return input.click();
            try {
                const cropped = await cropFile(file, ratioFor(input)); const transfer = new DataTransfer();
                Array.from(input.files).forEach((item,index) => transfer.items.add(index === 0 ? cropped : item));
                input.files = transfer.files;
                if (previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = URL.createObjectURL(cropped); previewImage.src = previewUrl;
            } catch (_) { /* User cancelled recropping. */ }
        });
    };
    const scan = () => document.querySelectorAll('input[type="file"]').forEach(attachInput);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', scan); else scan();
})();

// Resolve media skeletons only after the browser has loaded the actual image.
// This keeps SSR cards stable and avoids fake loading states for empty results.
const initCardImageSkeletons = () => {
    document.querySelectorAll('[data-card-media] [data-card-image]').forEach((image) => {
        if (image.dataset.skeletonReady === '1') return;

        image.dataset.skeletonReady = '1';
        const media = image.closest('[data-card-media]');
        if (!media) return;

        const markLoaded = () => {
            if (image.naturalWidth > 0) {
                media.classList.add('is-loaded');
                media.classList.remove('is-error');
            } else {
                media.classList.add('is-error');
                media.classList.remove('is-loaded');
            }
        };

        image.addEventListener('load', markLoaded, { once: true });
        image.addEventListener('error', markLoaded, { once: true });

        if (image.complete) markLoaded();
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCardImageSkeletons, { once: true });
} else {
    initCardImageSkeletons();
}


// AJAX requests made from the admin area receive a structured 419 response.
// Surface a persistent re-authentication prompt instead of leaving the user
// with an unexplained "Page Expired" response inside a dashboard widget.
if (/^\/admin(?:\/|$)/.test(window.location.pathname) && !window.__sgAdminSessionGuard) {
    window.__sgAdminSessionGuard = true;
    const nativeFetch = window.fetch.bind(window);

    window.fetch = async (...args) => {
        const response = await nativeFetch(...args);

        if (response.status === 419) {
            let payload = {};
            try {
                payload = await response.clone().json();
            } catch (_) {
                // Keep the fallback message if a legacy endpoint returns HTML.
            }

            window.dispatchEvent(new CustomEvent('admin:session-expired', {
                detail: payload,
            }));
        }

        return response;
    };
}


Alpine.start();
