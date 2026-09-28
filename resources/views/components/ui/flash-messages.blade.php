@php
    $flashItems = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'warning', 'message' => session('warning')],
        ['type' => 'info', 'message' => session('info')],
    ])->filter(fn (array $item) => filled($item['message']));

    if ($errors->any()) {
        $flashItems->push([
            'type' => 'error',
            'message' => 'اطلاعات واردشده کامل نیست. لطفاً موارد مشخص‌شده را بررسی کنید.',
        ]);
    }

    $flashItems = $flashItems->values();
@endphp

@if($flashItems->isNotEmpty())
    <div
        class="pointer-events-none fixed inset-x-3 top-4 z-[120] mx-auto flex max-w-lg flex-col gap-3"
        aria-live="polite"
        aria-atomic="true"
    >
        @foreach($flashItems as $item)
            @php
                $classes = match($item['type']) {
                    'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                    'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
                    'info' => 'border-sky-200 bg-sky-50 text-sky-900',
                    default => 'border-red-200 bg-red-50 text-red-900',
                };

                $icon = match($item['type']) {
                    'success' => '✓',
                    'warning' => '!',
                    'info' => 'i',
                    default => '!',
                };
            @endphp

            <div
                x-data="{ open: true }"
                x-show="open"
                x-transition.opacity.duration.250ms
                x-init="setTimeout(() => open = false, {{ $item['type'] === 'error' ? 7000 : 4500 }})"
                class="pointer-events-auto rounded-2xl border px-4 py-3 shadow-[0_18px_50px_rgba(24,23,21,.14)] backdrop-blur-xl {{ $classes }}"
                role="status"
            >
                <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/70 text-xs font-bold">
                        {{ $icon }}
                    </span>

                    <p class="min-w-0 flex-1 pt-1 text-xs font-medium leading-6">
                        {{ $item['message'] }}
                    </p>

                    <button
                        type="button"
                        @click="open = false"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs opacity-50 transition hover:bg-white/60 hover:opacity-100"
                        aria-label="بستن پیام"
                    >
                        ×
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
