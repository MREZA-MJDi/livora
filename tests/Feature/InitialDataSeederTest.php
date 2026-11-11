<?php

namespace Tests\Feature;

use Database\Seeders\InitialDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitialDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_data_seeder_populates_catalogue_content_and_blog_without_demo_seeders(): void
    {
        $this->artisan('db:seed', ['--class' => InitialDataSeeder::class])
            ->assertExitCode(0);

        $this->assertDatabaseCount('categories', 8);
        $this->assertDatabaseCount('products', 17);
        $this->assertDatabaseCount('blog_posts', 6);
        $this->assertDatabaseCount('contact_settings', 1);
        $this->assertDatabaseHas('content_pages', ['slug' => 'buying-guide']);
        $this->assertDatabaseHas('content_pages', [
            'slug' => 'about',
            'status' => 'published',
            'featured_image' => null,
        ]);
        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'choosing-sofa-for-living-room',
            'status' => 'published',
            'featured_image' => null,
        ]);
        $this->assertDatabaseCount('blog_tags', 4);

        $this->assertDatabaseHas('products', [
            'status' => 'active',
            'sku' => 'SG-SOF-101',
        ]);

        $this->assertSame(17, \App\Models\Product::query()->active()->count());
    }

    public function test_initial_data_seeder_is_idempotent_and_does_not_overwrite_existing_records(): void
    {
        $this->artisan('db:seed', ['--class' => InitialDataSeeder::class])
            ->assertExitCode(0);

        $this->artisan('db:seed', ['--class' => InitialDataSeeder::class])
            ->assertExitCode(0);

        $this->assertDatabaseCount('categories', 8);
        $this->assertDatabaseCount('products', 17);
        $this->assertDatabaseCount('blog_posts', 6);
        $this->assertDatabaseCount('blog_tags', 4);
        $this->assertDatabaseHas('content_pages', ['slug' => 'about']);

        $this->assertDatabaseHas('products', [
            'slug' => 'مبل-راحتی-سه-نفره-آرتا',
            'status' => 'active',
            'price' => 78000000,
        ]);
    }
}
