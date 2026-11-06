<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndBlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_does_not_emit_robots_directives(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('name="robots"', false)
            ->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_blog_only_shows_published_posts(): void
    {
        BlogPost::create([
            'title' => 'Draft article',
            'slug' => 'draft-article',
            'body' => 'Draft content',
            'status' => 'draft',
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        BlogPost::create([
            'title' => 'Published article',
            'slug' => 'published-article',
            'body' => 'Published content',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('Published article')
            ->assertDontSee('Draft article');

        $this->get('/blog/draft-article')->assertNotFound();

        $this->get('/blog/published-article')
            ->assertOk()
            ->assertDontSee('name="robots"', false);
    }

    public function test_sitemap_lists_published_blog_posts_without_global_indexing_switch(): void
    {
        BlogPost::create([
            'title' => 'Published sitemap article',
            'slug' => 'published-sitemap-article',
            'body' => 'Published content',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $sitemap = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        ob_start();
        $sitemap->baseResponse->sendContent();
        $xml = (string) ob_get_clean();

        $this->assertStringContainsString('/blog/published-sitemap-article', $xml);
    }
}
