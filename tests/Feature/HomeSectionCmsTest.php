<?php

namespace Tests\Feature;

use App\Models\HomeSection;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Database\Seeders\InitialDataSeeder;
use Database\Seeders\HomepageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeSectionCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_rendered_from_enabled_database_sections(): void
    {
        $this->seed(InitialDataSeeder::class);

        $hero = HomeSection::query()->where('key', 'hero')->firstOrFail();
        $this->assertTrue($hero->enabled);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($hero->title)
            ->assertSee('انتخاب دقیق')
            ->assertSee('برای خانه‌تان انتخابی ماندگار پیدا کنید');

        $hero->update(['enabled' => false]);
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee($hero->title);
    }

    public function test_admin_can_manage_and_persist_home_section_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $first = HomeSection::query()->where('key', 'hero')->firstOrFail();
        $second = HomeSection::query()->where('key', 'categories')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.home-sections.index'))
            ->assertOk()
            ->assertSee('سکشن‌های صفحه اصلی');

        $this->actingAs($admin)
            ->post(route('admin.home-sections.reorder'), [
                'order' => [$second->id, $first->id],
            ])
            ->assertRedirect();

        $this->assertSame(10, $second->fresh()->sort_order);
        $this->assertSame(20, $first->fresh()->sort_order);
    }


    public function test_admin_can_save_multiple_cropped_hero_slides_and_their_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $hero = HomeSection::query()->where('key', 'hero')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.home-sections.update', $hero), [
                'key' => 'hero',
                'type' => 'hero',
                'enabled' => '1',
                'payload' => [
                    'slides' => [
                        [
                            'eyebrow' => 'سیلا گالری',
                            'title' => 'اسلاید اول',
                            'subtitle' => 'توضیح اول',
                            'price_label' => 'شروع از ۱۲ میلیون تومان',
                            'cta_label' => 'مشاهده',
                            'cta_url' => '/shop',
                            'image_path' => '',
                            'object_position' => '50% 50%',
                        ],
                        [
                            'eyebrow' => 'مجموعه جدید',
                            'title' => 'اسلاید دوم',
                            'subtitle' => 'توضیح دوم',
                            'cta_label' => 'کشف مجموعه',
                            'cta_url' => '/categories',
                            'image_path' => '',
                            'object_position' => '50% 50%',
                        ],
                    ],
                ],
                'slide_images' => [
                    0 => UploadedFile::fake()->image('hero-one.jpg', 1200, 800),
                    1 => UploadedFile::fake()->image('hero-two.jpg', 1200, 800),
                ],
            ])
            ->assertRedirect(route('admin.home-sections.index'));

        $savedSlides = $hero->fresh()->payload['slides'];
        $this->assertCount(2, $savedSlides);
        $this->assertSame('اسلاید اول', $savedSlides[0]['title']);
        $this->assertSame('شروع از ۱۲ میلیون تومان', $savedSlides[0]['price_label']);
        $this->assertSame('اسلاید دوم', $savedSlides[1]['title']);
        Storage::disk('public')->assertExists($savedSlides[0]['image_path']);
        Storage::disk('public')->assertExists($savedSlides[1]['image_path']);
    }

    public function test_admin_hero_editor_uses_the_shared_storefront_preview_and_exposes_all_rendered_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $hero = HomeSection::query()->where('key', 'hero')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.home-sections.edit', $hero))
            ->assertOk()
            ->assertSee('sg-home-hero__layout', false)
            ->assertSee('sg-home-hero__media', false)
            ->assertSee('price_label', false)
            ->assertDontSee('پیش‌نمایش شناور');
    }

    public function test_homepage_renders_cms_hero_slide_controls_and_content(): void
    {
        $hero = HomeSection::query()->where('key', 'hero')->firstOrFail();
        $hero->update([
            'payload' => [
                'slides' => [
                    ['eyebrow' => 'اسلاید اول', 'title' => 'عنوان اول', 'subtitle' => 'توضیح اول', 'cta_label' => 'مشاهده', 'cta_url' => '/shop', 'image_path' => null],
                    ['eyebrow' => 'اسلاید دوم', 'title' => 'عنوان دوم', 'subtitle' => 'توضیح دوم', 'cta_label' => 'کشف', 'cta_url' => '/categories', 'image_path' => null],
                ],
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('عنوان اول')
            ->assertSee('data-sg-hero-dot="1"', false)
            ->assertSee('data-sg-hero-data', false);
    }

    public function test_homepage_does_not_emit_a_broken_url_for_a_missing_cms_slide_image(): void
    {
        Storage::fake('public');
        $hero = HomeSection::query()->where('key', 'hero')->firstOrFail();
        $hero->update([
            'payload' => [
                'slides' => [[
                    'title' => 'اسلاید بدون فایل تصویر',
                    'image_path' => 'home-sections/missing-slide.webp',
                ]],
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('اسلاید بدون فایل تصویر')
            ->assertDontSee('missing-slide.webp');
    }

    public function test_configured_cms_hero_slides_take_precedence_over_selected_hero_products(): void
    {
        $this->seed(InitialDataSeeder::class);

        $hero = HomeSection::query()->where('key', 'hero')->firstOrFail();
        $hero->update([
            'payload' => [
                'slides' => [[
                    'eyebrow' => 'محتوای مدیریت‌شده',
                    'title' => 'عنوان اختصاصی اسلاید CMS',
                    'subtitle' => 'این متن باید در صفحه اصلی باقی بماند.',
                    'cta_label' => 'مشاهده',
                    'cta_url' => '/shop',
                    'image_path' => null,
                ]],
            ],
        ]);

        $product = Product::query()->active()->firstOrFail();
        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => 'https://images.example.test/hero-product.webp',
            'alt' => 'تصویر محصول منتخب',
            'sort_order' => 0,
            'is_primary' => true,
        ]);
        $product->update([
            'is_home_hero' => true,
            'home_hero_sort_order' => 1,
        ]);

        $response = $this->get(route('home'))->assertOk();
        preg_match('/<script type="application\\/json" data-sg-hero-data>(.*?)<\\/script>/s', $response->getContent(), $matches);

        $this->assertNotEmpty($matches[1] ?? null);
        $slides = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('عنوان اختصاصی اسلاید CMS', $slides[0]['title']);
        $this->assertSame('این متن باید در صفحه اصلی باقی بماند.', $slides[0]['subtitle']);
    }

    public function test_homepage_seeder_preserves_admin_configured_hero_slides(): void
    {
        $hero = HomeSection::query()->where('key', 'hero')->firstOrFail();
        $slides = [
            ['title' => 'عنوان قابل حفظ', 'image_path' => 'home-sections/hero.jpg'],
        ];
        $hero->update(['payload' => ['slides' => $slides]]);

        $this->seed(HomepageContentSeeder::class);

        $this->assertSame($slides, $hero->fresh()->payload['slides']);
    }

    public function test_media_library_cannot_delete_an_image_referenced_by_a_homepage_hero_slide(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $path = 'home-sections/hero-protected.jpg';
        Storage::disk('public')->put($path, 'hero-image');

        $media = Media::query()->create([
            'user_id' => $admin->id,
            'disk' => 'public',
            'path' => $path,
            'filename' => 'hero-protected.jpg',
            'original_name' => 'hero-protected.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 10,
        ]);

        HomeSection::query()->create([
            'key' => 'hero-media-protection',
            'type' => 'hero',
            'enabled' => true,
            'sort_order' => 10,
            'payload' => [
                'slides' => [
                    ['title' => 'اسلاید متصل به رسانه', 'image_path' => $path],
                ],
            ],
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.media.destroy', $media))
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($path);
    }

    public function test_homepage_sections_can_be_disabled_without_deleting_their_content(): void
    {
        $section = HomeSection::query()->where('key', 'trust')->firstOrFail();
        $title = $section->title;

        $section->update(['enabled' => false]);
        $this->assertDatabaseHas('home_sections', [
            'id' => $section->id,
            'enabled' => false,
            'title' => $title,
        ]);
    }
}
