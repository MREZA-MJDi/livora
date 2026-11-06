<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentPage;
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
        $article = BlogPost::query()->published()->whereNotNull('featured_image')->firstOrFail();
        $about = ContentPage::query()->published()->where('slug', 'about')->firstOrFail();

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

        $this->get(route('about'))
            ->assertOk()
            ->assertSee($about->featured_image)
            ->assertSee('خانه، بازتابی از شیوه زندگی ما');

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee($article->featured_image)
            ->assertSee($article->title);

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }
}
