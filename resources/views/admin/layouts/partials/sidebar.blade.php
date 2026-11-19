@php
    $adminUser = auth()->user();

    $navigationGroups = [
        [
            'title' => 'فروش و مشتریان',
            'description' => 'پیگیری سفارش‌ها و ارتباط با خریداران',
            'items' => [
                ['label' => 'سفارش‌ها', 'description' => 'مشاهده سفارش‌های جدید و پیگیری وضعیت آن‌ها', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*'],
                ['label' => 'مشتریان', 'description' => 'اطلاعات و سوابق خرید مشتریان', 'route' => 'admin.customers.index', 'active' => 'admin.customers.*'],
                ['label' => 'پیام‌های دریافتی', 'description' => 'خواندن و پاسخ دادن به پیام‌های تماس با ما', 'route' => 'admin.contact-messages.index', 'active' => 'admin.contact-messages.*'],
                ['label' => 'پیش‌نمایش فرایند خرید', 'description' => 'بررسی مسیر خریدی که مشتری در سایت می‌بیند', 'route' => 'admin.checkout.preview', 'active' => 'admin.checkout.preview'],
            ],
        ],
        [
            'title' => 'محصولات و دسته‌بندی‌ها',
            'description' => 'مدیریت کالاهایی که برای فروش عرضه می‌کنید',
            'items' => [
                ['label' => 'محصولات', 'description' => 'ثبت کالا، قیمت، تصویر و مشخصات', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
                ['label' => 'دسته‌بندی‌ها', 'description' => 'گروه‌بندی محصولات برای پیدا کردن آسان‌تر', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
            ],
        ],
        [
            'title' => 'محتوای سایت',
            'description' => 'چیزهایی که بازدیدکننده در صفحات سایت می‌بیند',
            'items' => [
                ['label' => 'بخش‌های صفحه اصلی', 'description' => 'مدیریت اسلایدر و بخش‌های صفحه اول', 'route' => 'admin.home-sections.index', 'active' => 'admin.home-sections.*'],
                ['label' => 'صفحات سایت', 'description' => 'ویرایش درباره ما، قوانین و صفحه‌های متنی', 'route' => 'admin.pages.index', 'active' => 'admin.pages.*'],
                ['label' => 'وبلاگ و مقاله‌ها', 'description' => 'نوشتن، ویرایش و انتشار مقاله‌ها', 'route' => 'admin.blog.index', 'active' => 'admin.blog.*'],
                ['label' => 'بنر صفحه فروشگاه', 'description' => 'تغییر تصویر و متن بنر بالای فروشگاه', 'route' => 'admin.shop-banner.edit', 'active' => 'admin.shop-banner.*'],
                ['label' => 'رسانه و تصاویر', 'description' => 'مدیریت فایل‌ها و انتخاب تصاویر سایت', 'route' => 'admin.media.index', 'active' => 'admin.media.*'],
            ],
        ],
        [
            'title' => 'تنظیمات فروشگاه و دیده‌شدن در گوگل',
            'description' => 'مشخصات کسب‌وکار، نمادها، فوتر و تنظیمات جستجو',
            'items' => [
                ['label' => 'اطلاعات و هویت فروشگاه', 'description' => 'نام برند، لوگو، راه‌های تماس، نمادها و فوتر', 'route' => 'admin.contact-settings.edit', 'active' => 'admin.contact-settings.*'],
                ['label' => 'تنظیمات کلی سئو', 'description' => 'عنوان و توضیحات پیش‌فرض و تنظیم ایندکس سایت', 'route' => 'admin.seo.edit', 'active' => 'admin.seo.edit'],
                ['label' => 'عنوان صفحات در گوگل', 'description' => 'تنظیم عنوان و توضیحات صفحات اصلی', 'route' => 'admin.seo.pages', 'active' => 'admin.seo.pages'],
                ['label' => 'بررسی سلامت سئو', 'description' => 'پیدا کردن مشکلات فنی و محتوایی سئو', 'route' => 'admin.seo.audit', 'active' => 'admin.seo.audit'],
            ],
        ],
    ];
@endphp

<aside
    id="admin-sidebar"
    data-admin-sidebar
    aria-label="منوی مدیریت فروشگاه"
    class="admin-sidebar fixed inset-y-0 right-0 z-50 flex w-[260px] -translate-x-0 flex-col border-l border-[var(--admin-border)] bg-[var(--admin-white)] transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
>
    <div class="flex h-[76px] shrink-0 items-center justify-between border-b border-[var(--admin-border)] px-5">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-text)] text-xs font-bold text-white">SG</span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-bold text-[var(--admin-text)]">سیلا گالری</span>
                <span class="mt-1 block text-[10px] text-[var(--admin-muted)]">مدیریت فروشگاه اینترنتی</span>
            </span>
        </a>
        <button type="button" @click="sidebarOpen = false" aria-label="بستن منوی مدیریت" class="flex h-9 w-9 items-center justify-center rounded-lg text-[var(--admin-muted)] hover:bg-[var(--admin-surface)] lg:hidden">✕</button>
    </div>

    <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-4" aria-label="بخش‌های مدیریت">
        <a
            href="{{ route('admin.dashboard') }}"
            @class([
                'mb-4 flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition',
                'bg-[var(--admin-text)] text-white' => request()->routeIs('admin.dashboard'),
                'text-[var(--admin-text)] hover:bg-[var(--admin-surface)]' => !request()->routeIs('admin.dashboard'),
            ])
        >
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-black/5 text-base" aria-hidden="true">⌂</span>
            <span class="flex-1">پیشخوان فروشگاه</span>
            @if(request()->routeIs('admin.dashboard'))
                <span class="h-2 w-2 rounded-full bg-[var(--admin-accent)]"></span>
            @endif
        </a>

        @foreach($navigationGroups as $group)
            @php
                $groupIsActive = collect($group['items'])->contains(
                    fn (array $item): bool => request()->routeIs($item['active'])
                );
            @endphp
            <details class="mb-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)]" @if($groupIsActive) open @endif>
                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl px-3 py-3 hover:bg-[var(--admin-surface)]">
                    <span class="min-w-0 flex-1">
                        <span class="block text-xs font-bold text-[var(--admin-text)]">{{ $group['title'] }}</span>
                        <span class="mt-1 block text-[10px] leading-5 text-[var(--admin-muted)]">{{ $group['description'] }}</span>
                    </span>
                    <span class="text-xs text-[var(--admin-muted)]" aria-hidden="true">⌄</span>
                </summary>
                <div class="space-y-1 px-2 pb-2">
                    @foreach($group['items'] as $item)
                        @php($itemIsActive = request()->routeIs($item['active']))
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'block rounded-lg px-3 py-2.5 transition',
                                'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => $itemIsActive,
                                'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !$itemIsActive,
                            ])
                            @if($itemIsActive) aria-current="page" @endif
                        >
                            <span class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold">{{ $item['label'] }}</span>
                                @if($itemIsActive)
                                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>
                                @endif
                            </span>
                            <span class="mt-1 block text-[10px] leading-5 text-[var(--admin-muted)]">{{ $item['description'] }}</span>
                        </a>
                    @endforeach
                </div>
            </details>
        @endforeach

        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="mt-4 flex items-center justify-between rounded-xl border border-dashed border-[var(--admin-border-dark)] px-3 py-3 text-xs font-semibold text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)]">
            <span>مشاهده سایت برای مشتریان</span>
            <span aria-hidden="true">↗</span>
        </a>
    </nav>

    <div class="shrink-0 border-t border-[var(--admin-border)] p-3">
        <div class="flex items-center gap-3 rounded-xl bg-[var(--admin-surface)] p-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[var(--admin-text)] text-xs font-bold text-white">
                {{ mb_substr($adminUser?->name ?: 'م', 0, 1) }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-xs font-semibold text-[var(--admin-text)]">{{ $adminUser?->name ?: 'مدیر فروشگاه' }}</span>
                <span class="mt-1 block text-[10px] text-[var(--admin-muted)]">حساب مدیر</span>
            </span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" aria-label="خروج از پنل" class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--admin-muted)] hover:bg-white hover:text-[var(--admin-danger)]">↪</button>
            </form>
        </div>
    </div>
</aside>
