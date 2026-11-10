@php
    $flashType = session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : (session('info') ? 'info' : null)));
    $flashMessage = session('success') ?? session('error') ?? session('warning') ?? session('info');
    $hasValidationErrors = isset($errors) && $errors->any();
@endphp

@if($flashMessage || $hasValidationErrors)
    <div class="pointer-events-none fixed inset-x-4 top-4 z-[450] flex justify-center sm:inset-x-auto sm:left-5 sm:right-auto sm:w-full sm:max-w-md"
         x-data="{ visible: true, detailsOpen: {{ $hasValidationErrors ? 'true' : 'false' }} }"
         x-init="if (!detailsOpen && @js($flashType !== 'error' && !$hasValidationErrors)) setTimeout(() => visible = false, 5500)"
         x-show="visible" x-transition.opacity>
        <div class="pointer-events-auto w-full overflow-hidden rounded-2xl border bg-[var(--admin-white)] shadow-2xl
            {{ $flashType === 'success' ? 'border-emerald-300' : (($flashType === 'error' || $hasValidationErrors) ? 'border-red-300' : 'border-[var(--admin-border)]') }}"
             role="{{ $hasValidationErrors || $flashType === 'error' ? 'alert' : 'status' }}" aria-live="polite">
            <div class="flex items-start gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg
                    {{ $flashType === 'success' ? 'bg-emerald-50 text-emerald-700' : (($flashType === 'error' || $hasValidationErrors) ? 'bg-red-50 text-red-700' : 'bg-[var(--admin-surface-soft)] text-[var(--admin-text)]') }}">
                    @if($flashType === 'success') ✓ @elseif($flashType === 'error' || $hasValidationErrors) ! @else i @endif
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-[var(--admin-text)]">
                        @if($flashType === 'success') عملیات با موفقیت انجام شد
                        @elseif($flashType === 'error' || $hasValidationErrors) عملیات انجام نشد
                        @else پیام سیستم
                        @endif
                    </p>
                    @if($flashMessage)
                        <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">{{ $flashMessage }}</p>
                    @elseif($hasValidationErrors)
                        <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">اطلاعات واردشده نیاز به اصلاح دارد. جزئیات خطا را بررسی کن.</p>
                    @endif
                    @if($hasValidationErrors)
                        <button type="button" @click="detailsOpen = true" class="mt-2 text-xs font-bold text-red-700 underline underline-offset-4">نمایش جزئیات خطاها</button>
                    @endif
                </div>
                <button type="button" @click="visible = false" class="rounded-lg p-1 text-[var(--admin-muted)] hover:bg-[var(--admin-surface-soft)]" aria-label="بستن پیام">✕</button>
            </div>
        </div>

        @if($hasValidationErrors)
            <div x-show="detailsOpen" x-cloak x-transition.opacity class="pointer-events-auto fixed inset-0 z-[460] flex items-center justify-center bg-black/50 p-4"
                 @keydown.escape.window="detailsOpen = false">
                <section class="w-full max-w-lg rounded-3xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-5 shadow-2xl sm:p-6"
                         role="dialog" aria-modal="true" aria-labelledby="admin-validation-title">
                    <div class="flex items-center justify-between gap-3">
                        <h3 id="admin-validation-title" class="text-base font-bold text-[var(--admin-text)]">مواردی که باید اصلاح شوند</h3>
                        <button type="button" @click="detailsOpen = false" class="rounded-lg p-2 hover:bg-[var(--admin-surface-soft)]" aria-label="بستن">✕</button>
                    </div>
                    <ul class="mt-4 max-h-[55vh] space-y-2 overflow-y-auto">
                        @foreach($errors->all() as $error)
                            <li class="rounded-xl bg-red-50 px-4 py-3 text-sm leading-6 text-red-800">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" @click="detailsOpen = false" class="admin-btn admin-btn-primary mt-5 w-full">متوجه شدم</button>
                </section>
            </div>
        @endif
    </div>
@endif
