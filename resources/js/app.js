import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

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
        if (/category/.test(key)) return 1;
        if (/featured|blog|og_image|banner|hero|editor/.test(key)) return 16 / 9;
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
        const previewImage = preview.querySelector('img'); let previewUrl = null;
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

Alpine.start();
