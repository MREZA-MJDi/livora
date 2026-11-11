@extends('admin.layouts.app')

@section('title', 'تصاویر محصولات')
@section('page_title', 'تصاویر محصولات')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>
            <span class="text-xs font-medium text-[var(--admin-accent)]">
                CATALOG / PRODUCT IMAGES
            </span>

            <h2 class="admin-title mt-2">
                تصاویر محصولات
            </h2>

            <p class="admin-subtitle mt-2">
                مدیریت تصاویر محصولات و تصویر اصلی هر محصول
            </p>
        </div>

        <a
            href="{{ route('admin.product-images.create') }}"
            class="admin-btn admin-btn-primary"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke-width="1.5"
                 stroke="currentColor"
                 class="h-5 w-5">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 5.25v13.5M5.25 12h13.5"
                />
            </svg>

            افزودن تصویر
        </a>

    </div>


    {{-- Filters --}}
    <div class="admin-card mb-6 p-5">

        <form
            action="{{ route('admin.product-images.index') }}"
            method="GET"
            class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_auto_auto]"
        >

            <div>
                <label for="product_id" class="admin-label">
                    فیلتر بر اساس محصول
                </label>

                <div x-data="adminCatalogueLookup(@js(route('admin.catalogue.lookups.products')), @js($selectedProduct ? [['id' => $selectedProduct->id, 'label' => $selectedProduct->name, 'meta' => $selectedProduct->sku]] : []), false)" class="space-y-2">
                    <input type="hidden" name="product_id" :value="selected[0]?.id || ''">
                    <input type="search" class="admin-input" x-model="term" x-on:input.debounce.300ms="search()" minlength="2" maxlength="80" autocomplete="off" placeholder="جستجوی نام محصول یا SKU">
                    <div x-show="results.length" x-cloak class="max-h-56 space-y-1 overflow-y-auto rounded-xl border border-[var(--admin-border)] bg-white p-2" role="listbox" aria-label="نتایج محصولات">
                        <template x-for="item in results" :key="item.id">
                            <button type="button" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-right text-sm hover:bg-[var(--admin-surface)]" @click="choose(item)" role="option"><span x-text="item.label"></span><small class="text-[var(--admin-muted)]" dir="ltr" x-text="item.meta || ''"></small></button>
                        </template>
                    </div>
                    <template x-for="item in selected" :key="item.id">
                        <div class="flex items-center justify-between rounded-lg border border-[var(--admin-border)] bg-[var(--admin-surface)] px-3 py-2 text-sm"><span x-text="item.label"></span><button type="button" class="text-red-600" @click="remove(item.id)" aria-label="حذف فیلتر محصول">×</button></div>
                    </template>
                    <p class="admin-help" role="status" aria-live="polite" x-text="status"></p>
                </div>
            </div>

            <div class="flex items-end">
                <button
                    type="submit"
                    class="admin-btn admin-btn-secondary w-full md:w-auto"
                >
                    اعمال فیلتر
                </button>
            </div>

            @if(request()->filled('product_id'))

                <div class="flex items-end">
                    <a
                        href="{{ route('admin.product-images.index') }}"
                        class="admin-btn admin-btn-ghost w-full md:w-auto"
                    >
                        حذف فیلتر
                    </a>
                </div>

            @endif

        </form>

    </div>


    {{-- Images Table --}}
    <div class="admin-table-wrap">

        <div class="overflow-x-auto">

            <table class="admin-table">

                <thead>
                <tr>
                    <th>#</th>
                    <th>تصویر</th>
                    <th>محصول</th>
                    <th>Alt</th>
                    <th>ترتیب</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($images as $image)

                    <tr>

                        <td>
                            {{ $images->firstItem() + $loop->index }}
                        </td>


                        <td>

                            <div class="admin-image-thumb">

                                @if(filled($image->url))
                                    <img src="{{ $image->url }}" alt="{{ $image->alt ?: $image->product?->name }}" class="admin-image" loading="lazy">
                                @else
                                    <div class="flex aspect-square items-center justify-center rounded-xl bg-[var(--admin-surface)] px-2 text-center text-xs text-[var(--admin-muted)]">فایل تصویر در دسترس نیست</div>
                                @endif

                            </div>

                        </td>


                        <td>

                            @if($image->product)

                                <a
                                    href="{{ route('admin.products.show', $image->product) }}"
                                    class="font-semibold text-[var(--admin-text)] transition hover:text-[var(--admin-accent)]"
                                >
                                    {{ $image->product->name }}
                                </a>

                            @else

                                <span class="text-[var(--admin-muted)]">
                                        محصول حذف شده
                                    </span>

                            @endif

                        </td>


                        <td>
                                <span class="block max-w-[220px] truncate text-xs text-[var(--admin-text-soft)]">
                                    {{ $image->alt ?: '—' }}
                                </span>
                        </td>


                        <td>
                            {{ $image->sort_order }}
                        </td>


                        <td>

                            @if($image->is_primary)

                                <span class="admin-badge admin-badge-success">
                                        تصویر اصلی
                                    </span>

                            @else

                                <span class="admin-badge admin-badge-neutral">
                                        معمولی
                                    </span>

                            @endif

                        </td>


                        <td>
                                <span class="whitespace-nowrap text-xs text-[var(--admin-muted)]">
                                    {{ $image->created_at?->format('Y/m/d') ?? '—' }}
                                </span>
                        </td>


                        <td>

                            <div class="flex items-center justify-end gap-2">

                                <a
                                    href="{{ route('admin.product-images.show', $image) }}"
                                    class="admin-btn admin-btn-ghost px-3"
                                >
                                    مشاهده
                                </a>

                                <a
                                    href="{{ route('admin.product-images.edit', $image) }}"
                                    class="admin-btn admin-btn-secondary px-3"
                                >
                                    ویرایش
                                </a>

                                <form
                                    action="{{ route('admin.product-images.destroy', $image) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از حذف این تصویر مطمئن هستید؟');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="admin-btn admin-btn-danger px-3"
                                    >
                                        حذف
                                    </button>
                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8">

                            <div class="admin-empty">

                                <div class="admin-empty-icon">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke-width="1.5"
                                         stroke="currentColor"
                                         class="h-6 w-6">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z"
                                        />
                                    </svg>

                                </div>

                                <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                    هنوز تصویری ثبت نشده است.
                                </h3>

                                <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                    برای شروع، یک تصویر به محصول اضافه کنید.
                                </p>

                                <a
                                    href="{{ route('admin.product-images.create') }}"
                                    class="admin-btn admin-btn-primary mt-5"
                                >
                                    افزودن تصویر
                                </a>

                            </div>

                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    @if($images->hasPages())

        <div class="mt-6">
            {{ $images->links() }}
        </div>

    @endif

@endsection
