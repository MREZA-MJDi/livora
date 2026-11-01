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
            ->assertSee('DEMO-SG-0001')
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
            ->assertSee('در حال حاضر هیچ درگاه آنلاینی فعال نیست.');
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
