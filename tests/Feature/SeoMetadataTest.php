<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SeoPageSetting;
use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

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

    public function test_both_global_seo_switches_are_required_before_any_public_page_can_be_indexed(): void
    {
        SeoPageSetting::query()
            ->where('route_key', 'contact')
            ->firstOrFail()
            ->update(['seo_index' => true, 'seo_follow' => true]);
        Cache::forget('seo_page_settings.contact');

        foreach ([[true, false], [false, true]] as [$indexingEnabled, $followingEnabled]) {
            SeoSetting::current()->update([
                'indexing_enabled' => $indexingEnabled,
                'following_enabled' => $followingEnabled,
            ]);
            Cache::forget('seo_settings.current');

            $this->get('/contact')
                ->assertOk()
                ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
    }

    public function test_sitemap_stays_empty_when_only_one_global_seo_switch_is_enabled(): void
    {
        SeoSetting::current()->update([
            'indexing_enabled' => true,
            'following_enabled' => false,
        ]);
        Cache::forget('seo_settings.current');

        SeoPageSetting::query()
            ->where('route_key', 'home')
            ->firstOrFail()
            ->update(['seo_index' => true]);
        Cache::forget('seo_page_settings.home');

        $sitemap = $this->get('/sitemap.xml')->assertOk();
        ob_start();
        $sitemap->baseResponse->sendContent();
        $xml = (string) ob_get_clean();

        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringNotContainsString('<url>', $xml);
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

    public function test_canonical_and_open_graph_urls_are_pinned_to_sillagallery_origin_not_inbound_host(): void
    {
        config(['app.url' => 'https://sillashop.com']);

        SeoPageSetting::query()
            ->where('route_key', 'home')
            ->firstOrFail()
            ->update(['canonical_url' => 'https://farajihome.com/catalog/sale']);
        Cache::forget('seo_page_settings.home');

        $this->withServerVariables([
            'HTTP_HOST' => 'farajihome.com',
            'HTTPS' => 'on',
        ])->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://sillashop.com/catalog/sale">', false)
            ->assertSee('<meta property="og:url" content="https://sillashop.com/catalog/sale">', false)
            ->assertSee('"url":"https://sillashop.com/"', false)
            ->assertSee('"target":"https://sillashop.com/shop?search={search_term_string}"', false)
            ->assertDontSee('<meta property="og:url" content="https://farajihome.com/catalog/sale">');
    }

    public function test_public_storefront_routes_use_sillagallery_brand_without_internal_livora_namespace(): void
    {
        $routes = ['/', '/shop', '/categories', '/about', '/terms', '/blog', '/contact', '/login', '/register', '/cart'];

        foreach ($routes as $path) {
            $response = $this->get($path)->assertOk();
            $html = (string) $response->getContent();

            $this->assertStringContainsString('SillaGallery', $html, $path . ' must identify SillaGallery');
            $this->assertDoesNotMatchRegularExpression('/SilaGallery/i', $html, $path . ' exposes a misspelled public brand');
            $this->assertDoesNotMatchRegularExpression('/Livora/i', $html, $path . ' exposes the internal project name');
        }
    }


    public function test_seo_dashboard_is_admin_only_and_admin_can_upload_the_brand_social_image(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->get(route('admin.seo.edit'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->put(route('admin.seo.update'), [
                'site_name' => 'SillaGallery',
                'default_og_image' => UploadedFile::fake()->image('customer-og.jpg', 1200, 630),
            ])
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.seo.edit'))
            ->assertOk()
            ->assertSee('name="default_og_image"', false);

        $this->actingAs($admin)
            ->put(route('admin.seo.update'), [
                'site_name' => 'SillaGallery',
                'default_title' => 'SillaGallery | مبلمان و خانه',
                'default_description' => 'تصویر و توضیحات رسمی اشتراک‌گذاری SillaGallery.',
                'organization_name' => 'SillaGallery',
                'default_og_image' => UploadedFile::fake()->image('sillagallery-og.jpg', 1200, 630),
            ])
            ->assertRedirect(route('admin.seo.edit'));

        $settings = SeoSetting::current()->fresh();
        $this->assertNotEmpty($settings->default_og_image);
        Storage::disk('public')->assertExists($settings->default_og_image);

        $imageUrl = Storage::disk('public')->url($settings->default_og_image);
        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:image" content="' . $imageUrl . '"', false)
            ->assertSee('<meta name="twitter:image" content="' . $imageUrl . '">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }


}
