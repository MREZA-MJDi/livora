@props([
'type' => 'text',
'name' => null,
'label' => null,
'placeholder' => null,
'value' => null,
'required' => false,
'disabled' => false,
])

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>

    @if($label)
        <label
            @if($name) for="{{ $name }}" @endif
        class="mb-2 block text-sm font-medium text-[var(--livora-ink)]"
        >
            {{ $label }}

            @if($required)
                <span class="text-[var(--livora-accent)]">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        @if($name) name="{{ $name }}" @endif
        @if($name) id="{{ $name }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($value !== null) value="{{ $value }}" @endif
        @required($required)
        @disabled($disabled)
        {{ $attributes->except('class')->merge([
            'class' => 'w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm text-[var(--livora-ink)] outline-none transition-all duration-300 placeholder:text-[var(--livora-stone)] focus:border-[var(--livora-accent)] focus:ring-1 focus:ring-[var(--livora-accent)] disabled:cursor-not-allowed disabled:opacity-50',
        ]) }}
    >

</div>
