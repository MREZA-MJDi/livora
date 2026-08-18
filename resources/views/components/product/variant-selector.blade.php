@props([
'title',
'options' => collect(),
'name',
'type' => 'default',
])

<div
    x-data="{
        selected: null
    }"
    class="space-y-4"
>

    <div class="flex items-center justify-between">

        <h3 class="text-sm font-semibold text-[var(--livora-ink)]">
            {{ $title }}
        </h3>

        <span
            x-show="selected"
            x-text="selected"
            class="text-xs text-[var(--livora-accent)]"
        ></span>

    </div>


    <div class="flex flex-wrap gap-2">

        @foreach($options as $option)

            <label class="cursor-pointer">

                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $option->id }}"
                    x-model="selected"
                    class="sr-only"
                >

                <span
                    class="inline-flex min-w-20 items-center justify-center rounded-xl border px-4 py-2.5 text-sm transition-all"
                    :class="selected == {{ $option->id }}
                        ? 'border-[var(--livora-ink)] bg-[var(--livora-ink)] text-white'
                        : 'border-[var(--livora-border)] bg-[var(--livora-white)] text-[var(--livora-ink)] hover:border-[var(--livora-accent)]'"
                >
                    {{ $option->value }}
                </span>

            </label>

        @endforeach

    </div>

</div>
