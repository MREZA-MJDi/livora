<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
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


    public function test_seo_route_picker_lists_real_public_destinations_and_excludes_private_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create([
            'name' => 'دسته مسیر عمومی',
            'slug' => 'public-route-category',
            'description' => 'دسته‌بندی قابل انتخاب',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.seo.public-routes', ['q' => 'دسته مسیر عمومی']))
            ->assertOk()
            ->assertJsonPath('data.0.label', 'دسته‌بندی: دسته مسیر عمومی');

        $this->assertStringContainsString('/categories/public-route-category', $response->json('data.0.url'));
        $this->assertStringNotContainsString('/admin', json_encode($response->json('data')));
        $this->assertStringNotContainsString('/checkout', json_encode($response->json('data')));
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


    public function test_admin_can_select_default_open_graph_image_from_media_library_and_media_cannot_be_deleted_while_in_use(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->image('library-og.jpg', 1200, 630);
        $path = $file->store('media', 'public');
        $media = Media::query()->create([
            'user_id' => $admin->id,
            'disk' => 'public',
            'path' => $path,
            'filename' => basename($path),
            'original_name' => 'library-og.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => Storage::disk('public')->size($path),
            'alt' => 'تصویر اشتراک‌گذاری',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.seo.update'), [
                'site_name' => 'SillaGallery',
                'default_title' => 'SillaGallery',
                'default_og_media_id' => $media->id,
            ])
            ->assertRedirect(route('admin.seo.edit'));

        $settings = SeoSetting::current()->fresh();
        $this->assertSame($media->id, (int) $settings->default_og_media_id);
        $this->assertSame($media->url, $settings->default_og_image_url);

        $this->delete(route('admin.media.destroy', $media))
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($path);
    }

    public function test_media_library_crop_endpoint_creates_a_separate_derivative_without_mutating_source_asset(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $source = UploadedFile::fake()->image('original.png', 1600, 900);
        $sourcePath = $source->store('media', 'public');
        $sourceMedia = Media::query()->create([
            'user_id' => $admin->id,
            'disk' => 'public',
            'path' => $sourcePath,
            'filename' => basename($sourcePath),
            'original_name' => 'original.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => Storage::disk('public')->size($sourcePath),
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.media.crop'), [
                'image' => UploadedFile::fake()->image('original-cropped.png', 1200, 630),
                'alt' => 'برش مستقل تصویر Open Graph',
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'name', 'url', 'size']);

        $this->assertNotSame($sourceMedia->id, $response->json('id'));
        $this->assertDatabaseHas('media', ['id' => $sourceMedia->id, 'path' => $sourcePath]);
        $this->assertDatabaseHas('media', ['id' => $response->json('id'), 'alt' => 'برش مستقل تصویر Open Graph']);
        Storage::disk('public')->assertExists($sourcePath);
        Storage::disk('public')->assertExists('media/crops/'.$response->json('name'));
    }



}
