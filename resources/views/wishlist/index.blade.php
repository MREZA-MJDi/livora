@extends('layouts.app')

@section('title', 'علاقه‌مندی‌ها | LIVORA')

@section('content')

    <x-layout.container>

        <div class="py-10">

            <h1 class="text-3xl font-semibold">
                علاقه‌مندی‌ها
            </h1>

            @if($products->count())

                <div class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">

                    @foreach($products as $product)

                        <div>

                            <x-product.card
                                :product="$product"
                            />

                            <form
                                action="{{ route('wishlist.destroy', $product) }}"
                                method="POST"
                                class="mt-3"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="w-full rounded-xl border border-[var(--livora-border)] px-4 py-2.5 text-xs hover:border-red-300 hover:text-red-700"
                                >
                                    حذف از علاقه‌مندی‌ها
                                </button>

                            </form>

                        </div>

                    @endforeach

                </div>

                <div class="mt-10">
                    {{ $products->links() }}
                </div>

            @else

                <div class="py-20 text-center text-sm text-[var(--livora-stone)]">
                    هنوز محصولی به علاقه‌مندی‌ها اضافه نشده است.
                </div>

            @endif

        </div>

    </x-layout.container>

@endsection
