<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Services\Media\ProductImageVariantGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_variants_are_generated_from_the_original_without_overwriting_it(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('original-sofa.jpg', 1200, 1500);
        $path = $file->store('products/images', 'public');
        $originalHash = hash('sha256', Storage::disk('public')->get($path));

        $media = Media::query()->create([
            'user_id' => $user->id,
            'disk' => 'public',
            'path' => $path,
            'filename' => basename($path),
            'original_name' => 'original-sofa.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => $file->getSize(),
            'alt' => 'Sofa',
        ]);

        $generated = app(ProductImageVariantGenerator::class)->generate($media);

        $this->assertSame($originalHash, hash('sha256', Storage::disk('public')->get($path)));
        Storage::disk('public')->assertExists($generated->thumbnail_path);
        Storage::disk('public')->assertExists($generated->card_path);
        Storage::disk('public')->assertExists($generated->detail_path);
        $this->assertStringEndsWith('.webp', $generated->card_path);
        $this->assertSame($path, $generated->path);
    }

    public function test_variant_generation_rejects_images_below_product_minimum_dimensions(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('too-small.jpg', 320, 400);
        $path = $file->store('products/images', 'public');
        $media = Media::query()->create([
            'user_id' => $user->id,
            'disk' => 'public',
            'path' => $path,
            'filename' => basename($path),
            'original_name' => 'too-small.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => $file->getSize(),
        ]);

        $this->expectException(\RuntimeException::class);
        app(ProductImageVariantGenerator::class)->generate($media);
    }
}
