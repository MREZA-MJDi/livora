@php
    $inputName = $inputName ?? 'product_id';
    $fieldId = $fieldId ?? 'product-selector-' . str_replace(['[', ']'], '-', $inputName);
    $selectedProductId = old($inputName, $selectedProduct?->id ?? '');
    $selectedProductLabel = $selectedProduct?->name ?? '';
    $required = $required ?? false;
@endphp

<div
    data-admin-product-selector
    class="relative"
    data-search-url="{{ route('admin.product-images.product-options') }}"
    data-selected-id="{{ $selectedProductId }}"
    data-selected-label="{{ $selectedProductLabel }}"
>
    <label for="{{ $fieldId }}-search" class="admin-label">
        {{ $label ?? 'محصول' }}
    </label>

    <div class="relative">
        <div class="admin-search">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.6"
                stroke="currentColor"
                class="admin-search-icon h-4 w-4"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                />
            </svg>

            <input
                id="{{ $fieldId }}-search"
                type="search"
                class="admin-search-input"
                value="{{ $selectedProductLabel }}"
                placeholder="{{ $placeholder ?? 'حداقل ۲ حرف از نام محصول یا SKU...' }}"
                autocomplete="off"
                role="combobox"
                aria-expanded="false"
                aria-controls="{{ $fieldId }}-options"
                data-product-search
            >
        </div>

        <input
            type="hidden"
            id="{{ $fieldId }}"
            name="{{ $inputName }}"
            value="{{ $selectedProductId }}"
            data-product-value
            @if($required) required @endif
        >

        <div
            id="{{ $fieldId }}-options"
            data-product-results
            class="absolute inset-x-0 top-full z-40 mt-2 hidden max-h-72 overflow-y-auto rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-2 shadow-[var(--admin-shadow-lg)]"
        ></div>
    </div>

    <div
        data-product-selected
        class="mt-3 {{ $selectedProductId ? '' : 'hidden' }}"
    >
        <div class="flex items-center justify-between gap-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-3">
            <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--admin-muted)]">
                    PRODUCT
                </p>

                <p
                    data-product-selected-label
                    class="mt-1 truncate text-sm font-semibold text-[var(--admin-text)]"
                >
                    {{ $selectedProductLabel }}
                </p>
            </div>

            <button
                type="button"
                data-product-clear
                class="admin-btn admin-btn-ghost px-3"
            >
                تغییر
            </button>
        </div>
    </div>

    <p data-product-status class="admin-help">
        @if($selectedProductId)
            محصول انتخاب شده است.
        @else
            برای جستجو حداقل ۲ حرف وارد کنید.
        @endif
    </p>
</div>
