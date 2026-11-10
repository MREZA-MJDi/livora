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

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.product-variants.create', ['product_id' => $product->id, 'type' => 'رنگ', 'name' => 'رنگ']) }}"
               class="admin-btn admin-btn-secondary">افزودن رنگ</a>
            <a href="{{ route('admin.product-variants.create', ['product_id' => $product->id, 'type' => 'جنس', 'name' => 'جنس']) }}"
               class="admin-btn admin-btn-secondary">افزودن جنس</a>
            <a href="{{ route('admin.product-variants.create', ['product_id' => $product->id, 'type' => 'اندازه', 'name' => 'اندازه']) }}"
               class="admin-btn admin-btn-primary">افزودن اندازه / مدل</a>
        </div>

    </div>


    @if($product->allVariants->count())

        <div class="admin-table-wrap">

            <div class="overflow-x-auto">

                <table class="admin-table">

                    <thead>
                    <tr>
                        <th>نوع / گزینه</th>
                        <th>SKU</th>
                        <th>قیمت</th>
                        <th>موجودی</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($product->allVariants as $variant)

                        <tr>

                            <td>
                                @if($variant->images->isNotEmpty())
                                    <img src="{{ $variant->images->first()->thumbnail_url }}"
                                         alt="{{ $variant->images->first()->alt ?: $variant->value }}"
                                         class="mb-2 h-14 w-12 rounded-lg border border-[var(--admin-border)] object-cover"
                                         loading="lazy">
                                @endif
                                <div class="text-[11px] font-medium text-[var(--admin-muted)]">{{ $variant->type }}</div>
                                <div class="mt-1 font-semibold text-[var(--admin-text)]">
                                    {{ $variant->name ?? 'تنوع محصول' }}
                                </div>

                                <div class="mt-1 text-xs text-[var(--admin-muted)]">
                                    {{ $variant->value }}
                                </div>

                                @if($variant->color_hex)
                                    <span
                                        class="mt-2 inline-block h-4 w-4 rounded-full border border-black/10 align-middle"
                                        style="background-color: {{ $variant->color_hex }};"
                                        title="{{ $variant->color_hex }}"
                                    ></span>
                                @endif
                            </td>

                            <td>
                                    <span class="font-mono text-xs text-[var(--admin-text-soft)]">
                                        {{ $variant->sku ?? '—' }}
                                    </span>
                            </td>

                            <td>
                                <span class="font-semibold text-[var(--admin-text)]">
                                    {{ number_format(max(0, (float) $product->price + (float) $variant->price_adjustment)) }}
                                </span>
                                <span class="text-xs text-[var(--admin-muted)]">تومان · قیمت این گزینه به‌تنهایی</span>
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
                                    <div x-data="{ confirmOpen: false }">
                                        <button type="button" @click="confirmOpen = true" class="admin-btn admin-btn-danger px-3">حذف</button>
                                        <div x-show="confirmOpen" x-cloak x-transition.opacity
                                             class="fixed inset-0 z-[470] flex items-center justify-center bg-black/55 p-4"
                                             @click.self="confirmOpen = false" @keydown.escape.window="confirmOpen = false">
                                            <section class="w-full max-w-sm rounded-3xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-6 shadow-2xl"
                                                     role="dialog" aria-modal="true">
                                                <h3 class="text-base font-bold text-[var(--admin-text)]">حذف این تنوع؟</h3>
                                                <p class="mt-2 text-sm leading-7 text-[var(--admin-muted)]">
                                                    گزینه «{{ $variant->value }}» حذف می‌شود. سفارش‌های ثبت‌شده قبلی تغییر نمی‌کنند.
                                                </p>
                                                <div class="mt-5 flex gap-2">
                                                    <button type="button" @click="confirmOpen = false" class="admin-btn admin-btn-secondary flex-1">انصراف</button>
                                                    <form action="{{ route('admin.product-variants.destroy', $variant) }}" method="POST" class="flex-1">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="admin-btn admin-btn-danger w-full">تأیید حذف</button>
                                                    </form>
                                                </div>
                                            </section>
                                        </div>
                                    </div>

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
