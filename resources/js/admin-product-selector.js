document.addEventListener('DOMContentLoaded', () => {
    document
        .querySelectorAll('[data-admin-product-selector]')
        .forEach((root) => {
            const input = root.querySelector('[data-product-search]');
            const hidden = root.querySelector('[data-product-value]');
            const results = root.querySelector('[data-product-results]');
            const selected = root.querySelector('[data-product-selected]');
            const selectedLabel = root.querySelector('[data-product-selected-label]');
            const clear = root.querySelector('[data-product-clear]');
            const status = root.querySelector('[data-product-status]');
            const url = root.dataset.searchUrl;

            if (!input || !hidden || !results || !url) {
                return;
            }

            let timer = null;
            let controller = null;

            const escapeHtml = (value) => {
                const node = document.createElement('div');
                node.textContent = value ?? '';
                return node.innerHTML;
            };

            const setOpen = (open) => {
                results.classList.toggle('hidden', !open);
                input.setAttribute(
                    'aria-expanded',
                    open ? 'true' : 'false'
                );
            };

            const selectProduct = (product) => {
                hidden.value = String(product.id);
                input.value = product.label;

                if (selectedLabel) {
                    selectedLabel.textContent = product.label;
                }

                selected?.classList.remove('hidden');
                status.textContent = product.sku
                    ? `SKU: ${product.sku}`
                    : 'محصول انتخاب شد.';

                results.innerHTML = '';

                root.dispatchEvent(
                    new CustomEvent('admin-product-selected', {
                        bubbles: true,
                        detail: product,
                    })
                );

                setOpen(false);
            };

            const clearProduct = () => {
                hidden.value = '';
                input.value = '';

                selected?.classList.add('hidden');

                status.textContent =
                    'برای جستجو حداقل ۲ حرف وارد کنید.';

                setOpen(false);

                root.dispatchEvent(
                    new CustomEvent('admin-product-selected', {
                        bubbles: true,
                        detail: null,
                    })
                );

                input.focus();
            };

            const renderResults = (items) => {
                if (!items.length) {
                    results.innerHTML = `
                        <div class="px-3 py-4 text-center text-xs text-[var(--admin-muted)]">
                            محصولی مطابق جستجو پیدا نشد.
                        </div>
                    `;

                    setOpen(true);
                    return;
                }

                results.innerHTML = items
                    .map((product) => `
                        <button
                            type="button"
                            data-product-option
                            data-id="${escapeHtml(product.id)}"
                            data-label="${escapeHtml(product.label)}"
                            data-sku="${escapeHtml(product.sku ?? '')}"
                            class="flex w-full items-center justify-between gap-4 rounded-xl px-3 py-3 text-right transition hover:bg-[var(--admin-surface)]"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-[var(--admin-text)]">
                                    ${escapeHtml(product.label)}
                                </span>

                                ${product.sku
                                    ? `
                                        <span class="mt-1 block font-mono text-[10px] text-[var(--admin-muted)]">
                                            ${escapeHtml(product.sku)}
                                        </span>
                                    `
                                    : ''
                                }
                            </span>

                            <span class="shrink-0 text-[10px] font-semibold text-[var(--admin-accent)]">
                                انتخاب
                            </span>
                        </button>
                    `)
                    .join('');

                setOpen(true);
            };

            const search = async () => {
                const query = input.value.trim();

                if (query.length < 2) {
                    results.innerHTML = '';
                    setOpen(false);
                    status.textContent =
                        'برای جستجو حداقل ۲ حرف وارد کنید.';
                    return;
                }

                controller?.abort();
                controller = new AbortController();

                status.textContent = 'در حال جستجوی کاتالوگ...';

                const endpoint = new URL(
                    url,
                    window.location.origin
                );

                endpoint.searchParams.set('q', query);

                try {
                    const response = await fetch(
                        endpoint,
                        {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            signal: controller.signal,
                        }
                    );

                    if (!response.ok) {
                        throw new Error('product-search-failed');
                    }

                    const payload = await response.json();

                    renderResults(payload.data ?? []);

                    status.textContent =
                        (payload.data ?? []).length > 0
                            ? 'نتایج مرتبط را انتخاب کنید.'
                            : 'محصولی مطابق جستجو پیدا نشد.';
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    results.innerHTML = `
                        <div class="px-3 py-4 text-center text-xs text-[var(--admin-danger)]">
                            جستجو انجام نشد. دوباره تلاش کنید.
                        </div>
                    `;

                    setOpen(true);

                    status.textContent =
                        'ارتباط با سرویس جستجوی محصولات برقرار نشد.';
                }
            };

            const hydrateSelectedProduct = async () => {
                const selectedId = root.dataset.selectedId;

                if (!selectedId || selectedLabel?.textContent?.trim()) {
                    return;
                }

                const endpoint = new URL(
                    url,
                    window.location.origin
                );

                endpoint.searchParams.set('id', selectedId);

                try {
                    const response = await fetch(endpoint, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const product = payload.data?.[0];

                    if (!product) {
                        return;
                    }

                    hidden.value = String(product.id);
                    input.value = product.label;

                    if (selectedLabel) {
                        selectedLabel.textContent = product.label;
                    }

                    selected?.classList.remove('hidden');
                    status.textContent = product.sku
                        ? `SKU: ${product.sku}`
                        : 'محصول انتخاب شد.';
                } catch {
                    status.textContent =
                        'محصول انتخاب‌شده را دوباره جستجو کنید.';
                }
            };

            hydrateSelectedProduct();

            input.addEventListener('input', () => {
                if (
                    hidden.value
                    && input.value !== selectedLabel?.textContent?.trim()
                ) {
                    hidden.value = '';
                    selected?.classList.add('hidden');
                }

                clearTimeout(timer);

                timer = setTimeout(
                    search,
                    250
                );
            });

            input.addEventListener('focus', () => {
                if (input.value.trim().length >= 2) {
                    search();
                }
            });

            results.addEventListener('click', (event) => {
                const option =
                    event.target.closest('[data-product-option]');

                if (!option) {
                    return;
                }

                selectProduct({
                    id: option.dataset.id,
                    label: option.dataset.label,
                    sku: option.dataset.sku,
                });
            });

            clear?.addEventListener(
                'click',
                clearProduct
            );

            document.addEventListener('click', (event) => {
                if (!root.contains(event.target)) {
                    setOpen(false);
                }
            });
        });
});
