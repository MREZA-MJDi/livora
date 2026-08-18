<div class="admin-card p-6">

    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

        <div>
            <h3 class="text-base font-bold text-[var(--admin-text)]">
                تصاویر محصول
            </h3>

            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                تصاویر این محصول را مدیریت کنید.
            </p>
        </div>

        <a
            href="{{ route('admin.product-images.create', ['product_id' => $product->id]) }}"
            class="admin-btn admin-btn-secondary"
        >
            افزودن تصویر
        </a>

    </div>


    @if($product->images->count())

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

            @foreach($product->images as $image)

                <div class="group overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)]">

                    <div class="relative aspect-square overflow-hidden">

                        <img
                            src="{{ $image->url }}"
                            alt="{{ $image->alt ?: $product->name }}"
                            class="admin-image transition duration-300 group-hover:scale-105"
                        >

                        @if($image->is_primary)

                            <span class="absolute right-2 top-2 admin-badge admin-badge-success">
                                اصلی
                            </span>

                        @endif

                    </div>


                    <div class="space-y-2 p-3">

                        <p class="truncate text-xs text-[var(--admin-text-soft)]">
                            {{ $image->alt ?: 'بدون alt' }}
                        </p>

                        <div class="flex items-center justify-between">

                            <span class="text-[10px] text-[var(--admin-muted)]">
                                ترتیب: {{ $image->sort_order }}
                            </span>

                            <a
                                href="{{ route('admin.product-images.edit', $image) }}"
                                class="text-xs font-semibold text-[var(--admin-accent)] transition hover:text-[var(--admin-accent-hover)]"
                            >
                                ویرایش
                            </a>

                        </div>

                    </div>

                </div>

            @endforeach

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
                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 0 3.182 0l3.068-3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z"
                    />
                </svg>

            </div>

            <h4 class="text-sm font-semibold text-[var(--admin-text)]">
                هنوز تصویری ثبت نشده است.
            </h4>

            <p class="mt-2 text-xs text-[var(--admin-muted)]">
                برای این محصول اولین تصویر را اضافه کنید.
            </p>

            <a
                href="{{ route('admin.product-images.create', ['product_id' => $product->id]) }}"
                class="admin-btn admin-btn-primary mt-5"
            >
                افزودن تصویر
            </a>

        </div>

    @endif

</div>
