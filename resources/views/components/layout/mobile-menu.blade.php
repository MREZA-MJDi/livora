<div
    x-show="mobileOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="border-b border-[var(--livora-border)] bg-[var(--livora-cream)] lg:hidden"
>

    <x-layout.container>

        <div class="space-y-1 py-5">

            <a
                href="{{ route('home') }}"
                @click="mobileOpen = false"
                class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
            >
                خانه
            </a>

            <a
                href="{{ route('shop.index') }}"
                @click="mobileOpen = false"
                class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
            >
                فروشگاه
            </a>

            <a
                href="{{ route('categories.index') }}"
                @click="mobileOpen = false"
                class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
            >
                دسته‌بندی‌ها
            </a>

            <a
                href="{{ route('about') }}"
                @click="mobileOpen = false"
                class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
            >
                درباره ما
            </a>

            <a
                href="{{ route('contact') }}"
                @click="mobileOpen = false"
                class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
            >
                تماس
            </a>


            <div class="mt-3 border-t border-[var(--livora-border)] pt-3">

                @auth

                    @if(auth()->user()->isAdmin())

                        <a
                            href="{{ route('admin.dashboard') }}"
                            @click="mobileOpen = false"
                            class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
                        >
                            پنل مدیریت
                        </a>

                    @elseif(auth()->user()->isCustomer())

                        <a
                            href="{{ route('account.index') }}"
                            @click="mobileOpen = false"
                            class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
                        >
                            حساب کاربری
                        </a>

                        <a
                            href="{{ route('account.wishlist.index') }}"
                            @click="mobileOpen = false"
                            class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
                        >
                            علاقه‌مندی‌ها
                        </a>

                    @endif

                @else

                    <a
                        href="{{ route('login') }}"
                        @click="mobileOpen = false"
                        class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
                    >
                        ورود
                    </a>

                    <a
                        href="{{ route('register') }}"
                        @click="mobileOpen = false"
                        class="block rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
                    >
                        ثبت‌نام
                    </a>

                @endauth


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
                    @click="mobileOpen = false"
                    class="flex items-center justify-between rounded-xl px-4 py-3 text-sm text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
                >
                    <span>
                        سبد خرید
                    </span>

                    @if($cartCount > 0)

                        <span class="rounded-full bg-[var(--livora-accent)] px-2 py-0.5 text-[10px] font-semibold text-white">
                            {{ $cartCount }}
                        </span>

                    @endif
                </a>

            </div>

        </div>

    </x-layout.container>

</div>
