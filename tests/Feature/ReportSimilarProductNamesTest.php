<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReportSimilarProductNamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_detects_reordered_names_and_never_deletes_products(): void
    {
        $category = Category::query()->create([
            'name' => 'مبلمان',
            'slug' => 'furniture',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $first = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'سیلا کالکشن',
            'slug' => 'silla-collection',
            'sku' => 'SILLA-001',
            'price' => 1000000,
            'stock' => 1,
            'status' => 'active',
        ]);

        $second = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'کالکشن سیلا',
            'slug' => 'collection-silla',
            'sku' => 'SILLA-002',
            'price' => 1200000,
            'stock' => 1,
            'status' => 'active',
        ]);

        Artisan::call('products:report-similar-names', ['--min-similarity' => '0.88']);

        $output = Artisan::output();
        $this->assertStringContainsString('سیلا کالکشن', $output);
        $this->assertStringContainsString('کالکشن سیلا', $output);
        $this->assertDatabaseHas('products', ['id' => $first->id]);
        $this->assertDatabaseHas('products', ['id' => $second->id]);
        $this->assertDatabaseCount('products', 2);
    }
}
