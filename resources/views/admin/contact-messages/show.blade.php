@extends('admin.layouts.app')

@section('title', 'مشاهده پیام')

@section('content')

    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Header --}}

        <div>

            <a
                href="{{ route('admin.contact-messages.index') }}"
                class="text-xs text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)]"
            >
                ← بازگشت به پیام‌ها
            </a>

            <h1 class="mt-4 text-2xl font-semibold">
                پیام {{ $contactMessage->name }}
            </h1>

        </div>


        {{-- Contact Info --}}

        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

            <div class="grid gap-5 sm:grid-cols-2">

                <div>

                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                        NAME
                    </p>

                    <p class="mt-2 text-sm font-medium">
                        {{ $contactMessage->name }}
                    </p>

                </div>


                <div>

                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                        PHONE
                    </p>

                    <p
                        class="mt-2 text-sm"
                        dir="ltr"
                    >
                        {{ $contactMessage->phone }}
                    </p>

                </div>


                @if($contactMessage->email)

                    <div>

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                            EMAIL
                        </p>

                        <p
                            class="mt-2 text-sm"
                            dir="ltr"
                        >
                            {{ $contactMessage->email }}
                        </p>

                    </div>

                @endif


                <div>

                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                        SUBJECT
                    </p>

                    <p class="mt-2 text-sm">
                        {{ match($contactMessage->subject) {
                            'product' => 'مشاوره درباره محصول',
                            'installment' => 'شرایط خرید اقساطی',
                            'order' => 'پیگیری سفارش',
                            'shipping' => 'ارسال و تحویل',
                            'other' => 'سایر',
                            default => $contactMessage->subject,
                        } }}
                    </p>

                </div>

            </div>

        </div>


        {{-- Message --}}

        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                MESSAGE
            </p>

            <p class="mt-5 whitespace-pre-line text-sm leading-8 text-[var(--livora-stone)]">
                {{ $contactMessage->message }}
            </p>

        </div>


        {{-- Actions --}}

        <div class="flex flex-wrap gap-3">

            @if($contactMessage->status !== 'read')

                <form
                    action="{{ route('admin.contact-messages.read', $contactMessage) }}"
                    method="POST"
                >

                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="rounded-2xl bg-[var(--livora-ink)] px-5 py-3 text-xs font-medium text-white"
                    >
                        علامت‌گذاری به عنوان خوانده‌شده
                    </button>

                </form>

            @endif


            <form
                action="{{ route('admin.contact-messages.destroy', $contactMessage) }}"
                method="POST"
                onsubmit="return confirm('آیا از حذف این پیام مطمئن هستید؟');"
            >

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="rounded-2xl border border-red-200 px-5 py-3 text-xs font-medium text-red-600"
                >
                    حذف پیام
                </button>

            </form>

        </div>

    </div>

@endsection
