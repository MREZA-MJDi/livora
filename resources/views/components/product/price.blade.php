@props([
'product',
'showInstallment' => true,
'compact' => false,
])

@php
    $price = (float) $product->price;
    $compareAtPrice = $product->compare_at_price ? (float) $product->compare_at_price : null;
    $hasDiscount = $compareAtPrice !== null && $compareAtPrice > $price;
    $discountPercent = $hasDiscount ? round((($compareAtPrice - $price) / $compareAtPrice) * 100) : 0;
    $installmentEnabled = $showInstallment && (bool) $product->installment_enabled && (int) $product->installment_cash_percent < 100;
    $cashPercent = (int) ($product->installment_cash_percent ?? 100);
    $cashAmount = $installmentEnabled ? round($price * ($cashPercent / 100)) : null;
    $remainderAmount = $installmentEnabled ? $price - $cashAmount : null;
    $chequeCount = (int) ($product->installment_cheque_count ?? 0);
    $chequeAmount = $installmentEnabled && $chequeCount > 0 ? round($remainderAmount / $chequeCount) : null;
@endphp

<div
    {{ $attributes->merge(['class' => 'product-price']) }}
    x-data="{
        basePrice: {{ $price }},
        currentPrice: {{ $price }},
        selectedAdjustments: {},
        hasSelectedVariant: false,
        cashPercent: {{ $cashPercent }},
        chequeCount: {{ $chequeCount }},
        updateVariantPrice(event) {
            const detail = event?.detail ?? {};
            if (!detail.type) return;
            this.hasSelectedVariant = true;
            this.selectedAdjustments[detail.type] = Number(detail.priceAdjustment || 0);
            this.currentPrice = Math.max(0, this.basePrice + Object.values(this.selectedAdjustments).reduce((sum, value) => sum + value, 0));
        },
        money(value) { return new Intl.NumberFormat('fa-IR').format(Math.round(Number(value) || 0)); }
    }"
    @product-variant-selected.window="updateVariantPrice($event)"
>
    @if ($hasDiscount)
        <div class="product-price__discount" x-show="!hasSelectedVariant">{{ $discountPercent }}٪ تخفیف</div>
    @endif
    <div class="product-price__main">
        @if ($hasDiscount)
            <span class="product-price__compare" x-show="!hasSelectedVariant">{{ number_format($compareAtPrice) }} <span>تومان</span></span>
        @endif
        <div class="product-price__current">
            <strong x-text="money(currentPrice)">{{ number_format($price) }}</strong>
            <span>تومان</span>
        </div>
    </div>
    @if ($installmentEnabled)
        <div class="product-price__installment">
            <div class="product-price__installment-header">
                <span class="product-price__installment-icon">✓</span>
                <span>امکان خرید اقساطی</span>
            </div>
            <div class="product-price__installment-content">
                <div class="product-price__installment-row">
                    <span>پرداخت اولیه</span>
                    <strong x-text="money(currentPrice * cashPercent / 100) + ' تومان'">{{ number_format($cashAmount) }} تومان</strong>
                </div>
                @if ($chequeCount > 0 && $chequeAmount)
                    <div class="product-price__installment-row">
                        <span>{{ $chequeCount }} فقره چک</span>
                        <strong x-text="'هر چک ' + money((currentPrice * (1 - cashPercent / 100)) / chequeCount) + ' تومان'">هر چک {{ number_format($chequeAmount) }} تومان</strong>
                    </div>
                @endif
            </div>
            <div class="product-price__installment-note">{{ $cashPercent }}٪ نقد، مابقی به‌صورت اقساطی</div>
        </div>
    @endif
</div>
