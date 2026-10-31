const cropperStates = new WeakMap();
const objectUrls = new WeakMap();

function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
}

function createCropper(input) {
    const panel = document.createElement('section');
    panel.className = 'admin-cropper';
    panel.setAttribute('aria-label', 'ویرایش کادر تصویر');

    const top = document.createElement('div');
    top.className = 'admin-cropper__top';

    const headingWrap = document.createElement('div');
    const title = document.createElement('p');
    title.className = 'admin-cropper__title';
    title.textContent = (input.dataset.cropLabel || 'تصویر') + ' — تنظیم کادر برش';

    const hint = document.createElement('p');
    hint.className = 'admin-cropper__hint';
    hint.textContent = 'تصویر را داخل کادر بکش؛ روی موبایل هم با لمس جابه‌جا می‌شود.';

    headingWrap.append(title, hint);

    const reset = document.createElement('button');
    reset.type = 'button';
    reset.className = 'admin-cropper__reset';
    reset.textContent = 'بازنشانی کادر';

    top.append(headingWrap, reset);

    const viewport = document.createElement('div');
    viewport.className = 'admin-cropper__viewport';
    viewport.setAttribute('role', 'group');
    viewport.setAttribute('aria-label', 'کادر قابل جابه‌جایی تصویر');

    const image = document.createElement('img');
    image.className = 'admin-cropper__image';
    image.alt = '';
    image.draggable = false;

    const guide = document.createElement('span');
    guide.className = 'admin-cropper__guide';
    guide.setAttribute('aria-hidden', 'true');
    viewport.append(image, guide);

    const controls = document.createElement('div');
    controls.className = 'admin-cropper__controls';

    const zoomLabel = document.createElement('label');
    zoomLabel.className = 'admin-cropper__zoom-label';
    zoomLabel.textContent = 'بزرگ‌نمایی';

    const zoom = document.createElement('input');
    zoom.type = 'range';
    zoom.className = 'admin-cropper__zoom';
    zoom.min = '1';
    zoom.max = '3.5';
    zoom.step = '0.01';
    zoom.value = '1';
    zoom.setAttribute('aria-label', 'میزان بزرگ‌نمایی تصویر');

    const zoomValue = document.createElement('output');
    zoomValue.className = 'admin-cropper__zoom-value';
    zoomValue.textContent = '100%';

    controls.append(zoomLabel, zoom, zoomValue);

    const footer = document.createElement('p');
    footer.className = 'admin-cropper__footer';
    footer.textContent = 'کادر ثابت است؛ فقط تصویر را جابه‌جا یا بزرگ‌نمایی کن تا ترکیب دلخواهت ساخته شود.';

    const status = document.createElement('p');
    status.className = 'admin-cropper__status';
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');

    panel.append(top, viewport, controls, footer, status);
    input.insertAdjacentElement('afterend', panel);

    const ratioParts = (input.dataset.cropRatio || '4:3').split(':').map(Number);
    const ratioWidth = Math.max(1, ratioParts[0] || 4);
    const ratioHeight = Math.max(1, ratioParts[1] || 3);
    viewport.style.aspectRatio = ratioWidth + ' / ' + ratioHeight;

    const state = {
        panel,
        viewport,
        image,
        zoom,
        zoomValue,
        status,
        ratioWidth,
        ratioHeight,
        naturalWidth: 0,
        naturalHeight: 0,
        viewWidth: 0,
        viewHeight: 0,
        baseScale: 1,
        scale: 1,
        renderedWidth: 0,
        renderedHeight: 0,
        left: 0,
        top: 0,
        pointerId: null,
        pointerX: 0,
        pointerY: 0,
        ready: false,
    };

    cropperStates.set(input, state);

    function clampPosition() {
        state.left = clamp(state.left, state.viewWidth - state.renderedWidth, 0);
        state.top = clamp(state.top, state.viewHeight - state.renderedHeight, 0);
    }

    function paint() {
        state.renderedWidth = state.naturalWidth * state.baseScale * state.scale;
        state.renderedHeight = state.naturalHeight * state.baseScale * state.scale;
        clampPosition();
        image.style.width = state.renderedWidth + 'px';
        image.style.height = state.renderedHeight + 'px';
        image.style.left = state.left + 'px';
        image.style.top = state.top + 'px';
        zoom.value = String(state.scale);
        zoomValue.textContent = Math.round(state.scale * 100) + '%';
    }

    function fitViewport(preserveFocus) {
        const rect = viewport.getBoundingClientRect();
        if (!rect.width || !rect.height || !state.naturalWidth || !state.naturalHeight) return;

        let focusX = 0.5;
        let focusY = 0.5;
        if (preserveFocus && state.renderedWidth && state.renderedHeight) {
            focusX = clamp((state.viewWidth / 2 - state.left) / state.renderedWidth, 0, 1);
            focusY = clamp((state.viewHeight / 2 - state.top) / state.renderedHeight, 0, 1);
        }

        state.viewWidth = rect.width;
        state.viewHeight = rect.height;
        state.baseScale = Math.max(
            state.viewWidth / state.naturalWidth,
            state.viewHeight / state.naturalHeight
        );
        state.renderedWidth = state.naturalWidth * state.baseScale * state.scale;
        state.renderedHeight = state.naturalHeight * state.baseScale * state.scale;
        state.left = state.viewWidth / 2 - focusX * state.renderedWidth;
        state.top = state.viewHeight / 2 - focusY * state.renderedHeight;
        paint();
    }

    function setZoom(nextValue) {
        const nextScale = clamp(Number(nextValue) || 1, 1, 3.5);
        const focusX = state.renderedWidth
            ? clamp((state.viewWidth / 2 - state.left) / state.renderedWidth, 0, 1)
            : 0.5;
        const focusY = state.renderedHeight
            ? clamp((state.viewHeight / 2 - state.top) / state.renderedHeight, 0, 1)
            : 0.5;

        state.scale = nextScale;
        state.renderedWidth = state.naturalWidth * state.baseScale * state.scale;
        state.renderedHeight = state.naturalHeight * state.baseScale * state.scale;
        state.left = state.viewWidth / 2 - focusX * state.renderedWidth;
        state.top = state.viewHeight / 2 - focusY * state.renderedHeight;
        paint();
    }

    image.addEventListener('load', () => {
        state.naturalWidth = image.naturalWidth;
        state.naturalHeight = image.naturalHeight;
        state.scale = 1;
        state.ready = true;
        panel.classList.add('is-active');
        requestAnimationFrame(() => fitViewport(false));
        state.status.textContent = '';
    });

    image.addEventListener('error', () => {
        state.status.textContent = 'خواندن تصویر ممکن نشد؛ یک فایل تصویری معتبر انتخاب کن.';
        state.ready = false;
    });

    viewport.addEventListener('pointerdown', event => {
        if (!state.ready || (event.pointerType === 'mouse' && event.button !== 0)) return;
        event.preventDefault();
        state.pointerId = event.pointerId;
        state.pointerX = event.clientX;
        state.pointerY = event.clientY;
        viewport.classList.add('is-dragging');
        viewport.setPointerCapture?.(event.pointerId);
    });

    viewport.addEventListener('pointermove', event => {
        if (state.pointerId !== event.pointerId) return;
        event.preventDefault();
        state.left += event.clientX - state.pointerX;
        state.top += event.clientY - state.pointerY;
        state.pointerX = event.clientX;
        state.pointerY = event.clientY;
        paint();
    });

    function stopDragging(event) {
        if (state.pointerId !== event.pointerId) return;
        state.pointerId = null;
        viewport.classList.remove('is-dragging');
        if (viewport.hasPointerCapture?.(event.pointerId)) {
            viewport.releasePointerCapture(event.pointerId);
        }
    }

    viewport.addEventListener('pointerup', stopDragging);
    viewport.addEventListener('pointercancel', stopDragging);
    viewport.addEventListener('lostpointercapture', () => {
        state.pointerId = null;
        viewport.classList.remove('is-dragging');
    });

    zoom.addEventListener('input', () => setZoom(zoom.value));
    reset.addEventListener('click', () => {
        state.scale = 1;
        state.left = (state.viewWidth - state.naturalWidth * state.baseScale) / 2;
        state.top = (state.viewHeight - state.naturalHeight * state.baseScale) / 2;
        paint();
        state.status.textContent = 'کادر به حالت اولیه برگشت.';
    });

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(() => fitViewport(true));
        observer.observe(viewport);
        state.observer = observer;
    } else {
        window.addEventListener('resize', () => fitViewport(true), { passive: true });
    }

    return state;
}

