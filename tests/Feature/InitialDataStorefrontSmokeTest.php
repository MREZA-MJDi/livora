<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\InitialDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitialDataStorefrontSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_pages_render_after_initial_content_is_seeded(): void
    {
        $this->seed(InitialDataSeeder::class);

        $product = Product::query()->active()->firstOrFail();
        $category = Category::query()->active()->firstOrFail();
        $article = BlogPost::query()->published()->firstOrFail();

        $urls = [
            route('home'),
            route('shop.index'),
            route('categories.index'),
            route('categories.show', $category->slug),
            route('product.show', $product->slug),
            route('blog.index'),
            route('blog.show', $article->slug),
            route('about'),
            route('contact'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }
}
