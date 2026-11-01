<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CheckoutPreviewAndCardSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_a_read_only_checkout_preview_without_creating_orders_or_payments(): void
    {
        $admin = $this->user('admin', 'checkout-preview-admin@example.test');

        $response = $this->actingAs($admin)
            ->get(route('admin.checkout.preview'));

        $response->assertOk()
            ->assertSee('پیش‌نمایش مدیریتی Checkout')
            ->assertSee('PREVIEW-SG-0001')
            ->assertSee('DEMO · READ ONLY')
            ->assertSee('هیچ درخواست پرداختی ارسال نمی‌شود.')
            ->assertSee('name="robots" content="noindex,nofollow"', false);

        $content = $response->getContent();
        $this->assertStringNotContainsString('name="gateway"', $content);
        $this->assertStringNotContainsString('/checkout/payment/0/installment', $content);
        $this->assertStringNotContainsString('/checkout/payment/0/internal-installment', $content);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('order_installments', 0);
    }

    public function test_admin_can_upload_a_product_banner_from_the_product_crud(): void
    {
        Storage::fake('public');
        $admin = $this->user('admin', 'product-banner-upload-admin@example.test');
        $category = Category::query()->create([
            'name' => 'نشیمن',
            'slug' => 'product-banner-upload-category',
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'مبل با بنر',
                'slug' => 'sofa-with-banner',
                'sku' => 'SG-SOFA-BANNER-001',
                'short_description' => 'مبل نمونه',
                'description' => 'توضیح محصول نمونه',
                'price' => '48500000',
                'stock' => 3,
                'status' => 'active',
                'banner_image' => UploadedFile::fake()->image('sofa-banner.jpg', 1200, 500),
                'banner_image_alt' => 'بنر اختصاصی معرفی مبل',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'sofa-with-banner')->firstOrFail();

        $this->assertNotEmpty($product->banner_image);
        $this->assertSame('بنر اختصاصی معرفی مبل', $product->banner_image_alt);
        Storage::disk('public')->assertExists($product->banner_image);
    }

    public function test_product_page_displays_dashboard_managed_banner_with_its_alt_text(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/banners/sofa-banner.webp', 'fake-banner');

        $category = Category::query()->create([
            'name' => 'مبلمان',
            'slug' => 'banner-test-category',
            'description' => 'دسته تست',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'مبل تست بنردار',
            'slug' => 'product-with-page-banner',
            'sku' => 'SG-BANNER-001',
            'short_description' => 'توضیح کوتاه محصول',
            'description' => 'توضیح کامل محصول',
            'price' => 48500000,
            'stock' => 3,
            'status' => 'active',
            'is_featured' => false,
            'is_new' => false,
            'installment_enabled' => false,
            'banner_image' => 'products/banners/sofa-banner.webp',
            'banner_image_alt' => 'بنر معرفی مبل تست',
        ]);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url('products/banners/sofa-banner.webp'), false)
            ->assertSee('بنر معرفی مبل تست')
            ->assertSee('aspect-[12/5]', false);
    }

    public function test_product_admin_form_exposes_matching_primary_crop_and_page_banner_uploads(): void
    {
        $admin = $this->user('admin', 'product-banner-admin@example.test');
        $category = Category::query()->create([
            'name' => 'دکوراسیون',
            'slug' => 'product-banner-admin-category',
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'محصول بنر',
            'slug' => 'product-banner-admin-form',
            'sku' => 'SG-BANNER-ADMIN-001',
            'price' => 1000000,
            'stock' => 1,
            'status' => 'active',
            'installment_enabled' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('data-crop-ratio="0.8"', false)
            ->assertSee('data-crop-ratio="2.4"', false)
            ->assertSee('بنر بالای صفحه محصول')
            ->assertSee('banner_image_alt');
    }

    public function test_customers_cannot_open_the_admin_checkout_preview(): void
    {
        $customer = $this->user('customer', 'checkout-preview-customer@example.test');

        $this->actingAs($customer)
            ->get(route('admin.checkout.preview'))
            ->assertForbidden();
    }

    public function test_real_customer_payment_page_renders_a_waiting_state_when_no_gateway_is_connected(): void
    {
        config([
            'payment.production_ready' => false,
            'payment.digipay.enabled' => false,
            'payment.snappay.enabled' => false,
            'payment.torobpay.enabled' => false,
        ]);

        $customer = $this->user('customer', 'checkout-payment-owner@example.test');
        $order = $this->orderFor($customer);

        $this->actingAs($customer)
            ->get(route('checkout.payment', $order))
            ->assertOk()
            ->assertSee('دیجی‌پی')
            ->assertSee('اسنپ‌پی')
            ->assertSee('ترب‌پی')
            ->assertSee('اتصال درگاه پرداخت هنوز تکمیل نشده است.')
            ->assertSee('در انتظار پرداخت');
    }

    public function test_customer_cannot_view_another_customers_order_payment_page(): void
    {
        $owner = $this->user('customer', 'checkout-order-owner@example.test');
        $otherCustomer = $this->user('customer', 'checkout-order-other@example.test');
        $order = $this->orderFor($owner);

        $this->actingAs($otherCustomer)
            ->get(route('checkout.payment', $order))
            ->assertForbidden();
    }

    public function test_expired_admin_ajax_session_returns_a_distinct_reauthentication_response(): void
    {
        Route::middleware(['web', 'auth', 'admin'])
            ->post('/admin/_testing/session-expired', fn () => abort(419));

        $admin = $this->user('admin', 'checkout-session-admin@example.test');

        $this->actingAs($admin)
            ->withHeader('Referer', route('admin.dashboard'))
            ->postJson('/admin/_testing/session-expired')
            ->assertStatus(419)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'admin_session_expired')
            ->assertJsonPath('login_url', route('login'));

        $this->assertSame(route('admin.dashboard'), session('url.intended'));
    }

    public function test_home_shop_and_category_pages_use_the_shared_card_grid_and_image_skeletons(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('categories/living-room.webp', 'fake-image');
        Storage::disk('public')->put('products/sofa.webp', 'fake-image');

        $category = Category::query()->create([
            'name' => 'نشیمن',
            'slug' => 'living-room-card-system',
            'description' => 'دسته تستی کارت‌ها',
            'image' => 'categories/living-room.webp',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'مبل تستی کارت یکپارچه',
            'slug' => 'shared-product-card-system',
            'sku' => 'SG-CARD-SYSTEM-001',
            'short_description' => 'محصول تستی سیستم کارت',
            'description' => 'توضیح محصول تستی',
            'price' => 48500000,
            'stock' => 5,
            'status' => 'active',
            'is_featured' => true,
            'is_new' => true,
            'installment_enabled' => false,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => 'products/sofa.webp',
            'alt' => 'مبل تستی',
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('sg-product-grid--showcase', false)
            ->assertSee('sg-card-image-skeleton', false)
            ->assertSee('data-card-image', false)
            ->assertSee($product->name);

        $this->get('/shop')
            ->assertOk()
            ->assertSee('sg-product-grid--catalog', false)
            ->assertSee('sg-card-image-skeleton', false)
            ->assertSee($product->name);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('sg-product-grid--category', false)
            ->assertSee('sg-card-image-skeleton', false)
            ->assertSee($product->name);

        $this->get('/categories')
            ->assertOk()
            ->assertSee('sg-category-grid--index', false)
            ->assertSee('sg-card-image-skeleton', false)
            ->assertSee($category->name);
    }

    private function user(string $role, string $email): User
    {
        return User::query()->create([
            'name' => 'SillaGallery Test User',
            'email' => $email,
            'password' => Hash::make('TestPass123!'),
            'role' => $role,
        ]);
    }

    private function orderFor(User $customer): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'LV-CHECKOUT-' . strtoupper(substr(md5($customer->email), 0, 8)),
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'online',
            'payment_provider' => null,
            'subtotal' => 48500000,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 48500000,
            'first_name' => 'مشتری',
            'last_name' => 'آزمون',
            'phone' => '09120000000',
            'email' => $customer->email,
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'آدرس تستی',
            'postal_code' => '1234567890',
            'unit' => null,
            'notes' => null,
            'installment_enabled' => false,
        ]);
    }
}
