<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\InitialDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomeSectionLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_product_and_category_lookups_are_search_driven_and_bounded(): void
    {
        $this->seed(InitialDataSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);

        $baseProduct = Product::query()->active()->firstOrFail();
        for ($index = 1; $index <= 25; $index++) {
            $product = $baseProduct->replicate();
            $product->name = sprintf('CMS Lookup Product %02d', $index);
            $product->slug = sprintf('cms-lookup-product-%02d', $index);
            $product->sku = sprintf('CMS-LOOKUP-%02d', $index);
            $product->save();
        }

        $baseCategory = Category::query()->where('is_active', true)->firstOrFail();
        for ($index = 1; $index <= 25; $index++) {
            $category = $baseCategory->replicate();
            $category->name = sprintf('CMS Lookup Category %02d', $index);
            $category->slug = sprintf('cms-lookup-category-%02d', $index);
            $category->save();
        }

        $this->actingAs($admin)
            ->get(route('admin.catalogue.lookups.products', ['q' => 'CMS Lookup Product']))
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('has_more', true)
            ->assertJsonPath('limit', 20);

        $this->get(route('admin.catalogue.lookups.categories', ['q' => 'CMS Lookup Category']))
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('has_more', true)
            ->assertJsonPath('limit', 20);

        $this->getJson(route('admin.catalogue.lookups.products', ['q' => '   ']))
            ->assertUnprocessable();
    }

    public function test_homepage_section_form_does_not_embed_the_product_or_category_catalogues(): void
    {
        $this->seed(InitialDataSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::query()->active()->firstOrFail();
        $product->update(['name' => 'PRODUCT MUST NOT BE PRELOADED']);

        $this->actingAs($admin)
            ->get(route('admin.home-sections.create'))
            ->assertOk()
            ->assertSee('adminCatalogueLookup', false)
            ->assertDontSee('PRODUCT MUST NOT BE PRELOADED');
    }
}
