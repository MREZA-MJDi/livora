<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'demo@livora.test')
            ->firstOrFail();

        $cart = Cart::updateOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'active',
            ],
            [
                'session_id' => null,
            ]
        );

        $product = Product::query()
            ->where('slug', 'luna-sofa')
            ->firstOrFail();

        // Demo carts must use the same canonical variant snapshot and price
        // calculation as a real add-to-cart request. A bare "base" key is
        // invalid for products whose active option groups require selections.
        $requiredTypes = $product->allVariants()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->values();

        $selectedVariants = $product->variants()
            ->orderBy('type')
            ->orderBy('id')
            ->get()
            ->groupBy('type')
            ->map(fn ($variants) => $variants->first())
            ->values()
            ->sortBy('type', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $selectedTypes = $selectedVariants->pluck('type');
        $missingTypes = $requiredTypes->diff($selectedTypes)->values();

        if ($missingTypes->isNotEmpty()) {
            throw new RuntimeException(sprintf(
                'Cannot seed demo cart for "%s": no active selection exists for variant group(s): %s.',
                $product->slug,
                $missingTypes->implode(', ')
            ));
        }

        $variantOptions = $selectedVariants
            ->map(fn ($variant) => [
                'id' => (int) $variant->id,
                'type' => (string) $variant->type,
                'name' => (string) $variant->name,
                'value' => (string) $variant->value,
                'sku' => $variant->sku,
                'color_hex' => $variant->color_hex,
                'price_adjustment' => (float) $variant->price_adjustment,
                'stock' => (int) $variant->stock,
            ])
            ->values()
            ->all();

        $variantParts = $selectedVariants
            ->map(fn ($variant) => Str::lower((string) $variant->type) . ':' . $variant->id)
            ->values()
            ->all();

        $variantKey = $variantParts === []
            ? 'base'
            : hash('sha256', $product->id . '|' . implode('|', $variantParts));

        $unitPrice = max(
            0,
            (float) $product->price
                + (float) $selectedVariants->sum(
                    fn ($variant) => (float) $variant->price_adjustment
                )
        );

        $cart->items()->updateOrCreate(
            [
                'product_id' => $product->id,
            ],
            [
                'product_variant_id' => $selectedVariants->first()?->id,
                'variant_key' => $variantKey,
                'variant_options' => $variantOptions ?: null,
                'quantity' => 1,
                'unit_price' => $unitPrice,
            ]
        );
    }
}
