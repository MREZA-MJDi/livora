<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\InitialDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogueLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_selectors_do_not_embed_the_product_table_in_admin_forms(): void
    {
        $this->seed(InitialDataSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);

        $baseProduct = Product::query()->active()->firstOrFail();
        $unlistedProduct = $baseProduct->replicate();
        $unlistedProduct->name = 'PRODUCT MUST NOT BE PRELOADED';
        $unlistedProduct->slug = 'product-must-not-be-preloaded';
        $unlistedProduct->sku = 'NO-PRELOAD-0001';
        $unlistedProduct->save();

        $routes = [
            'admin.product-images.create',
            'admin.product-images.index',
            'admin.product-variants.create',
            'admin.product-variants.index',
            'admin.products.create',
            'admin.categories.create',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($admin)->get(route($routeName));

            $response->assertOk()
                ->assertSee('adminCatalogueLookup', false)
                ->assertDontSee('PRODUCT MUST NOT BE PRELOADED');
        }
    }

    public function test_generic_product_and_category_lookups_return_at_most_twenty_results(): void
    {
        $this->seed(InitialDataSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $baseProduct = Product::query()->active()->firstOrFail();

        for ($index = 1; $index <= 25; $index++) {
            $product = $baseProduct->replicate();
            $product->name = sprintf('Bounded Catalogue Product %02d', $index);
            $product->slug = sprintf('bounded-catalogue-product-%02d', $index);
            $product->sku = sprintf('BOUNDED-CAT-%02d', $index);
            $product->save();
        }

        $this->actingAs($admin)
            ->getJson(route('admin.catalogue.lookups.products', ['q' => 'Bounded Catalogue Product']))
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('has_more', true)
            ->assertJsonPath('limit', 20);
    }
}
