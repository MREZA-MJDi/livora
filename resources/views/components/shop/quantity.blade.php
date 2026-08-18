@props([
'value' => 1,
'min' => 1,
'max' => 99,
'name' => 'quantity',
])

<div
    x-data="{
        quantity: {{ (int) $value }},
        min: {{ (int) $min }},
        max: {{ (int) $max }},

        increase() {
            if (this.quantity < this.max) {
                this.quantity++;
            }
        },

        decrease() {
            if (this.quantity > this.min) {
                this.quantity--;
            }
        }
    }"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)]'
    ]) }}
>

    {{-- Decrease --}}
    <button
        type="button"
        @click="decrease()"
        :disabled="quantity <= min"
        class="flex h-10 w-10 items-center justify-center text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-ink)] disabled:cursor-not-allowed disabled:opacity-40"
        aria-label="کاهش تعداد"
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
                d="M5 12h14"
            />
        </svg>
    </button>


    {{-- Quantity --}}
    <input
        type="number"
        :value="quantity"
        name="{{ $name }}"
        min="{{ $min }}"
        max="{{ $max }}"
        readonly
        class="w-10 border-0 bg-transparent p-0 text-center text-sm font-medium text-[var(--livora-ink)] outline-none"
        aria-label="تعداد"
    >


    {{-- Increase --}}
    <button
        type="button"
        @click="increase()"
        :disabled="quantity >= max"
        class="flex h-10 w-10 items-center justify-center text-[var(--livora-stone)] transition-colors duration-300 hover:text-[var(--livora-ink)] disabled:cursor-not-allowed disabled:opacity-40"
        aria-label="افزایش تعداد"
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
                d="M12 5v14m-7-7h14"
            />
        </svg>
    </button>

</div>
