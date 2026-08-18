<footer class="border-t border-[var(--livora-border)] bg-[var(--livora-ink)] text-[var(--livora-cream)]">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">

        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">

            {{-- Brand --}}
            <div class="lg:col-span-1">
                <a
                    href="{{ url('/') }}"
                    class="inline-block text-2xl font-semibold tracking-[0.18em] text-[var(--livora-cream)]"
                >
                    LIVORA
                </a>

                <p class="mt-5 max-w-xs text-sm leading-7 text-[var(--livora-stone)]">
                    تجربه‌ای متفاوت از مبلمان و سبک زندگی؛
                    طراحی‌شده برای فضاهایی که قرار است ماندگار باشند.
                </p>
            </div>

            {{-- Shop --}}
            <div>
                <h3 class="text-sm font-semibold text-[var(--livora-cream)]">
                    فروشگاه
                </h3>

                <div class="mt-5 flex flex-col gap-3">
                    <a
                        href="{{ url('/shop') }}"
                        class="text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        همه محصولات
                    </a>

                    <a
                        href="#"
                        class="text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        مبلمان
                    </a>

                    <a
                        href="#"
                        class="text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        میز و صندلی
                    </a>

                    <a
                        href="#"
                        class="text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        دکوراسیون
                    </a>
                </div>
            </div>

            {{-- Customer --}}
            <div>
                <h3 class="text-sm font-semibold text-[var(--livora-cream)]">
                    خدمات مشتریان
                </h3>

                <div class="mt-5 flex flex-col gap-3">
                    <a
                        href="#"
                        class="text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        پیگیری سفارش
                    </a>

                    <a
                        href="#"
                        class="text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        شرایط ارسال
                    </a>

                    <a
                        href="#"
                        class="text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        بازگشت کالا
                    </a>

                    <a
                        href="#"
                        class="text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        سوالات متداول
                    </a>
                </div>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="text-sm font-semibold text-[var(--livora-cream)]">
                    ارتباط با ما
                </h3>

                <div class="mt-5 space-y-4">

                    <a
                        href="tel:+980000000000"
                        class="block text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        +98 00 000 0000
                    </a>

                    <a
                        href="mailto:hello@livora.ir"
                        class="block text-sm text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                    >
                        hello@livora.ir
                    </a>

                    <p class="text-sm leading-7 text-[var(--livora-stone)]">
                        تهران، ایران
                    </p>

                </div>
            </div>

        </div>

        {{-- Bottom --}}
        <div class="mt-12 flex flex-col gap-4 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-xs text-[var(--livora-stone)]">
                © {{ date('Y') }} LIVORA. تمامی حقوق محفوظ است.
            </p>

            <div class="flex items-center gap-5">
                <a
                    href="#"
                    aria-label="Instagram"
                    class="text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                >
                    Instagram
                </a>

                <a
                    href="#"
                    aria-label="Telegram"
                    class="text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-cream)]"
                >
                    Telegram
                </a>
            </div>

        </div>

    </div>
</footer>
