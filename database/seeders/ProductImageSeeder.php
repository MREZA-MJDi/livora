<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'luna-sofa' => [
                '/images/seed/products/sofa-front.svg',
                '/images/seed/products/sofa-detail.svg',
            ],
            'siena-lounge-chair' => [
                '/images/seed/products/chair-front.svg',
                '/images/seed/products/chair-detail.svg',
            ],
            'milo-accent-chair' => [
                '/images/seed/products/chair-detail.svg',
                '/images/seed/products/chair-front.svg',
            ],
            'oak-dining-chair' => [
                '/images/seed/products/chair-front.svg',
            ],
            'nordic-side-table' => [
                '/images/seed/products/table.svg',
            ],
            'mora-lounge-sofa' => [
                '/images/seed/products/sofa-detail.svg',
            ],
            'linea-coffee-table' => [
                '/images/seed/products/table.svg',
            ],
            'arc-floor-lamp' => [
                '/images/seed/products/lamp.svg',
            ],
            'stone-vase' => [
                '/images/seed/products/vase.svg',
            ],
        ];

        foreach ($images as $slug => $productImages) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                continue;
            }

            foreach ($productImages as $index => $path) {
                ProductImage::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sort_order' => $index,
                    ],
                    [
                        'path' => $path,
                        'alt' => $product->name,
                        'is_primary' => $index === 0,
                    ]
                );
            }
        }
    }
}
