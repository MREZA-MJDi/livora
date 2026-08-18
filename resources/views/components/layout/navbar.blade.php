<nav
    class="sticky top-0 z-50 w-full border-b border-[var(--livora-border)] bg-[var(--livora-cream)]/95 backdrop-blur-md"
>

    <x-layout.container>

        <div class="flex h-20 items-center justify-between">

            {{-- =========================================
                BRAND
            ========================================== --}}
            <a
                href="{{ route('home') }}"
                class="group flex items-center gap-2"
            >
                <span
                    class="text-2xl font-semibold tracking-[0.18em] text-[var(--livora-ink)] transition-opacity duration-300 group-hover:opacity-70"
                >
                    LIVORA
                </span>
            </a>


            {{-- =========================================
                DESKTOP NAVIGATION
            ========================================== --}}
            <div class="hidden items-center gap-8 lg:flex">

                {{-- Home --}}
                <a
                    href="{{ route('home') }}"
                    @class([
                        'text-sm font-medium transition-colors duration-300',
                        'text-[var(--livora-accent)]' => request()->routeIs('home'),
                        'text-[var(--livora-ink)] hover:text-[var(--livora-accent)]' => !request()->routeIs('home'),
                    ])
                >
                    خانه
                </a>


                {{-- Shop --}}
                <a
                    href="{{ route('shop.index') }}"
                    @class([
                        'text-sm font-medium transition-colors duration-300',
                        'text-[var(--livora-accent)]' => request()->routeIs('shop.index', 'product.show'),
                        'text-[var(--livora-ink)] hover:text-[var(--livora-accent)]' => !request()->routeIs('shop.index', 'product.show'),
                    ])
                >
                    فروشگاه
                </a>


                {{-- Categories --}}
                <a
                    href="{{ route('categories.index') }}"
                    @class([
                        'text-sm font-medium transition-colors duration-300',
                        'text-[var(--livora-accent)]' => request()->routeIs('categories.*'),
                        'text-[var(--livora-ink)] hover:text-[var(--livora-accent)]' => !request()->routeIs('categories.*'),
                    ])
                >
                    دسته‌بندی‌ها
                </a>


                {{-- About --}}
                <a
                    href="{{ route('about') }}"
                    @class([
                        'text-sm font-medium transition-colors duration-300',
                        'text-[var(--livora-accent)]' => request()->routeIs('about'),
                        'text-[var(--livora-ink)] hover:text-[var(--livora-accent)]' => !request()->routeIs('about'),
                    ])
                >
                    درباره ما
                </a>


                {{-- Contact --}}
                <a
                    href="{{ route('contact') }}"
                    @class([
                        'text-sm font-medium transition-colors duration-300',
                        'text-[var(--livora-accent)]' => request()->routeIs('contact'),
                        'text-[var(--livora-ink)] hover:text-[var(--livora-accent)]' => !request()->routeIs('contact'),
                    ])
                >
                    تماس
                </a>

            </div>


            {{-- =========================================
                ACTIONS
            ========================================== --}}
            <div class="flex items-center gap-2">

                {{-- Search --}}
                <button
                    type="button"
                    aria-label="جستجو"
                    :aria-expanded="searchOpen.toString()"
                    @click="
                        mobileOpen = false;
                        searchOpen = true;
                        $nextTick(() => {
                            $refs.searchInput?.focus();
                        });
                    "
                    class="flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition-all duration-300 hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                        />
                    </svg>
                </button>


                {{-- Wishlist --}}
                @auth

                    @if(auth()->user()->isCustomer())

                        <a
                            href="{{ route('account.wishlist.index') }}"
                            aria-label="علاقه‌مندی‌ها"
                            @class([
                                'hidden h-10 w-10 items-center justify-center rounded-full transition-all duration-300 sm:flex',
                                'bg-[var(--livora-white)] text-[var(--livora-accent)]' => request()->routeIs('account.wishlist.*'),
                                'text-[var(--livora-ink)] hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]' => !request()->routeIs('account.wishlist.*'),
                            ])
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                                />
                            </svg>
                        </a>

                    @else

                        <a
                            href="{{ route('login') }}"
                            aria-label="علاقه‌مندی‌ها"
                            class="hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition-all duration-300 hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)] sm:flex"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                                />
                            </svg>
                        </a>

                    @endif

                @else

                    <a
                        href="{{ route('login') }}"
                        aria-label="علاقه‌مندی‌ها"
                        class="hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition-all duration-300 hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)] sm:flex"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                            />
                        </svg>
                    </a>

                @endauth


                {{-- Cart --}}
                @php
                    $cartCount = 0;

                    if (auth()->check() && auth()->user()->isCustomer()) {
                        $cart = auth()->user()->activeCart;

                        if ($cart) {
                            $cartCount = $cart->itemCount();
                        }
                    }
                @endphp

                <a
                    href="{{ route('cart.index') }}"
                    aria-label="سبد خرید"
                    class="relative flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition-all duration-300 hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.836L5.75 7.5h13.5l1.125-4.5M5.75 7.5l1.5 6h9.5l1.5-6M8.25 19.5a.75.75 0 1 1-1.5 0Zm9.75 0a.75.75 0 1 1-1.5 0Z"
                        />
                    </svg>

                    @if($cartCount > 0)

                        <span
                            class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[var(--livora-accent)] px-1 text-[10px] font-semibold text-white"
                        >
                            {{ $cartCount }}
                        </span>

                    @endif
                </a>


                {{-- Profile --}}
                @auth

                    @if(auth()->user()->isAdmin())

                        <a
                            href="{{ route('admin.dashboard') }}"
                            aria-label="پنل مدیریت"
                            class="hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition-all duration-300 hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)] sm:flex"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1-7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0"
                                />
                            </svg>
                        </a>

                    @else

                        <a
                            href="{{ route('account.index') }}"
                            aria-label="حساب کاربری"
                            @class([
                                'hidden h-10 w-10 items-center justify-center rounded-full transition-all duration-300 sm:flex',
                                'bg-[var(--livora-white)] text-[var(--livora-accent)]' => request()->routeIs('account.*'),
                                'text-[var(--livora-ink)] hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]' => !request()->routeIs('account.*'),
                            ])
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1-7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0"
                                />
                            </svg>
                        </a>

                    @endif

                @else

                    <a
                        href="{{ route('login') }}"
                        aria-label="ورود"
                        class="hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition-all duration-300 hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)] sm:flex"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 1 1-7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0"
                            />
                        </svg>
                    </a>

                @endauth


                {{-- Mobile Menu Button --}}
                <button
                    type="button"
                    aria-label="منوی موبایل"
                    :aria-expanded="mobileOpen.toString()"
                    @click="
                        searchOpen = false;
                        mobileOpen = !mobileOpen;
                    "
                    class="flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition-all duration-300 hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)] lg:hidden"
                >
                    <svg
                        x-show="!mobileOpen"
                        x-cloak
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"
                        />
                    </svg>

                    <svg
                        x-show="mobileOpen"
                        x-cloak
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18 18 6M6 6l12 12"
                        />
                    </svg>
                </button>

            </div>

        </div>

    </x-layout.container>

</nav>
