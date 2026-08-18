@extends('layouts.app')

@section('title', 'تسویه حساب | LIVORA')

@section('content')

    <x-layout.container>

        <div class="py-10">

            <h1 class="text-3xl font-semibold">
                تکمیل سفارش
            </h1>


            <form
                action="{{ route('checkout.place') }}"
                method="POST"
                class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_380px]"
            >

                @csrf


                <div class="space-y-8">

                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

                        <h2 class="font-semibold">
                            اطلاعات تماس
                        </h2>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">

                            <x-ui.input
                                name="first_name"
                                label="نام"
                                required
                            />

                            <x-ui.input
                                name="last_name"
                                label="نام خانوادگی"
                                required
                            />

                            <x-ui.input
                                name="phone"
                                label="شماره موبایل"
                                required
                            />

                            <x-ui.input
                                type="email"
                                name="email"
                                label="ایمیل"
                                :value="auth()->user()->email"
                                required
                            />

                        </div>

                    </div>


                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

                        <h2 class="font-semibold">
                            آدرس تحویل
                        </h2>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">

                            <x-ui.input
                                name="province"
                                label="استان"
                                required
                            />

                            <x-ui.input
                                name="city"
                                label="شهر"
                                required
                            />

                            <div class="sm:col-span-2">

                                <x-ui.input
                                    name="address"
                                    label="آدرس"
                                    required
                                />

                            </div>

                            <x-ui.input
                                name="postal_code"
                                label="کد پستی"
                                required
                            />

                            <x-ui.input
                                name="unit"
                                label="واحد"
                            />

                        </div>

                    </div>

                </div>


                <aside>

                    <div class="sticky top-28 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

                        <h2 class="font-semibold">
                            خلاصه سفارش
                        </h2>

                        <div class="mt-6 space-y-4">

                            @foreach($cart->items as $item)

                                <div class="flex justify-between gap-4 text-sm">

                                    <span class="text-[var(--livora-stone)]">
                                        {{ $item->product->name }}
                                        × {{ $item->quantity }}
                                    </span>

                                    <span>
                                        {{ number_format((float) $item->unit_price * $item->quantity) }}
                                    </span>

                                </div>

                            @endforeach

                            <div class="border-t border-[var(--livora-border)] pt-4 flex justify-between font-semibold">

                                <span>
                                    مجموع
                                </span>

                                <span>
                                    {{ number_format($cart->subtotal()) }}
                                    تومان
                                </span>

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="mt-6 w-full rounded-xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white hover:bg-[var(--livora-accent)]"
                        >
                            ثبت سفارش و ادامه پرداخت
                        </button>

                    </div>

                </aside>

            </form>

        </div>

    </x-layout.container>

@endsection
