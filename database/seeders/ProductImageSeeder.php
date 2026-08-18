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
                'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1550226891-ef816aed4a98?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1000&q=80',
            ],

            'siena-lounge-chair' => [
                'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?auto=format&fit=crop&w=1200&q=85',
            ],

            'milo-accent-chair' => [
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1200&q=85',
            ],

            'oak-dining-chair' => [
                'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=1200&q=85',
            ],

            'nordic-side-table' => [
                'https://images.unsplash.com/photo-1532372320572-cda25653a26d?auto=format&fit=crop&w=1200&q=85',
            ],

            'mora-lounge-sofa' => [
                'https://images.unsplash.com/photo-1550226891-ef816aed4a98?auto=format&fit=crop&w=1200&q=85',
            ],

            'linea-coffee-table' => [
                'https://images.unsplash.com/photo-1532372320572-cda25653a26d?auto=format&fit=crop&w=1200&q=85',
            ],

            'arc-floor-lamp' => [
                'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=1200&q=85',
            ],

            'stone-vase' => [
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1200&q=85',
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
                        'path' => $path,
                    ],
                    [
                        'alt' => $product->name,
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ]
                );
            }
        }
    }
}
