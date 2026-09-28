(() => {
    'use strict';

    const ROOT_SELECTOR = '[data-admin-image-cropper]';
    const OUTPUT_WIDTH = 1200;
    const OUTPUT_HEIGHT = 1500;
    const OUTPUT_TYPE = 'image/jpeg';
    const OUTPUT_QUALITY = 0.88;

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function loadImage(source) {
        return new Promise((resolve, reject) => {
            const image = new Image();

            image.onload = () => resolve(image);
            image.onerror = () => reject(new Error('تصویر قابل خواندن نیست.'));

            try {
                const sourceUrl = new URL(
                    source,
                    window.location.href
                );

                if (sourceUrl.origin !== window.location.origin) {
                    image.crossOrigin = 'anonymous';
                }
            } catch (error) {
                // Keep browser defaults for blob/data URLs.
            }

            image.src = source;
        });
    }

    function createModal() {
        const modal = document.createElement('div');

        modal.className = 'admin-image-crop-modal hidden';
        modal.innerHTML = [
            '<div class="admin-image-crop-modal__backdrop" data-crop-cancel></div>',
            '<section class="admin-image-crop-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="admin-image-crop-title">',
            '  <header class="admin-image-crop-modal__header">',
            '    <div>',
            '      <p class="admin-eyebrow">Image framing</p>',
            '      <h2 id="admin-image-crop-title" class="admin-title">قاب تصویر را تنظیم کنید</h2>',
            '    </div>',
            '    <button type="button" class="admin-btn admin-btn-ghost" data-crop-cancel>بستن</button>',
            '  </header>',
            '  <div class="admin-image-crop-modal__body">',
            '    <div class="admin-image-crop-stage" data-crop-stage>',
            '      <canvas data-crop-canvas></canvas>',
            '      <div class="admin-image-crop-stage__frame" aria-hidden="true"></div>',
            '    </div>',
            '    <div class="admin-image-crop-controls">',
            '      <label class="admin-image-crop-zoom">',
            '        <span>بزرگ‌نمایی</span>',
            '        <input type="range" min="1" max="3" step="0.01" value="1" data-crop-zoom>',
            '      </label>',
            '      <p class="admin-help">تصویر را با کشیدن جابه‌جا کنید و با اسکرول یا نوار بزرگ‌نمایی قاب را تنظیم کنید.</p>',
            '    </div>',
            '  </div>',
            '  <footer class="admin-image-crop-modal__footer">',
            '    <button type="button" class="admin-btn admin-btn-secondary" data-crop-cancel>انصراف</button>',
            '    <button type="button" class="admin-btn admin-btn-primary" data-crop-confirm>اعمال برش</button>',
            '  </footer>',
            '</section>'
        ].join('');

        document.body.appendChild(modal);

        return {
            modal,
            stage: modal.querySelector('[data-crop-stage]'),
            canvas: modal.querySelector('[data-crop-canvas]'),
            zoom: modal.querySelector('[data-crop-zoom]'),
            confirm: modal.querySelector('[data-crop-confirm]'),
            cancel: Array.from(modal.querySelectorAll('[data-crop-cancel]')),
        };
    }

    function draw(state) {
        const rect = state.stage.getBoundingClientRect();
        if (!rect.width || !rect.height || !state.image) {
            return;
        }

        const width = Math.round(rect.width);
        const height = Math.round(rect.height);

        state.canvas.width = width;
        state.canvas.height = height;

        const baseScale = Math.max(
            width / state.image.naturalWidth,
            height / state.image.naturalHeight
        );

        const scale = baseScale * state.zoom;
        const imageWidth = state.image.naturalWidth * scale;
        const imageHeight = state.image.naturalHeight * scale;

        const maxOffsetX = Math.max(0, (imageWidth - width) / 2);
        const maxOffsetY = Math.max(0, (imageHeight - height) / 2);

        state.offsetX = clamp(state.offsetX, -maxOffsetX, maxOffsetX);
        state.offsetY = clamp(state.offsetY, -maxOffsetY, maxOffsetY);

        const context = state.canvas.getContext('2d');

        context.clearRect(0, 0, width, height);
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, width, height);

        context.drawImage(
            state.image,
            (width - imageWidth) / 2 + state.offsetX,
            (height - imageHeight) / 2 + state.offsetY,
            imageWidth,
            imageHeight
        );
    }

    function renderOutput(state) {
        const canvas = document.createElement('canvas');
        canvas.width = OUTPUT_WIDTH;
        canvas.height = OUTPUT_HEIGHT;

        const context = canvas.getContext('2d');
        if (!context || !state.image) {
            return null;
        }

        const stageWidth = state.stage.clientWidth;
        const stageHeight = state.stage.clientHeight;

        const baseScale = Math.max(
            stageWidth / state.image.naturalWidth,
            stageHeight / state.image.naturalHeight
        );

        const scale = baseScale * state.zoom;
        const imageWidth = state.image.naturalWidth * scale;
        const imageHeight = state.image.naturalHeight * scale;

        const maxOffsetX = Math.max(1, (imageWidth - stageWidth) / 2);
        const maxOffsetY = Math.max(1, (imageHeight - stageHeight) / 2);

        const normalizedX = state.offsetX / maxOffsetX;
        const normalizedY = state.offsetY / maxOffsetY;

        const outputScale = Math.max(
            OUTPUT_WIDTH / state.image.naturalWidth,
            OUTPUT_HEIGHT / state.image.naturalHeight
        ) * state.zoom;

        const outputImageWidth =
            state.image.naturalWidth * outputScale;
        const outputImageHeight =
            state.image.naturalHeight * outputScale;

        const outputMaxOffsetX =
            Math.max(0, (outputImageWidth - OUTPUT_WIDTH) / 2);
        const outputMaxOffsetY =
            Math.max(0, (outputImageHeight - OUTPUT_HEIGHT) / 2);

        const drawX =
            (OUTPUT_WIDTH - outputImageWidth) / 2 +
            normalizedX * outputMaxOffsetX;

        const drawY =
            (OUTPUT_HEIGHT - outputImageHeight) / 2 +
            normalizedY * outputMaxOffsetY;

        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, OUTPUT_WIDTH, OUTPUT_HEIGHT);

        context.drawImage(
            state.image,
            drawX,
            drawY,
            outputImageWidth,
            outputImageHeight
        );

        return canvas;
    }

    function canvasToFile(canvas, fileName) {
        return new Promise((resolve, reject) => {
            canvas.toBlob(
                (blob) => {
                    if (!blob) {
                        reject(new Error('برش تصویر انجام نشد.'));
                        return;
                    }

                    resolve(
                        new File(
                            [blob],
                            fileName,
                            {
                                type: OUTPUT_TYPE,
                                lastModified: Date.now(),
                            }
                        )
                    );
                },
                OUTPUT_TYPE,
                OUTPUT_QUALITY
            );
        });
    }

    function replaceInputFile(input, file) {
        const transfer = new DataTransfer();

        transfer.items.add(file);
        input.files = transfer.files;
    }

    function init(root) {
        const input = root.querySelector('[data-crop-input]');
        const preview = root.querySelector('[data-crop-preview]');
        const placeholder = root.querySelector('[data-crop-placeholder]');
        const openButton = root.querySelector('[data-crop-open]');

        if (!input || !preview || !placeholder) {
            return;
        }

        const modal = createModal();

        const state = {
            image: null,
            zoom: 1,
            offsetX: 0,
            offsetY: 0,
            stage: modal.stage,
            canvas: modal.canvas,
        };

        let sourceFile = null;
        let objectUrl = null;
        let dragPointerId = null;
        let dragStartX = 0;
        let dragStartY = 0;
        let dragOriginX = 0;
        let dragOriginY = 0;

        const showPreview = (source) => {
            /*
             * Update the preview independently from the crop modal.
             * The selected image must be visible even if the modal/canvas
             * fails to initialize.
             */
            preview.src = source;
            preview.removeAttribute('hidden');
            preview.classList.remove('hidden');
            preview.style.display = 'block';

            if (placeholder) {
                placeholder.classList.add('hidden');
                placeholder.classList.remove('flex');
            }

            if (openButton) {
                openButton.disabled = false;
                openButton.classList.remove('hidden');
                openButton.removeAttribute('disabled');
            }
        };

        const resetCrop = () => {
            state.zoom = 1;
            state.offsetX = 0;
            state.offsetY = 0;
            modal.zoom.value = '1';
            draw(state);
        };

        const openCrop = async (source, file = null) => {
            try {
                state.image = await loadImage(source);
                sourceFile = file;
                resetCrop();

                modal.modal.classList.remove('hidden');
                document.body.classList.add('admin-image-crop-open');

                requestAnimationFrame(() => draw(state));
            } catch (error) {
                window.alert(
                    error?.message ||
                    'امکان باز کردن تصویر برای برش وجود ندارد.'
                );
            }
        };

        const closeCrop = () => {
            modal.modal.classList.add('hidden');
            document.body.classList.remove('admin-image-crop-open');
            state.image = null;
        };

        const handleSelectedFile = (file) => {
            if (!file) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                window.alert('لطفاً یک فایل تصویری معتبر انتخاب کنید.');
                input.value = '';
                return;
            }

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }

            objectUrl = URL.createObjectURL(file);

            // Show the raw selected file immediately.
            showPreview(objectUrl);

            // Then open the cropper; a cropper failure must not hide the preview.
            void openCrop(objectUrl, file);
        };

        input.addEventListener('change', (event) => {
            handleSelectedFile(event.target.files?.[0] || null);
        });

        /*
         * Defensive delegated listener for admin forms that may be rendered
         * dynamically or re-initialized after navigation.
         */
        document.addEventListener('change', (event) => {
            if (event.target !== input) {
                return;
            }

            handleSelectedFile(input.files?.[0] || null);
        });

        if (openButton) {
            openButton.addEventListener('click', () => {
                const file = input.files?.[0];

                if (file) {
                    if (objectUrl) {
                        URL.revokeObjectURL(objectUrl);
                    }

                    objectUrl = URL.createObjectURL(file);

                    openCrop(objectUrl, file);
                    return;
                }

                const currentSource =
                    preview.currentSrc ||
                    preview.getAttribute('src');

                if (currentSource) {
                    openCrop(currentSource);
                }
            });
        }

        modal.zoom.addEventListener('input', () => {
            state.zoom = Number(modal.zoom.value) || 1;
            draw(state);
        });

        modal.stage.addEventListener('wheel', (event) => {
            event.preventDefault();

            state.zoom = clamp(
                state.zoom + (event.deltaY < 0 ? 0.08 : -0.08),
                1,
                3
            );

            modal.zoom.value = state.zoom.toFixed(2);
            draw(state);
        }, { passive: false });

        modal.stage.addEventListener('pointerdown', (event) => {
            dragPointerId = event.pointerId;
            dragStartX = event.clientX;
            dragStartY = event.clientY;
            dragOriginX = state.offsetX;
            dragOriginY = state.offsetY;

            modal.stage.setPointerCapture(event.pointerId);
            modal.stage.classList.add('is-dragging');
        });

        modal.stage.addEventListener('pointermove', (event) => {
            if (dragPointerId !== event.pointerId) {
                return;
            }

            state.offsetX =
                dragOriginX +
                (event.clientX - dragStartX);

            state.offsetY =
                dragOriginY +
                (event.clientY - dragStartY);

            draw(state);
        });

        const endDrag = () => {
            dragPointerId = null;
            modal.stage.classList.remove('is-dragging');
        };

        modal.stage.addEventListener('pointerup', endDrag);
        modal.stage.addEventListener('pointercancel', endDrag);

        modal.cancel.forEach((button) => {
            button.addEventListener('click', closeCrop);
        });

        modal.confirm.addEventListener('click', async () => {
            const outputCanvas = renderOutput(state);

            if (!outputCanvas) {
                return;
            }

            modal.confirm.disabled = true;

            try {
                const baseName =
                    (sourceFile?.name || 'image')
                        .replace(/\\.[^.]+$/, '');

                const file =
                    await canvasToFile(
                        outputCanvas,
                        baseName + '-cropped.jpg'
                    );

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                }

                objectUrl = URL.createObjectURL(file);
                showPreview(objectUrl);
                replaceInputFile(input, file);
                closeCrop();
            } catch (error) {
                window.alert(
                    error?.message ||
                    'ذخیره برش تصویر انجام نشد.'
                );
            } finally {
                modal.confirm.disabled = false;
            }
        });
    }

    function boot() {
        document
            .querySelectorAll(ROOT_SELECTOR)
            .forEach(init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();