function loadSelectedImage(input) {
    const file = input.files && input.files[0];
    if (!file) return;

    if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
        const state = cropperStates.get(input);
        if (state) state.status.textContent = 'فقط JPG، PNG یا WEBP پشتیبانی می‌شود.';
        input.value = '';
        return;
    }

    const previousUrl = objectUrls.get(input);
    if (previousUrl) URL.revokeObjectURL(previousUrl);

    const url = URL.createObjectURL(file);
    objectUrls.set(input, url);

    const oldState = cropperStates.get(input);
    if (oldState) {
        oldState.observer?.disconnect();
        oldState.panel.remove();
        cropperStates.delete(input);
    }

    const state = createCropper(input);
    state.image.src = url;

    const previewTargetId = input.dataset.previewTarget;
    const previewTarget = previewTargetId ? document.getElementById(previewTargetId) : null;
    if (previewTarget) {
        previewTarget.src = url;
        previewTarget.classList.remove('hidden');
        previewTarget.classList.add('block');
        previewTarget.parentElement?.querySelector('[data-hero-empty]')?.classList.add('hidden');
    }
}

function makeCroppedFile(input, state) {
    return new Promise((resolve, reject) => {
        if (!state.ready || !state.image.complete) {
            reject(new Error('تصویر هنوز برای برش آماده نیست.'));
            return;
        }

        const viewWidth = state.viewWidth;
        const viewHeight = state.viewHeight;
        const sourceX = clamp((-state.left / state.renderedWidth) * state.naturalWidth, 0, state.naturalWidth);
        const sourceY = clamp((-state.top / state.renderedHeight) * state.naturalHeight, 0, state.naturalHeight);
        const sourceWidth = Math.min(
            state.naturalWidth - sourceX,
            (viewWidth / state.renderedWidth) * state.naturalWidth
        );
        const sourceHeight = Math.min(
            state.naturalHeight - sourceY,
            (viewHeight / state.renderedHeight) * state.naturalHeight
        );

        const maxWidth = Math.max(320, Number(input.dataset.cropWidth) || 1600);
        const maxHeight = Math.max(240, Number(input.dataset.cropHeight) || 1200);
        const outputScale = Math.min(1, maxWidth / sourceWidth, maxHeight / sourceHeight);
        const outputWidth = Math.max(1, Math.round(sourceWidth * outputScale));
        const outputHeight = Math.max(1, Math.round(sourceHeight * outputScale));

        const canvas = document.createElement('canvas');
        canvas.width = outputWidth;
        canvas.height = outputHeight;

        const context = canvas.getContext('2d', { alpha: true });
        if (!context) {
            reject(new Error('ساخت تصویر برش‌خورده در این مرورگر ممکن نیست.'));
            return;
        }

        context.imageSmoothingEnabled = true;
        context.imageSmoothingQuality = 'high';
        context.drawImage(
            state.image,
            sourceX, sourceY, sourceWidth, sourceHeight,
            0, 0, outputWidth, outputHeight
        );

        canvas.toBlob(blob => {
            if (!blob) {
                reject(new Error('تبدیل تصویر به WEBP انجام نشد.'));
                return;
            }

            const originalName = (input.files?.[0]?.name || 'image').replace(/\.[^.]+$/, '');
            resolve(new File(
                [blob],
                originalName + '-cropped.webp',
                { type: 'image/webp', lastModified: Date.now() }
            ));
        }, 'image/webp', 0.91);
    });
}

