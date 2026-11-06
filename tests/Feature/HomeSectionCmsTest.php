<?php

namespace Tests\Feature;

use App\Models\HomeSection;
use App\Models\User;
use Database\Seeders\InitialDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
