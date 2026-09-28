document.addEventListener('DOMContentLoaded', () => {
    document
        .querySelectorAll('[data-variant-image-picker]')
        .forEach((picker) => {
            const grid = picker.querySelector('[data-variant-image-grid]');
            const status = picker.querySelector('[data-variant-image-status]');
            const endpoint = picker.dataset.imagesUrl;

            if (!grid || !endpoint) return;

            let controller = null;

            const escapeHtml = (value) => {
                const node = document.createElement('div');
                node.textContent = value ?? '';
                return node.innerHTML;
            };

            const emptyState = (message, productId = '') => {
                const action = productId
                    ? '<a href="/admin/product-images/create?product_id=' + encodeURIComponent(productId) + '" class="admin-btn admin-btn-primary mt-4">افزودن تصویر محصول</a>'
                    : '';

                grid.innerHTML =
                    '<div data-variant-image-empty class="col-span-full rounded-2xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-6 text-center">' +
                    '<p class="text-sm font-semibold text-[var(--admin-text)]">' + escapeHtml(message) + '</p>' +
                    action +
                    '</div>';
            };

            const renderImages = (items) => {
                if (!items.length) {
                    emptyState(
                        'برای این محصول هنوز تصویری ثبت نشده است.',
                        document.querySelector('[data-product-value]')?.value ?? ''
                    );
                    return;
                }

                grid.innerHTML = items.map((image) => {
                    const checked = '';
                    return '<label class="group relative cursor-pointer overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)]">' +
                        '<input type="checkbox" name="image_ids[]" value="' + escapeHtml(image.id) + '" class="peer sr-only" ' + checked + '>' +
                        '<img src="' + escapeHtml(image.url) + '" alt="' + escapeHtml(image.alt || 'تصویر محصول') + '" class="aspect-[4/3] w-full object-cover transition duration-200 group-hover:scale-[1.02]" loading="lazy">' +
                        '<span class="absolute inset-0 hidden bg-black/10 peer-checked:block"></span>' +
                        '<span class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full border border-white/60 bg-white/90 text-xs opacity-0 shadow-sm peer-checked:opacity-100">✓</span>' +
                        '<span class="block border-t border-[var(--admin-border)] px-3 py-2 text-[10px] text-[var(--admin-muted)]">#' + Number(image.sort_order ?? 0) + 1 + '</span>' +
                        '</label>';
                }).join('');
            };

            const loadImages = async (productId) => {
                controller?.abort();

                if (!productId) {
                    emptyState('ابتدا یک محصول انتخاب کنید.');
                    if (status) status.textContent = 'انتخاب محصول الزامی است.';
                    return;
                }

                controller = new AbortController();
                if (status) status.textContent = 'در حال دریافت تصاویر محصول...';

                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('product_id', productId);

                try {
                    const response = await fetch(url, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        signal: controller.signal,
                    });

                    if (!response.ok) throw new Error('variant-images-failed');

                    const payload = await response.json();
                    renderImages(payload.data ?? []);

                    if (status) {
                        status.textContent = (payload.data ?? []).length > 0
                            ? 'تصاویر محصول را برای این تنوع انتخاب کنید.'
                            : 'این محصول هنوز تصویری ندارد.';
                    }
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    emptyState('دریافت تصاویر محصول انجام نشد. دوباره تلاش کنید.');
                    if (status) status.textContent = 'ارتباط با سرویس تصاویر برقرار نشد.';
                }
            };

            document.addEventListener('admin-product-selected', (event) => {
                loadImages(event.detail?.id ?? '');
            });
        });
});