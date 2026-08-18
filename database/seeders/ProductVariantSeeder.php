<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $variants = [
            'luna-sofa' => [
                [
                    'type' => 'color',
                    'name' => 'رنگ',
                    'value' => 'cream',
                    'sku' => 'LIV-SOF-001-CREAM',
                    'price_adjustment' => 0,
                    'stock' => 5,
                ],
                [
                    'type' => 'color',
                    'name' => 'رنگ',
                    'value' => 'charcoal',
                    'sku' => 'LIV-SOF-001-CHARCOAL',
                    'price_adjustment' => 0,
                    'stock' => 4,
                ],
                [
                    'type' => 'color',
                    'name' => 'رنگ',
                    'value' => 'brown',
                    'sku' => 'LIV-SOF-001-BROWN',
                    'price_adjustment' => 1500000,
                    'stock' => 3,
                ],
                [
                    'type' => 'size',
                    'name' => 'سایز',
                    'value' => 'small',
                    'sku' => 'LIV-SOF-001-S',
                    'price_adjustment' => -5000000,
                    'stock' => 4,
                ],
                [
                    'type' => 'size',
                    'name' => 'سایز',
                    'value' => 'medium',
                    'sku' => 'LIV-SOF-001-M',
                    'price_adjustment' => 0,
                    'stock' => 5,
                ],
                [
                    'type' => 'size',
                    'name' => 'سایز',
                    'value' => 'large',
                    'sku' => 'LIV-SOF-001-L',
                    'price_adjustment' => 7000000,
                    'stock' => 3,
                ],
            ],

            'siena-lounge-chair' => [
                [
                    'type' => 'color',
                    'name' => 'رنگ',
                    'value' => 'beige',
                    'sku' => 'LIV-CHR-001-BEIGE',
                    'price_adjustment' => 0,
                    'stock' => 8,
                ],
                [
                    'type' => 'color',
                    'name' => 'رنگ',
                    'value' => 'brown',
                    'sku' => 'LIV-CHR-001-BROWN',
                    'price_adjustment' => 1200000,
                    'stock' => 10,
                ],
            ],

            'milo-accent-chair' => [
                [
                    'type' => 'color',
                    'name' => 'رنگ',
                    'value' => 'brown',
                    'sku' => 'LIV-CHR-002-BROWN',
                    'price_adjustment' => 0,
                    'stock' => 6,
                ],
                [
                    'type' => 'color',
                    'name' => 'رنگ',
                    'value' => 'cream',
                    'sku' => 'LIV-CHR-002-CREAM',
                    'price_adjustment' => 700000,
                    'stock' => 4,
                ],
            ],
        ];

        foreach ($variants as $slug => $productVariants) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                continue;
            }

            foreach ($productVariants as $variant) {
                ProductVariant::updateOrCreate(
                    ['sku' => $variant['sku']],
                    [
                        'product_id' => $product->id,
                        'type' => $variant['type'],
                        'name' => $variant['name'],
                        'value' => $variant['value'],
                        'price_adjustment' => $variant['price_adjustment'],
                        'stock' => $variant['stock'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
