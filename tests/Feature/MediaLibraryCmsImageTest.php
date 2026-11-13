<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryCmsImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_rich_text_editor_uploads_are_registered_in_the_media_library(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson(route('admin.editor-images.store'), [
            'image' => UploadedFile::fake()->image('article-inline.png', 640, 480),
            'alt' => 'تصویر داخل مقاله',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'article-inline.png')
            ->assertJsonPath('alt', 'تصویر داخل مقاله');

        $mediaId = $response->json('id');
        $media = Media::query()->findOrFail($mediaId);

        $this->assertSame('editor', dirname($media->path));
        $this->assertSame('image/png', $media->mime_type);
        Storage::disk('public')->assertExists($media->path);
        $this->assertSame($media->url, $response->json('url'));
    }

    public function test_media_used_inside_rich_text_content_cannot_be_deleted(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        Storage::disk('public')->put('editor/inline-image.png', 'image-bytes');

        $media = Media::query()->create([
            'user_id' => $admin->id,
            'disk' => 'public',
            'path' => 'editor/inline-image.png',
            'filename' => 'inline-image.png',
            'original_name' => 'inline-image.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => 12,
            'alt' => 'تصویر در متن صفحه',
        ]);

        ContentPage::query()->create([
            'user_id' => $admin->id,
            'title' => 'صفحه آزمایشی',
            'slug' => 'media-usage-test',
            'body' => '<p>متن</p><img src="'.asset('storage/'.$media->path).'" alt="تصویر">',
            'status' => 'draft',
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.media.destroy', $media))
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->path);
    }
}