function prepareFormSubmission(form, inputs) {
    form.addEventListener('submit', async event => {
        if (form.dataset.cropperPrepared === '1') {
            delete form.dataset.cropperPrepared;
            return;
        }

        const pendingInputs = inputs.filter(input =>
            input.form === form && input.files?.length && cropperStates.has(input)
        );
        if (!pendingInputs.length || form.dataset.cropperBusy === '1') return;

        event.preventDefault();
        form.dataset.cropperBusy = '1';

        const submitter = event.submitter || null;
        const submitButtons = Array.from(form.querySelectorAll('button[type="submit"], input[type="submit"]'));
        submitButtons.forEach(button => { button.disabled = true; });

        try {
            for (const input of pendingInputs) {
                const state = cropperStates.get(input);
                state.status.textContent = 'در حال آماده‌سازی تصویر برش‌خورده…';

                const croppedFile = await makeCroppedFile(input, state);
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                input.files = transfer.files;

                state.status.textContent = 'کادر تصویر آماده ذخیره است.';
            }

            delete form.dataset.cropperBusy;
            form.dataset.cropperPrepared = '1';
            submitButtons.forEach(button => { button.disabled = false; });

            if (submitter && submitter.form === form) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }
        } catch (error) {
            delete form.dataset.cropperBusy;
            submitButtons.forEach(button => { button.disabled = false; });
            const state = pendingInputs.map(input => cropperStates.get(input)).find(Boolean);
            if (state) state.status.textContent = error?.message || 'آماده‌سازی تصویر ناموفق بود؛ دوباره تلاش کن.';
        }
    });
}

function initializeImageCroppers() {
    const inputs = Array.from(document.querySelectorAll('input[type="file"][data-image-cropper]'));
    if (!inputs.length) return;

    inputs.forEach(input => {
        input.addEventListener('change', () => loadSelectedImage(input));
    });

    const forms = new Set(inputs.map(input => input.form).filter(Boolean));
    forms.forEach(form => prepareFormSubmission(
        form,
        inputs.filter(input => input.form === form)
    ));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeImageCroppers, { once: true });
} else {
    initializeImageCroppers();
}
