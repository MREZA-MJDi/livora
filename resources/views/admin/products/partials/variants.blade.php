<div class="admin-card p-6">

    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

        <div>
            <h3 class="text-base font-bold text-[var(--admin-text)]">
                تنوع‌های محصول
            </h3>

            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                مدیریت رنگ، اندازه، قیمت و موجودی تنوع‌های محصول.
            </p>
        </div>

        <a
            href="{{ route('admin.product-variants.create', ['product_id' => $product->id]) }}"
            class="admin-btn admin-btn-secondary"
        >
            افزودن تنوع
        </a>

    </div>


    @if($product->variants->count())

        <div class="admin-table-wrap">

            <div class="overflow-x-auto">

                <table class="admin-table">

                    <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>SKU</th>
                        <th>قیمت</th>
                        <th>موجودی</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($product->variants as $variant)

                        <tr>

                            <td>
                                <div class="font-semibold text-[var(--admin-text)]">
                                    {{ $variant->name ?? $variant->title ?? 'تنوع محصول' }}
                                </div>
                            </td>

                            <td>
                                    <span class="font-mono text-xs text-[var(--admin-text-soft)]">
                                        {{ $variant->sku ?? '—' }}
                                    </span>
                            </td>

                            <td>
                                @if(isset($variant->price))
                                    {{ number_format((float) $variant->price) }}
                                    <span class="text-xs text-[var(--admin-muted)]">
                                            تومان
                                        </span>
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                {{ number_format((int) ($variant->stock ?? 0)) }}
                            </td>

                            <td>

                                @if($variant->is_active ?? true)

                                    <span class="admin-badge admin-badge-success">
                                            فعال
                                        </span>

                                @else

                                    <span class="admin-badge admin-badge-neutral">
                                            غیرفعال
                                        </span>

                                @endif

                            </td>

                            <td>

                                <div class="flex items-center justify-end gap-2">

                                    <a
                                        href="{{ route('admin.product-variants.edit', $variant) }}"
                                        class="admin-btn admin-btn-secondary px-3"
                                    >
                                        ویرایش
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    @else

        <div class="admin-empty">

            <div class="admin-empty-icon">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-6 w-6"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15"
                    />
                </svg>

            </div>

            <h4 class="text-sm font-semibold text-[var(--admin-text)]">
                هنوز تنوعی برای این محصول ثبت نشده است.
            </h4>

            <p class="mt-2 text-xs text-[var(--admin-muted)]">
                برای این محصول اولین تنوع را ایجاد کنید.
            </p>

            <a
                href="{{ route('admin.product-variants.create', ['product_id' => $product->id]) }}"
                class="admin-btn admin-btn-primary mt-5"
            >
                افزودن تنوع
            </a>

        </div>

    @endif

</div>
