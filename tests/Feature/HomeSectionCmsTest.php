<?php

namespace Tests\Feature;

use App\Models\HomeSection;
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
        $this->assertSame('اسلاید دوم', $savedSlides[1]['title']);
        Storage::disk('public')->assertExists($savedSlides[0]['image_path']);
        Storage::disk('public')->assertExists($savedSlides[1]['image_path']);
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
