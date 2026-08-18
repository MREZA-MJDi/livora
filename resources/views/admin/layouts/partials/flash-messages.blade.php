@if(session('success'))
    <div class="admin-alert admin-alert-success mb-6 flex items-start gap-3">

        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="mt-0.5 h-5 w-5 shrink-0"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m9 12.75 2.25 2.25L15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
        </svg>

        <div class="flex-1 text-sm leading-7">
            {{ session('success') }}
        </div>

    </div>
@endif


@if(session('error'))
    <div class="admin-alert admin-alert-danger mb-6 flex items-start gap-3">

        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="mt-0.5 h-5 w-5 shrink-0"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m0 3h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
        </svg>

        <div class="flex-1 text-sm leading-7">
            {{ session('error') }}
        </div>

    </div>
@endif


@if(session('warning'))
    <div class="admin-alert admin-alert-warning mb-6 flex items-start gap-3">

        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="mt-0.5 h-5 w-5 shrink-0"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m0 3h.008v.008H12v-.008ZM10.125 3.75h3.75L21 18.75H3l7.125-15Z"
            />
        </svg>

        <div class="flex-1 text-sm leading-7">
            {{ session('warning') }}
        </div>

    </div>
@endif


@if(session('info'))
    <div class="admin-alert admin-alert-info mb-6 flex items-start gap-3">

        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="mt-0.5 h-5 w-5 shrink-0"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 16.5v-4.5m0-3h.008v.008H12V9Zm0-6.75a9.75 9.75 0 1 0 0 19.5 9.75 9.75 0 0 0 0-19.5Z"
            />
        </svg>

        <div class="flex-1 text-sm leading-7">
            {{ session('info') }}
        </div>

    </div>
@endif


@if($errors->any())
    <div class="admin-alert admin-alert-danger mb-6">

        <div class="flex items-start gap-3">

            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="mt-0.5 h-5 w-5 shrink-0"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v3.75m0 3h.008v.008H12V9Zm8.25 3a8.25 8.25 0 1 1-16.5 0 8.25 8.25 0 0 1 16.5 0Z"
                />
            </svg>

            <div class="flex-1">

                <p class="mb-2 text-sm font-semibold">
                    لطفاً خطاهای زیر را بررسی کنید:
                </p>

                <ul class="space-y-1 text-sm leading-7">

                    @foreach($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach

                </ul>

            </div>

        </div>

    </div>
@endif
