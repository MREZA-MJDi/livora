<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndSlugGovernanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_production_robots_are_always_noindex_nofollow(): void
    {
        $settings = SeoSetting::query()->create([
            'indexing_enabled' => true,
            'following_enabled' => true,
            'site_name' => 'SillaGallery',
        ]);

        config(['app.env' => 'local']);
        $this->assertSame('noindex, nofollow', $settings->robots());
        $this->assertSame('noindex, nofollow', $settings->robotsFor(true, true));
    }

    public function test_production_robots_respect_global_and_page_level_settings(): void
    {
        $settings = SeoSetting::query()->create([
            'indexing_enabled' => true,
            'following_enabled' => true,
            'site_name' => 'SillaGallery',
        ]);

        $this->app['env'] = 'production';
        $this->assertSame('index, follow', $settings->robotsFor(true, true));
        $this->assertSame('noindex, follow', $settings->robotsFor(false, true));
        $settings->update(['indexing_enabled' => false]);
        $this->assertSame('noindex, follow', $settings->robotsFor(true, true));
    }

    public function test_product_slug_changes_create_a_query_preserving_permanent_redirect(): void
    {
        $category = Category::query()->create([
            'name' => 'دسته تست',
            'slug' => 'test-category',
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'مبل تست',
            'slug' => 'test-sofa-old',
            'sku' => 'TEST-SOFA-001',
            'price' => 1000000,
            'stock' => 1,
            'status' => 'active',
        ]);

        $product->update(['slug' => 'test-sofa-new']);

        $this->assertDatabaseHas('slug_redirects', [
            'old_path' => '/product/test-sofa-old',
            'new_path' => '/product/test-sofa-new',
            'is_active' => true,
        ]);

        $this->get('/product/test-sofa-old?source=legacy')
            ->assertStatus(301)
            ->assertRedirect('/product/test-sofa-new?source=legacy');
    }

    public function test_category_slug_changes_create_a_permanent_redirect(): void
    {
        $category = Category::query()->create([
            'name' => 'دسته قدیمی',
            'slug' => 'old-category',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category->update(['slug' => 'new-category']);

        $this->get('/categories/old-category')
            ->assertStatus(301)
            ->assertRedirect('/categories/new-category');
    }
}
