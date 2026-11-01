@extends('admin.layouts.app')

@section('title', 'مشاهده پیام')

@section('content')

    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Header --}}

        <div>

            <a
                href="{{ route('admin.contact-messages.index') }}"
                class="text-xs text-[var(--sg-stone)] transition hover:text-[var(--sg-ink)]"
            >
                ← بازگشت به پیام‌ها
            </a>

            <h1 class="mt-4 text-2xl font-semibold">
                پیام {{ $contactMessage->name }}
            </h1>

        </div>


        {{-- Contact Info --}}

        <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-6">

            <div class="grid gap-5 sm:grid-cols-2">

                <div>

                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                        NAME
                    </p>

                    <p class="mt-2 text-sm font-medium">
                        {{ $contactMessage->name }}
                    </p>

                </div>


                <div>

                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
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

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
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

                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
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

        <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-6">

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                MESSAGE
            </p>

            <p class="mt-5 whitespace-pre-line text-sm leading-8 text-[var(--sg-stone)]">
                {{ $contactMessage->message }}
            </p>

        </div>


        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        @if($contactMessage->admin_reply)
            <section class="rounded-3xl border border-emerald-200 bg-emerald-50/50 p-6">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-emerald-700">آخرین پاسخ ثبت‌شده</p>
                <p class="mt-4 whitespace-pre-line text-sm leading-8 text-[var(--admin-text)]">{{ $contactMessage->admin_reply }}</p>
                @if($contactMessage->replied_at)
                    <p class="mt-3 text-xs text-[var(--admin-muted)]">ثبت: {{ $contactMessage->replied_at->format('Y/m/d H:i') }}</p>
                @endif
            </section>
        @endif

        @if($contactMessage->email)
            <section class="rounded-3xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-6">
                <h2 class="text-base font-bold">پاسخ ایمیلی به کاربر</h2>
                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">پاسخ پس از ارسال از طریق Mail تنظیم‌شده در Laravel ثبت می‌شود. قبل از استفاده در Production، SMTP واقعی را تنظیم و تست کنید.</p>
                <form method="POST" action="{{ route('admin.contact-messages.reply', $contactMessage) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="admin-label" for="admin_reply">متن پاسخ</label>
                        <textarea id="admin_reply" name="admin_reply" rows="7" maxlength="5000" required class="admin-textarea">{{ old('admin_reply', $contactMessage->admin_reply) }}</textarea>
                        @error('admin_reply')<p class="admin-error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="admin-btn admin-btn-primary">ارسال پاسخ ایمیلی و ثبت در سابقه</button>
                </form>
            </section>
        @else
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs leading-7 text-amber-800">
                برای این پیام آدرس ایمیل ثبت نشده است. پاسخ ایمیلی ممکن نیست؛ از شماره تماس برای پیگیری استفاده کنید.
            </div>
        @endif

        {{-- Actions --}}

        <div class="flex flex-wrap gap-3">

            @if($contactMessage->status === 'unread')

                <form
                    action="{{ route('admin.contact-messages.read', $contactMessage) }}"
                    method="POST"
                >

                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="rounded-2xl bg-[var(--sg-ink)] px-5 py-3 text-xs font-medium text-white"
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
