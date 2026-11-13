<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_publish_a_content_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.pages.create'))
            ->assertOk()
            ->assertSee('متن صفحه')
            ->assertSee('تصویر شاخص');

        $this->actingAs($admin)
            ->post(route('admin.pages.store'), [
                'title' => 'راهنمای انتخاب میز',
                'slug' => 'table-selection-guide',
                'excerpt' => 'راهنمای بررسی میز متناسب با فضا',
                'body' => '<h2>انتخاب میز</h2><script>alert(1)</script><p>ابعاد و کاربرد را بررسی کنید.</p>',
                'status' => 'published',
                'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                'meta_title' => 'راهنمای انتخاب میز',
                'meta_description' => 'نکات انتخاب میز برای فضای خانه',
                'seo_index' => '1',
                'seo_follow' => '1',
            ])
            ->assertRedirect(route('admin.pages.index'));

        $page = ContentPage::query()->where('slug', 'table-selection-guide')->firstOrFail();

        $this->assertSame('published', $page->status);
        $this->assertStringContainsString('ابعاد و کاربرد', $page->body);
        $this->assertStringNotContainsString('<script', $page->body);

        $this->get('/table-selection-guide')
            ->assertOk()
            ->assertSee('راهنمای انتخاب میز')
            ->assertSee('ابعاد و کاربرد');
    }

    public function test_admin_can_preview_a_draft_page_without_publishing_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = ContentPage::query()->create([
            'user_id' => $admin->id,
            'title' => 'پیش‌نمایش صفحه پیش‌نویس',
            'slug' => 'draft-preview-test',
            'body' => '<h2>محتوای آزمایشی</h2><p>این صفحه هنوز منتشر نشده است.</p>',
            'status' => 'draft',
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pages.preview', $page))
            ->assertOk()
            ->assertSee('پیش‌نمایش صفحه پیش‌نویس')
            ->assertSee('این صفحه هنوز منتشر نشده است.')
            ->assertSee('noindex, nofollow', false);

        $this->assertSame('draft', $page->fresh()->status);
        $this->get('/draft-preview-test')->assertNotFound();
    }

    public function test_admin_can_save_the_same_content_page_more_than_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = ContentPage::query()->create([
            'user_id' => $admin->id,
            'title' => 'صفحه آزمایشی',
            'slug' => 'repeat-save-page',
            'body' => '<p>نسخه اول</p>',
            'status' => 'draft',
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $payload = [
            'title' => 'صفحه آزمایشی',
            'slug' => 'repeat-save-page',
            'excerpt' => 'خلاصه صفحه',
            'meta_title' => 'عنوان صفحه',
            'meta_description' => 'توضیحات صفحه',
            'status' => 'published',
            'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'seo_index' => '1',
            'seo_follow' => '1',
        ];

        $this->actingAs($admin)
            ->put(route('admin.pages.update', $page), $payload + [
                'body' => '<h2>نسخه دوم</h2><p>ذخیره اول</p>',
            ])
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertStringContainsString('ذخیره اول', $page->fresh()->body);

        $this->put(route('admin.pages.update', $page), $payload + [
            'body' => '<h2>نسخه سوم</h2><p>ذخیره دوم</p>',
        ])->assertRedirect(route('admin.pages.edit', $page));

        $this->assertStringContainsString('ذخیره دوم', $page->fresh()->body);
        $this->assertSame('published', $page->fresh()->status);

        $this->get('/repeat-save-page')
            ->assertOk()
            ->assertSee('نسخه سوم')
            ->assertSee('ذخیره دوم');
    }
}
