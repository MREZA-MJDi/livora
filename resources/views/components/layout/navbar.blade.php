<nav
    x-data
    class="sticky top-0 z-50 hidden w-full border-b border-[var(--livora-border)] bg-[var(--livora-cream)]/90 backdrop-blur-xl lg:block"
>
    <x-layout.container>

        <div class="flex h-[76px] items-center justify-between gap-6">

            {{-- =========================================================
                BRAND
            ========================================================== --}}
            <div class="flex items-center">

                <a
                    href="{{ route('home') }}"
                    class="group inline-flex items-center"
                    aria-label="SilaGallery"
                >
                    <span class="silagallery-wordmark" aria-hidden="true">
    <span class="silagallery-wordmark__main">Sila</span><span class="silagallery-wordmark__sub">Gallery</span>
</span>
                </a>

            </div>


            {{-- =========================================================
                DESKTOP NAVIGATION
            ========================================================== --}}
            <div class="hidden items-center gap-9 lg:flex">

                <a
                    href="{{ route('home') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-ink)]' => request()->routeIs('home'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('home'),
                    ])
                >
                    خانه

                    @if(request()->routeIs('home'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('shop.index') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-ink)]' => request()->routeIs('shop.index', 'product.show'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('shop.index', 'product.show'),
                    ])
                >
                    فروشگاه

                    @if(request()->routeIs('shop.index', 'product.show'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('categories.index') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-ink)]' => request()->routeIs('categories.*'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('categories.*'),
                    ])
                >
                    دسته‌بندی‌ها

                    @if(request()->routeIs('categories.*'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('about') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-ink)]' => request()->routeIs('about'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('about'),
                    ])
                >
                    درباره ما

                    @if(request()->routeIs('about'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('contact') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-ink)]' => request()->routeIs('contact'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('contact'),
                    ])
                >
                    تماس

                    @if(request()->routeIs('contact'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

            </div>


            {{-- =========================================================
                RIGHT ACTIONS
            ========================================================== --}}
            <div class="flex items-center gap-1">

            </div>

        </div>

    </x-layout.container>

</nav>
