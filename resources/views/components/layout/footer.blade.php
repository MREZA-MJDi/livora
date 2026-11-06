<footer class="relative overflow-hidden border-t border-white/10 bg-[var(--sg-ink)] text-[var(--sg-cream)]">
    <x-layout.container>
        {{-- Brand campaign --}}
        <section class="relative isolate overflow-hidden border-b border-white/10 py-12 sm:py-16 lg:py-20">
            <div class="pointer-events-none absolute -left-20 -top-24 -z-10 h-72 w-72 rounded-full bg-[var(--sg-accent)]/10 blur-3xl"></div>
            <div class="grid items-end gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:gap-12">
                <div class="max-w-3xl">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-[var(--sg-accent)]">SILLAGALLERY · THE ART OF LIVING</p>
                    <h2 class="mt-5 max-w-2xl text-3xl font-semibold leading-[1.5] tracking-tight text-white sm:text-4xl lg:text-5xl">
                        خانه، جایی برای روایت سلیقه شماست.
                    </h2>
                    <p class="mt-5 max-w-2xl text-sm leading-8 text-white/55 sm:text-base">
                        از میان انتخاب‌های SillaGallery، جزئیاتی را پیدا کنید که با سبک زندگی و حال‌وهوای خانه شما هماهنگ‌اند؛
                        برای الهام گرفتن، انتخاب آگاهانه و ساختن فضایی که به شما تعلق دارد.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3 lg:pb-1">
                    <a href="{{ route('shop.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-full bg-[var(--sg-cream)] px-6 text-sm font-semibold text-[var(--sg-ink)] transition hover:bg-white">
                        کشف محصولات <span class="mr-3" aria-hidden="true">←</span>
                    </a>
                    <a href="{{ route('about') }}" class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/20 px-6 text-sm font-medium text-white transition hover:border-white/50 hover:bg-white/5">
                        داستان ما
                    </a>
                </div>
            </div>
        </section>

        {{-- Main navigation --}}
        <div class="grid gap-10 py-12 sm:grid-cols-2 sm:gap-12 lg:grid-cols-[1.35fr_0.8fr_0.8fr_1fr] lg:py-16">
            <div class="max-w-sm">
                <a href="{{ route('home') }}" aria-label="{{ $storeSettings?->brand_name ?: config('app.name') }}" class="inline-flex items-center">
                    <span class="text-2xl font-semibold tracking-[0.2em] text-white transition hover:text-[var(--sg-cream)]">
                        {{ $storeSettings?->brand_name ?: config('app.name') }}
                    </span>
                </a>
                <p class="mt-5 text-sm leading-8 text-white/55">
                    انتخاب‌هایی برای خانه‌ای با هویت شخصی؛ از کشف محصولات تا الهام گرفتن از ایده‌ها و جزئیات دکوراسیون.
                </p>
                @if(filled($storeSettings?->warranty_text))
                    <p class="mt-4 border-r-2 border-[var(--sg-accent)] pr-3 text-xs leading-7 text-white/45">{{ $storeSettings->warranty_text }}</p>
                @endif
                <a href="{{ route('blog.index') }}" class="mt-6 inline-flex items-center gap-2 text-xs font-medium text-[var(--sg-cream)] transition hover:text-white">
                    ایده‌ها و مطالب تازه <span aria-hidden="true">←</span>
                </a>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/40">فروشگاه</p>
                <nav aria-label="لینک‌های فروشگاه" class="mt-5 flex flex-col gap-3.5">
                    <a href="{{ route('shop.index') }}" class="text-sm text-white/60 transition hover:text-white">همه محصولات</a>
                    <a href="{{ route('categories.index') }}" class="text-sm text-white/60 transition hover:text-white">دسته‌بندی‌ها</a>
                    <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="text-sm text-white/60 transition hover:text-white">تازه‌ترین انتخاب‌ها</a>
                    <a href="{{ route('cart.index') }}" class="text-sm text-white/60 transition hover:text-white">سبد خرید</a>
                </nav>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/40">همراه شما</p>
                <nav aria-label="لینک‌های مشتریان" class="mt-5 flex flex-col gap-3.5">
                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('account.index') }}" class="text-sm text-white/60 transition hover:text-white">حساب کاربری</a>
                            <a href="{{ route('account.orders.index') }}" class="text-sm text-white/60 transition hover:text-white">پیگیری سفارش‌ها</a>
                            <a href="{{ route('account.wishlist.index') }}" class="text-sm text-white/60 transition hover:text-white">علاقه‌مندی‌ها</a>
                            <a href="{{ route('account.addresses.index') }}" class="text-sm text-white/60 transition hover:text-white">آدرس‌های من</a>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-sm text-white/60 transition hover:text-white">پنل مدیریت</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-white/60 transition hover:text-white">ورود به حساب</a>
                        <a href="{{ route('register') }}" class="text-sm text-white/60 transition hover:text-white">ساخت حساب کاربری</a>
                    @endauth
                    <a href="{{ route('about') }}" class="text-sm text-white/60 transition hover:text-white">درباره SillaGallery</a>
                    <a href="{{ route('contact') }}" class="text-sm text-white/60 transition hover:text-white">راهنمایی و تماس</a>
                </nav>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/40">در ارتباط بمانیم</p>
                <p class="mt-5 text-sm leading-7 text-white/50">برای پرسش درباره محصولات یا پیگیری درخواستتان از راه‌های ارتباطی زیر استفاده کنید.</p>
                <div class="mt-5 space-y-3">
                    @if(filled($storeSettings?->phone))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $storeSettings->phone) }}" class="block break-all text-sm text-white/70 transition hover:text-white">{{ $storeSettings->phone }}</a>
                    @endif
                    @if(filled($storeSettings?->email))
                        <a href="mailto:{{ $storeSettings->email }}" class="block break-all text-sm text-white/70 transition hover:text-white">{{ $storeSettings->email }}</a>
                    @endif
                    @if(filled($storeSettings?->address))
                        <p class="text-sm leading-7 text-white/45">{{ $storeSettings->address }}</p>
                    @endif
                    @if(filled($storeSettings?->hours))
                        <p class="text-xs leading-6 text-white/40">ساعات پاسخ‌گویی: {{ $storeSettings->hours }}</p>
                    @endif
                </div>
                <a href="{{ route('contact') }}" class="mt-6 inline-flex min-h-11 items-center rounded-full border border-white/20 px-5 text-xs font-medium text-white transition hover:border-white/50 hover:bg-white/5">
                    با ما گفتگو کنید <span class="mr-2" aria-hidden="true">←</span>
                </a>
            </div>
        </div>

        {{-- Editorial discovery strip --}}
        <div class="grid gap-4 border-y border-white/10 py-6 sm:grid-cols-3 sm:gap-6 sm:py-7">
            <a href="{{ route('categories.index') }}" class="group flex items-start gap-3">
                <span class="mt-0.5 text-lg text-[var(--sg-accent)]" aria-hidden="true">✳</span>
                <span><strong class="block text-sm font-medium text-white transition group-hover:text-[var(--sg-cream)]">انتخاب بر اساس سلیقه</strong><span class="mt-1 block text-xs leading-6 text-white/40">دسته‌بندی‌ها را ببینید و مسیر انتخابتان را پیدا کنید.</span></span>
            </a>
            <a href="{{ route('blog.index') }}" class="group flex items-start gap-3">
                <span class="mt-0.5 text-lg text-[var(--sg-accent)]" aria-hidden="true">✳</span>
                <span><strong class="block text-sm font-medium text-white transition group-hover:text-[var(--sg-cream)]">الهام برای فضای بهتر</strong><span class="mt-1 block text-xs leading-6 text-white/40">با مقاله‌ها و ایده‌های تازه، انتخاب‌هایتان را کامل کنید.</span></span>
            </a>
            <a href="{{ route('contact') }}" class="group flex items-start gap-3">
                <span class="mt-0.5 text-lg text-[var(--sg-accent)]" aria-hidden="true">✳</span>
                <span><strong class="block text-sm font-medium text-white transition group-hover:text-[var(--sg-cream)]">پیش از خرید سؤال دارید؟</strong><span class="mt-1 block text-xs leading-6 text-white/40">برای دریافت راهنمایی درباره محصولات با ما در تماس باشید.</span></span>
            </a>
        </div>

        {{-- Bottom bar --}}
        <div class="flex flex-col gap-5 py-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs leading-6 text-white/40">
                © {{ \App\Helpers\JalaliHelper::formatYear(now()) }} {{ $storeSettings?->brand_name ?: config('app.name') }}.
                تمامی حقوق محفوظ است.
            </p>
            <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                <a href="{{ route('about') }}" class="text-xs text-white/45 transition hover:text-white">درباره ما</a>
                <a href="{{ route('contact') }}" class="text-xs text-white/45 transition hover:text-white">تماس با ما</a>
                <a href="{{ route('terms') }}" class="text-xs text-white/45 transition hover:text-white">شرایط استفاده</a>
                @auth
                    @if(auth()->user()->isCustomer())
                        <a href="{{ route('account.index') }}" class="text-xs text-white/45 transition hover:text-white">حساب من</a>
                    @endif
                @endauth
                <x-layout.social-links :settings="$storeSettings" variant="footer" />
            </div>
        </div>
    </x-layout.container>
</footer>
