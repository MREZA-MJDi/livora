<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SeoPageSetting;
use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_public_pages_are_noindex_nofollow_and_sitemap_is_empty_by_default(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $sitemap = $this->get('/sitemap.xml');
        $sitemap
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        // StreamedResponse content is emitted only when sendContent() runs;
        // TestResponse::assertSee() cannot inspect an unexecuted stream callback.
        ob_start();
        $sitemap->baseResponse->sendContent();
        $xml = (string) ob_get_clean();

        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringNotContainsString('<url>', $xml);
    }

    public function test_fixed_page_seo_values_override_template_defaults_when_global_gate_is_enabled(): void
    {
        SeoSetting::current()->update([
            'indexing_enabled' => true,
            'following_enabled' => true,
        ]);
        Cache::forget('seo_settings.current');

        SeoPageSetting::query()
            ->where('route_key', 'contact')
            ->firstOrFail()
            ->update([
                'meta_title' => 'SillaGallery Contact SEO Test',
                'meta_description' => 'Contact page metadata controlled from the SEO panel.',
                'seo_index' => true,
                'seo_follow' => true,
            ]);
        Cache::forget('seo_page_settings.contact');

        $this->get('/contact')
            ->assertOk()
            ->assertSee('<title>SillaGallery Contact SEO Test</title>', false)
            ->assertSee('<meta name="description" content="Contact page metadata controlled from the SEO panel.">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false);

        // Private commerce routes must stay noindex even after global indexing
        // is enabled for public catalogue and content pages.
        $this->get('/cart')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_category_meta_title_is_used_while_global_noindex_lock_remains_enforced(): void
    {
        $category = Category::query()->create([
            'name' => 'مبلمان تست',
            'slug' => 'test-furniture',
            'description' => 'توضیحات تست',
            'meta_title' => 'عنوان اختصاصی دسته‌بندی تست | SillaGallery',
            'meta_description' => 'توضیحات متای اختصاصی دسته‌بندی تست.',
            'is_active' => true,
            'sort_order' => 1,
            'seo_index' => true,
            'seo_follow' => true,
        ]);

        $this->get('/categories/' . $category->slug)
            ->assertOk()
            ->assertSee('<title>عنوان اختصاصی دسته‌بندی تست | SillaGallery</title>', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_page_level_robots_flags_cannot_override_the_global_safety_lock(): void
    {
        SeoPageSetting::query()
            ->where('route_key', 'blog')
            ->firstOrFail()
            ->update([
                'seo_index' => true,
                'seo_follow' => true,
            ]);
        Cache::forget('seo_page_settings.blog');

        $this->get('/blog')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
    public function test_admin_cms_forms_render_editor_and_fixed_page_slugs_cannot_break_navigation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $about = \App\Models\ContentPage::query()->where('slug', 'about')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.pages.create'))
            ->assertOk()
            ->assertSee('contenteditable="true"', false);

        $this->actingAs($admin)
            ->get(route('admin.pages.edit', $about))
            ->assertOk()
            ->assertSee('contenteditable="true"', false);

        $this->actingAs($admin)
            ->put(route('admin.pages.update', $about), [
                'title' => 'درباره SillaGallery - ویرایش',
                'slug' => 'changed-about-path',
                'body' => '<p>متن به‌روزشده</p>',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.pages.edit', $about));

        $this->assertDatabaseHas('content_pages', [
            'id' => $about->id,
            'slug' => 'about',
            'title' => 'درباره SillaGallery - ویرایش',
        ]);

        $this->get('/about')->assertOk()->assertSee('درباره SillaGallery - ویرایش');
    }

    public function test_public_storefront_routes_use_sillagallery_brand_without_internal_livora_namespace(): void
    {
        foreach (['/', '/shop', '/categories', '/about', '/terms', '/blog', '/contact', '/login', '/register', '/cart'] as $path) {
            $response = $this->get($path)->assertOk();
            $html = strtolower((string) $response->getContent());

            $this->assertStringContainsString('sillagallery', $html, $path . ' must identify SillaGallery');
            $this->assertStringNotContainsString('livora', $html, $path . ' must not expose Livora');
            $this->assertStringNotContainsString('silagallery', $html, $path . ' must not expose the misspelled brand');
            $this->assertStringNotContainsString('data-livora-', $html, $path . ' must not expose old frontend hooks');
        }
    }

}
