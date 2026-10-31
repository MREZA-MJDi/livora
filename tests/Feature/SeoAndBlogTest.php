<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndBlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_defaults_to_noindex_nofollow(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
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
            ->assertSee('noindex, nofollow');
    }

    public function test_sitemap_is_empty_until_global_indexing_is_enabled(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertDontSee('/product/');
    }
}
