<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentPage;
use App\Models\ContactSetting;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\InitialStoreContentSeeder;
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

    public function test_contact_page_uses_dashboard_contact_details_and_page_content(): void
    {
        ContactSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'is_active' => true,
                'phone' => '02112345678',
                'phone_label' => 'خط اختصاصی فروشگاه',
                'email' => 'support@example.test',
                'email_label' => 'ایمیل پشتیبانی فروشگاه',
                'support_label' => 'پشتیبانی',
                'online_label' => 'ارتباط آنلاین',
                'page_content' => [
                    'hero_kicker' => 'متن داشبورد',
                    'hero_title' => 'عنوان اختصاصی صفحه تماس',
                    'hero_highlight' => 'پاسخ‌گویی واقعی',
                    'hero_description' => 'توضیح ذخیره‌شده در تنظیمات تماس',
                    'hero_cta' => 'ارسال درخواست',
                    'info_title' => 'اطلاعات ثبت‌شده فروشگاه',
                ],
                'faqs' => [],
            ]
        );

        $this->get('/contact')
            ->assertOk()
            ->assertSee('عنوان اختصاصی صفحه تماس')
            ->assertSee('توضیح ذخیره‌شده در تنظیمات تماس')
            ->assertSee('خط اختصاصی فروشگاه')
            ->assertSee('02112345678')
            ->assertDontSee('برای انتخاب بهتر،');
    }

    public function test_about_page_uses_the_published_content_page_managed_by_the_admin(): void
    {
        ContentPage::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'داستان واقعی برند از CMS',
                'excerpt' => 'خلاصه ثبت‌شده در داشبورد',
                'body' => '<p>متن مدیریت‌شده درباره ما</p>',
                'status' => 'published',
                'published_at' => now()->subMinute(),
                'seo_index' => true,
                'seo_follow' => true,
            ]
        );

        $this->get('/about')
            ->assertOk()
            ->assertSee('داستان واقعی برند از CMS')
            ->assertSee('متن مدیریت‌شده درباره ما')
            ->assertDontSee('خانه فقط یک فضا نیست.');
    }

    public function test_admin_managed_about_page_image_is_the_image_rendered_on_the_public_page(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $page = ContentPage::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'user_id' => $admin->id,
                'title' => 'درباره برند',
                'body' => '<p>محتوای مدیریت‌شده درباره ما</p>',
                'status' => 'published',
                'published_at' => now()->subMinute(),
                'seo_index' => true,
                'seo_follow' => true,
            ]
        );

        $this->actingAs($admin)
            ->put(route('admin.pages.update', $page), [
                'title' => 'درباره برند از داشبورد',
                'slug' => 'about',
                'excerpt' => 'معرفی مدیریت‌شده',
                'body' => '<p>محتوای نهایی صفحه درباره ما</p>',
                'status' => 'published',
                'published_at' => now()->subMinute()->toISOString(),
                'featured_image' => UploadedFile::fake()->image('about-managed.webp', 1200, 675),
                'featured_image_alt' => 'تصویر مدیریت‌شده درباره برند',
                'seo_index' => '1',
                'seo_follow' => '1',
            ])
            ->assertRedirect(route('admin.pages.edit', $page));

        $page->refresh()->load('media');
        $imageUrl = $page->featured_image_url;
        $this->assertNotEmpty($imageUrl);

        $this->get('/about')
            ->assertOk()
            ->assertSee('درباره برند از داشبورد')
            ->assertSee('تصویر مدیریت‌شده درباره برند')
            ->assertSee($imageUrl, false);
    }

    public function test_about_page_does_not_render_a_missing_media_file_as_a_broken_image(): void
    {
        $media = Media::query()->create([
            'disk' => 'public',
            'path' => 'pages/missing-about-image.webp',
            'filename' => 'missing-about-image.webp',
            'original_name' => 'missing-about-image.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 10,
        ]);

        $this->assertSame('', $media->url);

        ContentPage::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'درباره با رسانه ناموجود',
                'body' => '<p>محتوای صفحه</p>',
                'featured_image' => 'pages/missing-about-image.webp',
                'media_id' => $media->id,
                'status' => 'published',
                'published_at' => now()->subMinute(),
            ]
        );

        $this->get('/about')
            ->assertOk()
            ->assertDontSee('missing-about-image.webp');
    }

    public function test_about_page_seeder_does_not_restore_an_image_removed_by_an_administrator(): void
    {
        ContentPage::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'درباره قابل ویرایش',
                'body' => '<p>محتوای مدیر</p>',
                'featured_image' => null,
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        $this->seed(InitialStoreContentSeeder::class);

        $this->assertNull(ContentPage::query()->where('slug', 'about')->value('featured_image'));
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
