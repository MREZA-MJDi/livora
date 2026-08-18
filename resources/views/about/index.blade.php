@extends('layouts.app')

@section('title', 'درباره ما | LIVORA')

@section('description', 'درباره LIVORA و فلسفه طراحی آن')

@section('content')

    <section class="border-b border-[var(--livora-border)]">
        <x-layout.container>

            <div class="grid gap-10 py-16 lg:grid-cols-2 lg:items-center lg:py-24">

                <div>

                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                        ABOUT LIVORA
                    </p>

                    <h1 class="mt-4 text-4xl font-semibold tracking-tight text-[var(--livora-ink)] sm:text-5xl">
                        طراحی برای زندگی،
                        نه فقط برای فضا.
                    </h1>

                    <p class="mt-6 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                        LIVORA با تمرکز بر مبلمان، دکوراسیون و انتخاب‌های ماندگار،
                        تلاش می‌کند محصولاتی را گرد هم بیاورد که در کنار زیبایی،
                        بخشی از تجربه زندگی روزمره باشند.
                    </p>

                </div>

                <div class="rounded-3xl bg-[var(--livora-white)] p-8 sm:p-10">

                    <p class="text-sm leading-8 text-[var(--livora-stone)]">
                        نگاه ما ساده است: فرم خوب، متریال مناسب و انتخاب دقیق.
                        ما به فضاهایی فکر می‌کنیم که قرار است سال‌ها در آن‌ها
                        زندگی شود و هنوز حس خوبی ایجاد کنند.
                    </p>

                </div>

            </div>

        </x-layout.container>
    </section>


    <section id="about">
        <x-layout.container>

            <div class="grid gap-5 py-16 sm:grid-cols-3 lg:py-20">

                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">
                    <p class="text-xs uppercase tracking-[0.15em] text-[var(--livora-accent)]">
                        01
                    </p>

                    <h2 class="mt-4 text-lg font-semibold">
                        انتخاب دقیق
                    </h2>

                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">
                        هر محصول با توجه به فرم، کیفیت و هماهنگی آن با سبک زندگی انتخاب می‌شود.
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">
                    <p class="text-xs uppercase tracking-[0.15em] text-[var(--livora-accent)]">
                        02
                    </p>

                    <h2 class="mt-4 text-lg font-semibold">
                        طراحی ماندگار
                    </h2>

                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">
                        از ترندهای زودگذر فاصله می‌گیریم و روی طراحی‌هایی تمرکز می‌کنیم که ماندگار باشند.
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">
                    <p class="text-xs uppercase tracking-[0.15em] text-[var(--livora-accent)]">
                        03
                    </p>

                    <h2 class="mt-4 text-lg font-semibold">
                        تجربه ساده
                    </h2>

                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">
                        از مشاهده محصول تا خرید، همه چیز باید ساده، شفاف و لذت‌بخش باشد.
                    </p>
                </div>

            </div>

        </x-layout.container>
    </section>

@endsection
