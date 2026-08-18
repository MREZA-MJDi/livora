@extends('layouts.app')

@section('title', 'تماس با ما | LIVORA')

@section('description', 'راه‌های ارتباط با LIVORA')

@section('content')

    <section class="border-b border-[var(--livora-border)]">
        <x-layout.container>

            <div class="py-10 sm:py-14">

                <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    CONTACT LIVORA
                </p>

                <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)] sm:text-4xl">
                    تماس با ما
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-7 text-[var(--livora-stone)]">
                    برای مشاوره خرید، پیگیری سفارش یا هر سوال دیگری با ما در ارتباط باشید.
                </p>

            </div>

        </x-layout.container>
    </section>


    <section id="contact">
        <x-layout.container>

            <div class="grid gap-8 py-12 lg:grid-cols-2 lg:py-16">

                <div class="space-y-5">

                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">
                        <p class="text-xs text-[var(--livora-stone)]">
                            تلفن
                        </p>

                        <a
                            href="tel:+980000000000"
                            class="mt-2 block text-lg font-semibold text-[var(--livora-ink)]"
                        >
                            +98 00 000 0000
                        </a>
                    </div>

                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">
                        <p class="text-xs text-[var(--livora-stone)]">
                            ایمیل
                        </p>

                        <a
                            href="mailto:hello@livora.ir"
                            class="mt-2 block text-lg font-semibold text-[var(--livora-ink)]"
                        >
                            hello@livora.ir
                        </a>
                    </div>

                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">
                        <p class="text-xs text-[var(--livora-stone)]">
                            آدرس
                        </p>

                        <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">
                            تهران، ایران
                        </p>
                    </div>

                </div>


                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                    <p class="text-xs uppercase tracking-[0.15em] text-[var(--livora-accent)]">
                        SAY HELLO
                    </p>

                    <h2 class="mt-3 text-2xl font-semibold">
                        خوشحال می‌شویم صدایتان را بشنویم.
                    </h2>

                    <p class="mt-4 text-sm leading-7 text-[var(--livora-stone)]">
                        برای شروع، می‌توانید از طریق تلفن یا ایمیل با تیم LIVORA در ارتباط باشید.
                    </p>

                    <div class="mt-8">
                        <a
                            href="mailto:hello@livora.ir"
                            class="inline-flex rounded-xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition-colors hover:bg-[var(--livora-accent)]"
                        >
                            ارسال ایمیل
                        </a>
                    </div>

                </div>

            </div>

        </x-layout.container>
    </section>

@endsection
