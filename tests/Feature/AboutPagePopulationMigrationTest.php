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
        $this->assertStringContainsString('خرید خوب', $page->body);
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

    public function test_it_is_safe_when_the_content_pages_table_does_not_exist(): void
    {
        $migration = require database_path('migrations/2026_11_13_130000_populate_sillagallery_about_page.php');

        $migration->up();

        $this->assertFalse(Schema::hasTable('content_pages'));
    }
}
