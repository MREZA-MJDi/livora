<?php

namespace Tests\\Feature;

use App\\Models\\Category;
use App\\Models\\Product;
use App\\Models\\ShopBannerSetting;
use App\\Models\\User;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Http\\UploadedFile;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Support\\Facades\\Storage;
use Tests\\TestCase;

class ShopBannerAndHomeCarouselTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_banner_is_admin_managed_cropped_and_rendered_on_shop_page(): void
    {
        Storage::fake('public');

        $admin = User::query()->create([
            'name' => 'Shop Banner Admin',
            'email' => 'shop-banner-admin@example.test',
            'password' => Hash::make('StrongAdminPassword123'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.shop-banner.edit'))
            ->assertOk()
            ->assertSee('data-crop-ratio="2.4"', false)
            ->assertSee('مدیریت بنر فروشگاه');

        $image = UploadedFile::fake()->image('shop-hero.jpg', 2400, 1000);

        $this->actingAs($admin)
            ->put(route('admin.shop-banner.update'), [
                'eyebrow' => 'SILLAGALLERY EDIT',
                'title' => 'انتخابی برای خانه شما',
                'description' => 'محصولات را با خیال راحت بررسی و مقایسه کنید.',
                'banner_image_alt' => 'نمای مبلمان در فضای نشیمن',
                'banner_image' => $image,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.shop-banner.edit'))
            ->assertSessionHas('success');

        $settings = ShopBannerSetting::query()->firstOrFail();

        $this->assertSame('انتخابی برای خانه شما', $settings->title);
        $this->assertTrue($settings->is_active);
        $this->assertNotEmpty($settings->banner_image);
        Storage::disk('public')->assertExists($settings->banner_image);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('انتخابی برای خانه شما')
            ->assertSee('نمای مبلمان در فضای نشیمن')
            ->assertSee($settings->banner_image_url);
    }

    public function test_guests_cannot_manage_the_shop_banner(): void
    {
        $this->get(route('admin.shop-banner.edit'))
            ->assertRedirect(route('login'));

        $this->put(route('admin.shop-banner.update'), [
            'eyebrow' => 'No access',
            'title' => 'No access',
            'is_active' => '1',
        ])->assertRedirect(route('login'));
    }

    public function test_homepage_product_sections_use_swipeable_carousels_and_structured_cards(): void
    {
        $category = Category::query()->create([
            'name' => 'مبلمان',
            'slug' => 'home-carousel-category',
            'description' => 'دسته تست صفحه اصلی',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'مبل راحتی تست',
            'slug' => 'home-carousel-sofa',
            'sku' => 'HOME-CAROUSEL-001',
            'short_description' => 'مبل راحتی با طراحی مینیمال',
            'description' => 'توضیح کامل محصول آزمایشی',
            'price' => 12500000,
            'stock' => 4,
            'status' => 'active',
            'is_featured' => true,
            'is_new' => true,
            'installment_enabled' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-sg-carousel', false)
            ->assertSee('data-carousel-track', false)
            ->assertSee('مبل راحتی تست')
            ->assertSee('دسته‌بندی:')
            ->assertSee('قیمت محصول')
            ->assertSee('مبل راحتی با طراحی مینیمال');
    }

    public function test_shop_banner_without_an_image_keeps_a_styled_text_fallback(): void
    {
        ShopBannerSetting::query()->create([
            'id' => 1,
            'eyebrow' => 'SILLAGALLERY',
            'title' => 'فروشگاه با تصویر پیش‌فرض',
            'description' => 'توضیح بنر بدون تصویر',
            'is_active' => true,
        ]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('فروشگاه با تصویر پیش‌فرض')
            ->assertSee('توضیح بنر بدون تصویر');
    }
}
