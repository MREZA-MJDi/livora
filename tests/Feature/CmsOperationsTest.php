<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\ContactSetting;
use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CmsOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_admin_can_manage_contact_details_and_faqs_render_on_public_contact_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.contact-settings.edit'))
            ->assertOk()
            ->assertSee('پرسش‌های متداول صفحه تماس');

        $this->actingAs($admin)
            ->put(route('admin.contact-settings.update'), [
                'phone' => '+989121234567',
                'email' => 'support@sillashop.com',
                'address' => 'تهران، خیابان نمونه',
                'hours' => 'شنبه تا پنجشنبه، ۹ تا ۱۸',
                'phone_label' => 'تماس با فروشگاه',
                'email_label' => 'ایمیل پشتیبانی',
                'support_label' => 'پشتیبانی خرید',
                'online_label' => 'ارتباط آنلاین',
                'support_description' => 'راهنمایی پیش از خرید',
                'online_description' => 'از فرم تماس پیام بفرستید',
                'is_active' => '1',
                'faqs' => [
                    ['question' => 'چطور سفارش ثبت کنم؟', 'answer' => 'محصول را به سبد خرید اضافه کنید.'],
                ],
                'page_content' => [
                    'hero_title' => 'با تیم ما صحبت کن',
                    'hero_highlight' => 'همیشه همراه شما',
                    'faq_title' => 'سوالات مشتریان',
                ],
            ])
            ->assertRedirect(route('admin.contact-settings.edit'));

        $settings = ContactSetting::query()->firstOrFail();
        $this->assertSame('support@sillashop.com', $settings->email);
        $this->assertSame('چطور سفارش ثبت کنم؟', $settings->faqs[0]['question']);

        $this->assertDatabaseHas('seo_settings', [
            'id' => 1,
            'contact_email' => 'support@sillashop.com',
            'contact_phone' => '+989121234567',
        ]);

        Cache::forget('seo_settings.current');

        $this->get('/contact')
            ->assertOk()
            ->assertSee('تماس با فروشگاه')
            ->assertSee('+989121234567')
            ->assertSee('با تیم ما صحبت کن')
            ->assertSee('سوالات مشتریان')
            ->assertSee('چطور سفارش ثبت کنم؟')
            ->assertSee('محصول را به سبد خرید اضافه کنید.')
            ->assertSee('"@type":"FAQPage"', false);
    }

    public function test_contact_settings_and_seo_audit_are_admin_only(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('admin.contact-settings.edit'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.seo.audit'))
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.seo.audit'))
            ->assertOk()
            ->assertSee('ممیزی فنی SEO')
            ->assertSee('بدون Meta Title')
            ->assertSee('Google Search Console');
    }

    public function test_storefront_settings_hide_unconfigured_channels_and_enable_only_configured_floating_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.contact-settings.update'), [
                'brand_name' => 'برند آزمایشی',
                'phone_label' => 'تماس',
                'email_label' => 'ایمیل',
                'support_label' => 'پشتیبانی',
                'online_label' => 'ارتباط آنلاین',
                'whatsapp_url' => 'https://wa.me/989121234567',
                'floating_actions_enabled' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.contact-settings.edit'));

        $this->assertDatabaseHas('contact_settings', [
            'brand_name' => 'برند آزمایشی',
            'whatsapp_url' => 'https://wa.me/989121234567',
            'floating_actions_enabled' => true,
        ]);

        Cache::forget('storefront.settings');

        $this->get('/')
            ->assertOk()
            ->assertSee('برند آزمایشی')
            ->assertSee('واتساپ')
            ->assertDontSee('تلگرام');
    }

    public function test_contact_message_is_not_marked_replied_when_production_mailer_is_not_configured(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $message = ContactMessage::query()->create([
            'name' => 'مشتری تست',
            'phone' => '09120000000',
            'email' => 'customer@example.com',
            'subject' => 'other',
            'message' => 'لطفاً درباره محصول راهنمایی کنید.',
            'status' => 'unread',
        ]);

        config(['mail.default' => 'log']);

        $this->actingAs($admin)
            ->patch(route('admin.contact-messages.reply', $message), [
                'admin_reply' => 'پاسخ تست',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('contact_messages', [
            'id' => $message->id,
            'status' => 'unread',
            'admin_reply' => null,
        ]);
    }

    public function test_contact_message_reply_is_saved_only_after_mail_transport_accepts_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $message = ContactMessage::query()->create([
            'name' => 'مشتری تست',
            'phone' => '09120000000',
            'email' => 'customer@example.com',
            'subject' => 'other',
            'message' => 'لطفاً درباره محصول راهنمایی کنید.',
            'status' => 'read',
        ]);

        config(['mail.default' => 'smtp']);
        Mail::shouldReceive('raw')->once();

        $this->actingAs($admin)
            ->patch(route('admin.contact-messages.reply', $message), [
                'admin_reply' => 'پاسخ کارشناسی به پیام شما',
            ])
            ->assertRedirect(route('admin.contact-messages.show', $message))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'id' => $message->id,
            'status' => 'replied',
            'admin_reply' => 'پاسخ کارشناسی به پیام شما',
        ]);
        $this->assertNotNull($message->fresh()->replied_at);
    }

    public function test_sitemap_requires_record_level_index_and_follow_permissions(): void
    {
        SeoSetting::current()->update([
            'indexing_enabled' => true,
            'following_enabled' => true,
        ]);
        Cache::forget('seo_settings.current');

        BlogPost::query()->create([
            'title' => 'Post blocked by follow',
            'slug' => 'post-blocked-by-follow',
            'body' => 'Content',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'seo_index' => true,
            'seo_follow' => false,
        ]);

        BlogPost::query()->create([
            'title' => 'Post allowed in sitemap',
            'slug' => 'post-allowed-in-sitemap',
            'body' => 'Content',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'seo_index' => true,
            'seo_follow' => true,
        ]);

        $response = $this->get('/sitemap.xml')->assertOk();
        ob_start();
        $response->baseResponse->sendContent();
        $xml = (string) ob_get_clean();

        $this->assertStringContainsString('/blog/post-allowed-in-sitemap', $xml);
        $this->assertStringNotContainsString('/blog/post-blocked-by-follow', $xml);
    }

    public function test_blog_create_and_edit_forms_use_jalali_calendar_not_gregorian_datetime_input(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.blog.create'))
            ->assertOk()
            ->assertSee('زمان انتشار (تقویم شمسی)')
            ->assertSee('jalaliDatePicker', false)
            ->assertSee('تقویم و تاریخ نمایشی شمسی')
            ->assertDontSee('datetime-local');

        $post = BlogPost::query()->create([
            'user_id' => $admin->id,
            'title' => 'مقاله زمان‌بندی شمسی',
            'slug' => 'jalali-blog-calendar',
            'body' => 'متن مقاله',
            'status' => 'draft',
            'published_at' => now(),
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.blog.edit', $post))
            ->assertOk()
            ->assertSee('jalaliDatePicker', false)
            ->assertDontSee('datetime-local');
    }

    public function test_admin_blog_index_displays_published_at_in_jalali_calendar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        BlogPost::query()->create([
            'user_id' => $admin->id,
            'title' => 'مقاله تاریخ شمسی',
            'slug' => 'jalali-blog-index-date',
            'body' => 'متن مقاله',
            'status' => 'published',
            'published_at' => \Carbon\Carbon::parse('2025-03-21 12:35:00', 'Asia/Tehran'),
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.blog.index'))
            ->assertOk()
            ->assertSee('۱۴۰۴/۰۱/۰۱ ۱۲:۳۵');
    }

    public function test_published_blog_article_emits_blog_posting_structured_data(): void
    {
        BlogPost::query()->create([
            'title' => 'راهنمای انتخاب مبل',
            'slug' => 'furniture-selection-guide',
            'excerpt' => 'راهنمای واقعی انتخاب مبلمان',
            'body' => '<p>محتوای مقاله</p>',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $this->get('/blog/furniture-selection-guide')
            ->assertOk()
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('"headline":"راهنمای انتخاب مبل"', false);
    }
}
