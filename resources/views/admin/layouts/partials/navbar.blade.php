<header class="admin-navbar sticky top-0 z-40 h-16 border-b">

    <div class="flex h-full items-center justify-between px-4 sm:px-6 lg:px-8">

        {{-- Right Side --}}
        <div class="flex items-center gap-3">

            {{-- Mobile Menu --}}
            <button
                type="button"
                id="admin-mobile-menu-button"
                class="admin-navbar-action lg:hidden"
                aria-label="باز کردن منو"
                aria-controls="admin-sidebar"
                aria-expanded="false"
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
                        d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"
                    />
                </svg>
            </button>

            {{-- Page Title --}}
            <div>
                <p class="text-xs font-medium text-[var(--admin-muted)]">
                    پنل مدیریت
                </p>

                <h1 class="mt-0.5 text-sm font-bold text-[var(--admin-text)] sm:text-base">
                    @yield('page_title', 'داشبورد')
                </h1>
            </div>

        </div>


        {{-- Left Side --}}
        <div class="flex items-center gap-2">

            {{-- Store --}}
            <a
                href="{{ route('home') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="hidden items-center gap-2 rounded-lg px-3 py-2 text-sm text-[var(--admin-text-soft)] transition hover:bg-white/5 hover:text-[var(--admin-text)] sm:inline-flex"
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
                        d="M13.5 21 3 11.25 12.75 3l2.25 2.25m-3.75 0H21v9.75"
                    />
                </svg>

                مشاهده فروشگاه
            </a>


            {{-- User Menu --}}
            @auth
                <div class="relative">

                    <button
                        type="button"
                        id="admin-user-menu-button"
                        class="flex items-center gap-2 rounded-xl px-2 py-1.5 transition hover:bg-white/5"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >

                        <span class="hidden text-right sm:block">

                            <span class="block text-xs font-medium text-[var(--admin-muted)]">
                                مدیر
                            </span>

                            <span class="block max-w-32 truncate text-sm font-semibold text-[var(--admin-text)]">
                                {{ auth()->user()->name }}
                            </span>

                        </span>


                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[var(--admin-accent)] text-sm font-bold text-white">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="hidden h-4 w-4 text-[var(--admin-muted)] sm:block"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m6.75 9 5.25 5.25L17.25 9"
                            />
                        </svg>

                    </button>


                    {{-- Dropdown --}}
                    <div
                        id="admin-user-menu"
                        class="admin-dropdown absolute left-0 top-full mt-2 hidden"
                    >

                        <div class="mb-1 border-b border-[var(--admin-border)] px-3 py-2">

                            <p class="truncate text-sm font-semibold text-[var(--admin-text)]">
                                {{ auth()->user()->name }}
                            </p>

                            <p class="mt-0.5 truncate text-xs text-[var(--admin-muted)]">
                                {{ auth()->user()->email }}
                            </p>

                        </div>


                        <a
                            href="{{ route('home') }}"
                            class="admin-dropdown-item"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-4 w-4"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 10.5V6.75a3.75 3.75 0 0 0-7.5 0V10.5m-3 0h13.5l-1.5 9h-10.5l-1.5-9Z"
                                />
                            </svg>

                            مشاهده فروشگاه
                        </a>


                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="admin-dropdown-item text-[var(--admin-danger)]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-4 w-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H10.5m0 0 3-3m-3 3 3 3"
                                    />
                                </svg>

                                خروج
                            </button>
                        </form>

                    </div>

                </div>
            @endauth

        </div>

    </div>

</header>
