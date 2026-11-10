<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Media;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_read_update_and_soft_delete_a_product_with_primary_image_replacement(): void
    {
        $category = $this->createCategory('product-lifecycle-category');

        $this->actingAs($this->admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('تصویر اصلی محصول');

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->productPayload($category, [
                'name' => 'مبل چرمی',
                'slug' => 'leather-sofa-lifecycle',
                'sku' => 'SG-LIFECYCLE-001',
                'image' => UploadedFile::fake()->image('sofa-old.jpg', 800, 1000),
            ]))
            ->assertRedirect(route('admin.products.edit', Product::query()->where('slug', 'leather-sofa-lifecycle')->value('id')));

        $product = Product::query()->where('slug', 'leather-sofa-lifecycle')->firstOrFail();
        $oldImage = $product->images()->firstOrFail();

        $this->assertTrue($oldImage->is_primary);
        Storage::disk('public')->assertExists($oldImage->path);

        $this->actingAs($this->admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('مبل چرمی');

        $this->actingAs($this->admin)
            ->get(route('admin.products.show', $product))
            ->assertOk()
            ->assertSee('مبل چرمی');

        $this->actingAs($this->admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('تصویر اصلی محصول');

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->productPayload($category, [
                'name' => 'مبل چرمی ممتاز',
                'slug' => 'leather-sofa-lifecycle',
                'sku' => 'SG-LIFECYCLE-001',
                'price' => '14500000',
                'image' => UploadedFile::fake()->image('sofa-new.jpg', 800, 1000),
            ]))
            ->assertRedirect(route('admin.products.edit', $product));

        $product->refresh();
        $newImage = $product->images()->firstOrFail();

        $this->assertSame('مبل چرمی ممتاز', $product->name);
        $this->assertSame('14500000.00', $product->price);
        $this->assertTrue($newImage->is_primary);
        $this->assertNotSame($oldImage->path, $newImage->path);
        Storage::disk('public')->assertExists($oldImage->path); // Replaced assets remain in the Media Library until explicitly removed.
        Storage::disk('public')->assertExists($newImage->path);
        $this->assertNotNull($oldImage->fresh()->media_id);
        $this->assertNotNull($newImage->fresh()->media_id);

        $this->actingAs($this->admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }


    public function test_admin_can_remove_primary_image_and_banner_without_deleting_shared_media(): void
    {
        $category = $this->createCategory('product-media-removal-category');

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->productPayload($category, [
                'name' => 'مبل دارای بنر',
                'slug' => 'sofa-media-removal',
                'sku' => 'SG-MEDIA-REMOVE-001',
                'image' => UploadedFile::fake()->image('primary-before-remove.jpg', 800, 1000),
                'banner_image' => UploadedFile::fake()->image('banner-before-remove.jpg', 1200, 500),
                'banner_image_alt' => 'بنر محصول برای تست حذف',
            ]))
            ->assertRedirect(route('admin.products.edit', Product::query()->where('slug', 'sofa-media-removal')->value('id')));

        $product = Product::query()->where('slug', 'sofa-media-removal')->firstOrFail();
        $primary = $product->images()->firstOrFail();
        $primaryMedia = Media::query()->findOrFail($primary->media_id);
        $bannerMedia = $product->bannerMedia()->firstOrFail();

        $secondaryPath = 'products/images/secondary-sofa.webp';
        Storage::disk('public')->put($secondaryPath, 'secondary-image-content');
        $secondaryMedia = Media::query()->create([
            'user_id' => $this->admin->id,
            'disk' => 'public',
            'path' => $secondaryPath,
            'filename' => 'secondary-sofa.webp',
            'original_name' => 'secondary-sofa.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => Storage::disk('public')->size($secondaryPath),
            'alt' => 'تصویر دوم مبل',
        ]);
        $secondary = ProductImage::query()->create([
            'product_id' => $product->id,
            'media_id' => $secondaryMedia->id,
            'path' => $secondaryPath,
            'alt' => 'تصویر دوم مبل',
            'sort_order' => 1,
            'is_primary' => false,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->productPayload($category, [
                'name' => 'مبل دارای بنر',
                'slug' => 'sofa-media-removal',
                'sku' => 'SG-MEDIA-REMOVE-001',
                // The form can still post its preselected IDs when a removal checkbox is used.
                'media_id' => $primaryMedia->id,
                'banner_media_id' => $bannerMedia->id,
                'remove_primary_image' => '1',
                'remove_banner_image' => '1',
            ]))
            ->assertRedirect(route('admin.products.edit', $product));

        $product->refresh();
        $this->assertNull($product->banner_image);
        $this->assertNull($product->banner_media_id);
        $this->assertNull($product->banner_image_alt);

        $this->assertDatabaseMissing('product_images', ['id' => $primary->id]);
        $this->assertDatabaseHas('product_images', [
            'id' => $secondary->id,
            'is_primary' => true,
        ]);
        $this->assertDatabaseHas('media', ['id' => $primaryMedia->id]);
        $this->assertDatabaseHas('media', ['id' => $bannerMedia->id]);
        Storage::disk('public')->assertExists($primaryMedia->path);
        Storage::disk('public')->assertExists($bannerMedia->path);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee($secondaryMedia->url, false);
    }

    public function test_admin_can_reuse_a_library_image_and_published_product_appears_on_storefront(): void
    {
        $category = $this->createCategory('media-picker-category');
        $path = 'media/library-sofa.webp';
        Storage::disk('public')->put($path, 'fake-image-content');
        $media = Media::query()->create([
            'user_id' => $this->admin->id,
            'disk' => 'public',
            'path' => $path,
            'filename' => 'library-sofa.webp',
            'original_name' => 'library-sofa.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 19,
            'alt' => 'مبل در کتابخانه رسانه',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.media.picker', ['q' => 'library-sofa']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $media->id);

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->productPayload($category, [
                'name' => 'مبل متصل به کتابخانه',
                'slug' => 'media-library-sofa',
                'sku' => 'SG-MEDIA-001',
                'media_id' => $media->id,
                'status' => 'active',
            ]))
            ->assertRedirect(route('admin.products.edit', Product::query()->where('slug', 'media-library-sofa')->value('id')));

        $product = Product::query()->where('slug', 'media-library-sofa')->firstOrFail();
        $image = $product->images()->firstOrFail();

        $this->assertSame($media->id, $image->media_id);
        $this->assertSame($path, $image->path);
        Storage::disk('public')->assertExists($path);

        $this->get('/shop')->assertOk()->assertSee('مبل متصل به کتابخانه');
        $this->get('/')->assertOk()->assertSee('مبل متصل به کتابخانه');

        $this->actingAs($this->admin)
            ->delete(route('admin.media.destroy', $media))
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('error');

        Storage::disk('public')->assertExists($path);
    }

    public function test_admin_can_create_read_update_and_delete_categories_and_replace_category_images(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSee('اطلاعات اصلی');

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'اتاق نشیمن',
                'slug' => 'living-room-lifecycle',
                'description' => 'دسته‌بندی آزمایشی',
                'is_active' => '1',
                'sort_order' => '1',
                'image' => UploadedFile::fake()->image('category-old.jpg', 800, 800),
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('slug', 'living-room-lifecycle')->firstOrFail();
        $oldImage = $category->image;

        $this->assertNotEmpty($oldImage);
        Storage::disk('public')->assertExists($oldImage);

        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('اتاق نشیمن');

        $this->actingAs($this->admin)
            ->get(route('admin.categories.show', $category))
            ->assertOk()
            ->assertSee('اتاق نشیمن');

        $this->actingAs($this->admin)
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('تصویر دسته‌بندی');

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'اتاق نشیمن مدرن',
                'slug' => 'living-room-lifecycle',
                'description' => 'توضیح به‌روزشده',
                'is_active' => '1',
                'sort_order' => '2',
                'image' => UploadedFile::fake()->image('category-new.jpg', 1000, 1000),
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('اتاق نشیمن مدرن', $category->name);
        $this->assertSame(2, $category->sort_order);
        $this->assertNotSame($oldImage, $category->image);
        Storage::disk('public')->assertExists($oldImage); // Replaced source remains in the Media Library.
        Storage::disk('public')->assertExists($category->image);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        Storage::disk('public')->assertExists($category->image); // Deleting content never deletes the shared source asset.
    }

    public function test_category_delete_is_blocked_when_only_soft_deleted_products_still_reference_it(): void
    {
        $category = $this->createCategory('trashed-product-category');
        $product = Product::query()->create($this->productPayload($category, [
            'name' => 'محصول حذف نرم‌شده',
            'slug' => 'trashed-product-category-reference',
            'sku' => 'SG-TRASHED-001',
        ]));
        $product->delete();

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_edit_publish_and_delete_blog_posts_with_tags_and_featured_images(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.blog.create'))
            ->assertOk()
            ->assertSee('زمان انتشار (تقویم شمسی)');

        $this->actingAs($this->admin)
            ->post(route('admin.blog.store'), [
                'title' => 'راهنمای انتخاب مبلمان',
                'slug' => 'furniture-guide-lifecycle',
                'category' => 'راهنمای خرید',
                'excerpt' => 'راهنمای کوتاه',
                'body' => '<p>محتوای سالم</p><script>alert(1)</script>',
                'tags' => 'مبلمان، راهنمای خرید',
                'status' => 'draft',
                'featured_image' => UploadedFile::fake()->image('blog-old.jpg', 1200, 675),
                'featured_image_alt' => 'مبلمان در فضای نشیمن',
                'seo_index' => '0',
                'seo_follow' => '0',
            ])
            ->assertRedirect(route('admin.blog.index'));

        $post = BlogPost::query()->where('slug', 'furniture-guide-lifecycle')->firstOrFail();
        $oldImage = $post->featured_image;

        $this->assertSame($this->admin->id, $post->user_id);
        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);
        $this->assertStringNotContainsString('<script', $post->body);
        $this->assertCount(2, $post->tags);
        Storage::disk('public')->assertExists($oldImage);

        $this->actingAs($this->admin)
            ->get(route('admin.blog.index'))
            ->assertOk()
            ->assertSee('راهنمای انتخاب مبلمان');

        $this->actingAs($this->admin)
            ->get(route('admin.blog.edit', $post))
            ->assertOk()
            ->assertSee('راهنمای انتخاب مبلمان');

        $this->actingAs($this->admin)
            ->put(route('admin.blog.update', $post), [
                'title' => 'راهنمای کامل انتخاب مبلمان',
                'slug' => 'furniture-guide-lifecycle',
                'category' => 'آموزش',
                'excerpt' => 'نسخه به‌روزشده',
                'body' => '<p>محتوای جدید</p>',
                'tags' => 'دکوراسیون',
                'status' => 'published',
                'published_at' => now()->subMinute()->toISOString(),
                'featured_image' => UploadedFile::fake()->image('blog-new.jpg', 1200, 675),
                'featured_image_alt' => 'راهنمای کامل چیدمان',
                'seo_index' => '1',
                'seo_follow' => '1',
            ])
            ->assertRedirect(route('admin.blog.edit', $post));

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertSame('محتوای جدید', strip_tags($post->body));
        $this->assertSame(['دکوراسیون'], $post->tags()->pluck('name')->all());
        Storage::disk('public')->assertExists($oldImage); // Replaced source remains in the Media Library.
        Storage::disk('public')->assertExists($post->featured_image);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('راهنمای کامل انتخاب مبلمان');

        $newImage = $post->featured_image;
        $this->actingAs($this->admin)
            ->delete(route('admin.blog.destroy', $post))
            ->assertRedirect(route('admin.blog.index'));

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
        Storage::disk('public')->assertExists($newImage); // The asset remains available for reuse after post deletion.
    }

    public function test_admin_can_manage_product_images_and_variants_without_leaving_orphaned_links(): void
    {
        $category = $this->createCategory('product-image-variant-category');
        $product = Product::query()->create($this->productPayload($category, [
            'name' => 'میز تست',
            'slug' => 'table-image-variant',
            'sku' => 'SG-TABLE-001',
        ]));

        $this->actingAs($this->admin)
            ->get(route('admin.product-images.create', ['product_id' => $product->id]))
            ->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.product-images.store'), [
                'product_id' => $product->id,
                'image' => UploadedFile::fake()->image('table-old.jpg', 800, 1000),
                'alt' => 'تصویر میز',
                'sort_order' => 0,
                'is_primary' => '0',
            ])
            ->assertRedirect(route('admin.product-images.index', ['product_id' => $product->id]));

        $image = ProductImage::query()->where('product_id', $product->id)->firstOrFail();
        $oldPath = $image->path;
        $this->assertTrue($image->is_primary);
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($this->admin)
            ->get(route('admin.product-images.show', $image))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.product-images.edit', $image))
            ->assertOk();

        $this->actingAs($this->admin)
            ->put(route('admin.product-images.update', $image), [
                'product_id' => $product->id,
                'image' => UploadedFile::fake()->image('table-new.jpg', 800, 1000),
                'alt' => 'تصویر جدید میز',
                'sort_order' => 1,
                'is_primary' => '0',
            ])
            ->assertRedirect(route('admin.product-images.index', ['product_id' => $product->id]));

        $image->refresh();
        $this->assertTrue($image->is_primary, 'A product must retain a primary image after an update.');
        Storage::disk('public')->assertExists($oldPath); // The old upload remains available in the central Media Library.
        Storage::disk('public')->assertExists($image->path);
        $this->assertNotNull($image->media_id);

        $this->actingAs($this->admin)
            ->post(route('admin.product-variants.store'), [
                'product_id' => $product->id,
                'type' => 'color',
                'name' => 'رنگ',
                'value' => 'black',
                'sku' => 'SG-TABLE-BLACK',
                'price_adjustment' => '0',
                'stock' => '4',
                'is_active' => '1',
                'image_ids' => [$image->id],
            ])
            ->assertRedirect(route('admin.products.edit', $product));

        $variant = ProductVariant::query()->where('sku', 'SG-TABLE-BLACK')->firstOrFail();
        $this->assertDatabaseHas('product_variant_images', [
            'product_variant_id' => $variant->id,
            'product_image_id' => $image->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.product-variants.show', $variant))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.product-variants.edit', $variant))
            ->assertOk();

        $this->actingAs($this->admin)
            ->put(route('admin.product-variants.update', $variant), [
                'product_id' => $product->id,
                'type' => 'color',
                'name' => 'رنگ',
                'value' => 'grey',
                'sku' => 'SG-TABLE-GREY',
                'price_adjustment' => '100000',
                'stock' => '5',
                'is_active' => '1',
                'image_ids' => [$image->id],
            ])
            ->assertRedirect(route('admin.products.edit', $product));

        $variant->refresh();
        $this->assertSame('grey', $variant->value);
        $this->assertDatabaseHas('product_variant_images', [
            'product_variant_id' => $variant->id,
            'product_image_id' => $image->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.product-variants.destroy', $variant))
            ->assertRedirect(route('admin.products.edit', $product));

        $this->actingAs($this->admin)
            ->delete(route('admin.product-images.destroy', $image))
            ->assertRedirect(route('admin.product-images.index', ['product_id' => $product->id]));

        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        Storage::disk('public')->assertExists($image->path); // Removing a usage never removes the shared original.
    }

    public function test_admin_can_upload_and_delete_media_library_assets(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.media.create'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('library-image.jpg'),
                'alt' => 'تصویر کتابخانه رسانه',
                'description' => 'فایل آزمایشی',
            ])
            ->assertRedirect(route('admin.media.index'));

        $media = Media::query()->where('original_name', 'library-image.jpg')->firstOrFail();
        $path = $media->path;

        $this->assertSame($this->admin->id, $media->user_id);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('library-image.jpg');

        $this->actingAs($this->admin)
            ->delete(route('admin.media.destroy', $media))
            ->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_create_edit_publish_and_delete_content_pages(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.pages.create'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.pages.store'), [
                'title' => 'راهنمای نگهداری چوب',
                'slug' => 'wood-care-lifecycle',
                'excerpt' => 'راهنمای نگهداری',
                'body' => '<p>متن صفحه</p><script>alert(2)</script>',
                'status' => 'draft',
                'featured_image' => UploadedFile::fake()->image('page-old.jpg'),
                'seo_index' => '0',
                'seo_follow' => '0',
            ])
            ->assertRedirect(route('admin.pages.index'));

        $page = ContentPage::query()->where('slug', 'wood-care-lifecycle')->firstOrFail();
        $oldImage = $page->featured_image;
        $this->assertSame($this->admin->id, $page->user_id);
        $this->assertStringNotContainsString('<script', $page->body);
        Storage::disk('public')->assertExists($oldImage);

        $this->actingAs($this->admin)
            ->get(route('admin.pages.index'))
            ->assertOk()
            ->assertSee('راهنمای نگهداری چوب');

        $this->actingAs($this->admin)
            ->get(route('admin.pages.edit', $page))
            ->assertOk();

        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', $page), [
                'title' => 'راهنمای کامل نگهداری چوب',
                'slug' => 'wood-care-lifecycle',
                'excerpt' => 'نسخه کامل',
                'body' => '<p>محتوای به‌روزشده</p>',
                'status' => 'published',
                'published_at' => now()->subMinute()->toISOString(),
                'featured_image' => UploadedFile::fake()->image('page-new.jpg'),
                'seo_index' => '0',
                'seo_follow' => '0',
            ])
            ->assertRedirect(route('admin.pages.edit', $page));

        $page->refresh();
        $this->assertSame('published', $page->status);
        Storage::disk('public')->assertExists($oldImage); // Replaced source remains in the Media Library.
        Storage::disk('public')->assertExists($page->featured_image);

        $this->get('/wood-care-lifecycle')
            ->assertOk()
            ->assertSee('راهنمای کامل نگهداری چوب');

        $newImage = $page->featured_image;
        $this->actingAs($this->admin)
            ->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseMissing('content_pages', ['id' => $page->id]);
        Storage::disk('public')->assertExists($newImage); // Deleting a page detaches the usage but keeps the shared library asset.
    }

    public function test_customer_cannot_open_or_mutate_admin_catalogue_cms_or_media_crud_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = $this->createCategory('admin-routes-guard-category');

        $product = Product::query()->create($this->productPayload($category, [
            'name' => 'محصول محافظت‌شده',
            'slug' => 'protected-admin-product',
            'sku' => 'SG-PROTECTED-001',
        ]));

        $blog = BlogPost::query()->create([
            'user_id' => $this->admin->id,
            'title' => 'مقاله محافظت‌شده',
            'slug' => 'protected-admin-blog',
            'body' => 'محتوا',
            'status' => 'draft',
            'seo_index' => false,
            'seo_follow' => false,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.products.create'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->put(route('admin.products.update', $product), $this->productPayload($category, [
                'name' => 'نباید تغییر کند',
                'slug' => 'protected-admin-product',
                'sku' => 'SG-PROTECTED-001',
            ]))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.categories.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.blog.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->delete(route('admin.blog.destroy', $blog))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.media.index'))
            ->assertForbidden();

        $this->assertSame('محصول محافظت‌شده', $product->fresh()->name);
        $this->assertDatabaseHas('blog_posts', ['id' => $blog->id]);
    }

    public function test_admin_can_update_customer_details_and_order_fulfillment_status(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'مشتری اولیه',
            'email' => 'customer-before@example.test',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('مشتری اولیه');

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('مشتری اولیه');

        $this->actingAs($this->admin)
            ->get(route('admin.customers.edit', $customer))
            ->assertOk();

        $this->actingAs($this->admin)
            ->put(route('admin.customers.update', $customer), [
                'name' => 'مشتری به‌روزشده',
                'email' => 'customer-after@example.test',
            ])
            ->assertRedirect(route('admin.customers.show', $customer));

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'مشتری به‌روزشده',
            'email' => 'customer-after@example.test',
            'role' => 'customer',
        ]);

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'LIV-ADMIN-CRUD-0001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 1000000,
            'shipping_cost' => 50000,
            'discount' => 0,
            'total' => 1050000,
            'first_name' => 'مشتری',
            'last_name' => 'آزمایشی',
            'phone' => '09120000000',
            'email' => 'customer-after@example.test',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'خیابان نمونه، پلاک ۱',
            'postal_code' => '1234567890',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('LIV-ADMIN-CRUD-0001');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('LIV-ADMIN-CRUD-0001');

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), [
                'status' => 'shipped',
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'payment_status' => 'pending',
        ]);
    }

    public function test_home_section_editor_offers_real_sources_and_floating_slide_preview(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.home-sections.create'))
            ->assertOk()
            ->assertSee('پرفروش‌ترین محصولات')
            ->assertSee('محصولات ویژه')
            ->assertSee('پیش‌نمایش شناور')
            ->assertSee('LIVE PREVIEW')
            ->assertSee('payload[product_source]');
    }

    public function test_variant_cover_upload_is_cropped_and_kept_out_of_the_base_product_gallery(): void
    {
        $category = $this->createCategory('variant-cover-scope-category');
        $product = Product::query()->create($this->productPayload($category, [
            'name' => 'مبل با رنگ مستقل',
            'slug' => 'sofa-variant-cover-scope',
            'sku' => 'SG-VARIANT-COVER-001',
        ]));

        $this->actingAs($this->admin)
            ->post(route('admin.product-variants.store'), [
                'product_id' => $product->id,
                'type' => 'رنگ',
                'name' => 'رنگ بدنه',
                'value' => 'قرمز',
                'sku' => 'SG-VARIANT-COVER-RED',
                'price_adjustment' => '0',
                'stock' => '4',
                'is_active' => '1',
                'variant_image' => UploadedFile::fake()->image('sofa-red.jpg', 800, 1000),
            ])
            ->assertRedirect(route('admin.products.edit', $product));

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('value', 'قرمز')
            ->firstOrFail();
        $variantImage = $variant->images()->firstOrFail();

        $this->assertTrue($variantImage->is_variant_only);
        $this->assertSame(1, $variant->images()->count());
        $this->assertSame(0, $product->images()->count());
        $this->assertNotNull($variantImage->media_id);

        $media = Media::query()->findOrFail($variantImage->media_id);
        $this->assertNotEmpty($media->detail_path);
        Storage::disk('public')->assertExists($media->path);
        Storage::disk('public')->assertExists($media->detail_path);

        $this->actingAs($this->admin)
            ->get(route('admin.product-variants.edit', $variant))
            ->assertOk()
            ->assertSee('variant-image-preview')
            ->assertSee($variantImage->detail_url, false);
    }

    public function test_admin_image_upload_previews_match_the_public_card_aspect_ratios(): void
    {
        $category = $this->createCategory('image-preview-ratio-category');
        $product = Product::query()->create($this->productPayload($category, [
            'name' => 'محصول نسبت تصویر',
            'slug' => 'image-preview-ratio-product',
            'sku' => 'SG-IMAGE-RATIO-001',
        ]));

        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => 'https://images.example.test/products/variant-preview.webp',
            'alt' => 'تصویر آزمایشی وریانت',
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSee('aspect-[4/3]', false)
            ->assertSee('data-crop-ratio="1.3333333"', false)
            ->assertSee('data-preview-target="category-image-preview"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('aspect-[4/5]', false)
            ->assertSee('data-crop-ratio="0.8"', false)
            ->assertSee('data-preview-target="product-image-preview"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.product-images.create', ['product_id' => $product->id]))
            ->assertOk()
            ->assertSee('aspect-[4/5]', false)
            ->assertSee('data-crop-ratio="0.8"', false)
            ->assertSee('data-preview-target="product-gallery-image-preview"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.product-variants.create', ['product_id' => $product->id]))
            ->assertOk()
            ->assertSee('aspect-[4/5]', false)
            ->assertSee('data-crop-ratio="0.8"', false)
            ->assertSee('data-preview-target="variant-image-preview"', false)
            ->assertSee('aspect-[4/3]', false)
            ->assertSee($product->images()->firstOrFail()->url);

        $this->actingAs($this->admin)
            ->get(route('admin.shop-banner.edit'))
            ->assertOk()
            ->assertSee('aspect-[12/5]', false)
            ->assertSee('data-crop-ratio="2.4"', false)
            ->assertSee('data-preview-target="shop-banner-image-preview"', false);
    }

    private function createCategory(string $slug): Category
    {
        return Category::query()->create([
            'name' => 'دسته تست',
            'slug' => $slug,
            'description' => 'دسته‌بندی برای تست عملیات مدیریت',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function productPayload(Category $category, array $overrides = []): array
    {
        return array_merge([
            'category_id' => $category->id,
            'name' => 'محصول آزمایشی',
            'slug' => 'admin-crud-product',
            'sku' => 'SG-ADMIN-CRUD-001',
            'short_description' => 'توضیح کوتاه',
            'description' => 'توضیح کامل محصول',
            'price' => '12500000',
            'stock' => 5,
            'status' => 'active',
            'is_featured' => '0',
            'is_new' => '0',
            'installment_enabled' => '0',
        ], $overrides);
    }
}
