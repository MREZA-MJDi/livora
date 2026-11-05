@php
    $siteKey = config('services.recaptcha.site_key');
    $secretKey = config('services.recaptcha.secret_key');
@endphp

@if(filled($siteKey) && filled($secretKey))
    <div class="space-y-2">
        <div
            class="g-recaptcha"
            data-sitekey="{{ $siteKey }}"
            data-theme="light"
        ></div>

        @error('captcha')
            <p class="text-xs leading-6 text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    @once
        <script
            src="https://www.google.com/recaptcha/api.js?hl=fa"
            async
            defer
        ></script>
    @endonce
@elseif(app()->isProduction())
    <p class="text-xs leading-6 text-red-600" role="alert">
        سامانه ضدربات در حال حاضر پیکربندی نشده است؛ امکان ارسال این فرم وجود ندارد.
    </p>
@endif
