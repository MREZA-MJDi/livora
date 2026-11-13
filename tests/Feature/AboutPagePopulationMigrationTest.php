<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AboutPagePopulationMigrationTest extends TestCase
{
    private function createContentPagesTable(): void
    {
        Schema::create('content_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_populates_an_empty_about_page_without_publishing_it(): void
    {
        $this->createContentPagesTable();

        DB::table('content_pages')->insert([
            'title' => 'درباره ما',
            'slug' => 'about',
            'body' => null,
            'status' => 'draft',
            'published_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_13_130000_populate_sillagallery_about_page.php');
        $migration->up();

        $page = DB::table('content_pages')->where('slug', 'about')->first();

        $this->assertNotEmpty($page->body);
        $this->assertStringContainsString('خانه، با انتخاب‌هایی شکل می‌گیرد', $page->body);
        $this->assertNotEmpty($page->excerpt);
        $this->assertNotEmpty($page->meta_title);
        $this->assertNotEmpty($page->meta_description);
        $this->assertSame('draft', $page->status);
        $this->assertNull($page->published_at);
    }

    public function test_it_never_overwrites_existing_about_page_content(): void
    {
        $this->createContentPagesTable();

        DB::table('content_pages')->insert([
            'title' => 'درباره ما',
            'slug' => 'about',
            'body' => '<p>متن اختصاصی مدیر فروشگاه</p>',
            'excerpt' => 'خلاصه ثبت‌شده',
            'meta_title' => 'عنوان اختصاصی',
            'meta_description' => 'توضیح اختصاصی',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_13_130000_populate_sillagallery_about_page.php');
        $migration->up();

        $page = DB::table('content_pages')->where('slug', 'about')->first();

        $this->assertSame('<p>متن اختصاصی مدیر فروشگاه</p>', $page->body);
        $this->assertSame('خلاصه ثبت‌شده', $page->excerpt);
        $this->assertSame('عنوان اختصاصی', $page->meta_title);
        $this->assertSame('توضیح اختصاصی', $page->meta_description);
        $this->assertSame('published', $page->status);
    }

    public function test_it_upgrades_only_the_original_seed_about_copy(): void
    {
        $this->createContentPagesTable();

        DB::table('content_pages')->insert([
            'title' => 'درباره SillaGallery',
            'slug' => 'about',
            'body' => '<h1>درباره SillaGallery</h1><p>SillaGallery با تمرکز بر مبلمان و عناصر خانه، تلاش می‌کند انتخاب محصول را به تجربه‌ای روشن، دقیق و دلنشین تبدیل کند.</p><h2>نگاه ما</h2><p>ما باور داریم انتخاب خوب فقط به ظاهر محصول محدود نمی‌شود؛ کیفیت متریال، تناسب با فضا، کاربرد روزمره و دوام در طول زمان نیز اهمیت دارند.</p><h2>چیزهایی که برای ما مهم‌اند</h2><ul><li><strong>طراحی:</strong> فرم و ظاهر باید در خدمت فضای زندگی باشد.</li><li><strong>کیفیت:</strong> انتخاب متریال و ساخت خوب، ارزش محصول را در طول زمان حفظ می‌کند.</li><li><strong>شفافیت:</strong> مشخصات، قیمت، موجودی و شرایط خرید باید روشن باشند.</li><li><strong>تجربه:</strong> مسیر کشف، بررسی و سفارش باید ساده و قابل‌اعتماد باشد.</li></ul><h2>از کشف تا انتخاب</h2><p>در فروشگاه می‌توانید مجموعه‌ها را بررسی کنید، مشخصات محصولات را بخوانید و شرایط خرید را پیش از ثبت سفارش ببینید.</p><p>برای دریافت راهنمایی بیشتر از <a href="/contact">صفحه تماس با ما</a> با ما در ارتباط باشید.</p>',
            'excerpt' => 'با نگاه، ارزش‌ها و رویکرد SillaGallery در انتخاب مبلمان و عناصر خانه آشنا شوید.',
            'meta_title' => 'درباره SillaGallery | مبلمان و لوازم خانه',
            'meta_description' => 'با داستان، رویکرد و ارزش‌های SillaGallery در انتخاب مبلمان و عناصر خانه آشنا شوید.',
            'status' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_13_130000_populate_sillagallery_about_page.php');
        $migration->up();

        $page = DB::table('content_pages')->where('slug', 'about')->first();

        $this->assertStringContainsString('خانه، با انتخاب‌هایی شکل می‌گیرد', $page->body);
        $this->assertStringContainsString('کیفیت متریال و کاربرد', $page->body);
        $this->assertSame('published', $page->status);
    }

    public function test_it_is_safe_when_the_content_pages_table_does_not_exist(): void
    {
        $migration = require database_path('migrations/2026_11_13_130000_populate_sillagallery_about_page.php');

        $migration->up();

        $this->assertFalse(Schema::hasTable('content_pages'));
    }
}
