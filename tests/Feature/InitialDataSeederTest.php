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
        $this->assertDatabaseCount('products', 16);
        $this->assertDatabaseCount('blog_posts', 6);
        $this->assertDatabaseCount('contact_settings', 1);
        $this->assertDatabaseHas('content_pages', ['slug' => 'buying-guide']);

        $this->assertDatabaseMissing('products', [
            'status' => 'active',
            'sku' => 'SG-SOF-101',
        ]);
    }

    public function test_initial_data_seeder_is_idempotent_and_does_not_overwrite_existing_records(): void
    {
        $this->artisan('db:seed', ['--class' => InitialDataSeeder::class])
            ->assertExitCode(0);

        $this->artisan('db:seed', ['--class' => InitialDataSeeder::class])
            ->assertExitCode(0);

        $this->assertDatabaseCount('categories', 8);
        $this->assertDatabaseCount('products', 16);
        $this->assertDatabaseCount('blog_posts', 6);

        $this->assertDatabaseHas('products', [
            'slug' => 'مبل-راحتی-سه-نفره-آرتا',
            'status' => 'draft',
            'price' => 78000000,
        ]);
    }
}
