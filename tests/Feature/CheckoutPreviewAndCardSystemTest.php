<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Services\Payments\Contracts\InstallmentPaymentGatewayInterface;
use App\Services\Payments\PaymentManager;
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
            ->assertSee('هیچ درخواست پرداختی ارسال نمی‌شود.');

        $content = $response->getContent();
        $this->assertStringContainsString('noindex', $content);
        $this->assertStringContainsString('nofollow', $content);
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
            ->assertSee('sg-product-carousel', false)
            ->assertSee('data-carousel-track', false)
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

        public function test_admin_can_manage_category_editorial_sections_faq_comparison_and_related_links(): void
    {
        $admin = $this->user('admin', 'category-content-admin@example.test');
        $relatedCategory = Category::query()->create([
            'name' => 'اتاق خواب',
            'slug' => 'related-bedroom-category',
            'description' => 'دسته مرتبط',
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $relatedProduct = Product::query()->create([
            'category_id' => $relatedCategory->id,
            'name' => 'پاتختی مرتبط',
            'slug' => 'related-nightstand',
            'sku' => 'SG-REL-001',
            'short_description' => 'پاتختی',
            'description' => 'محصول مرتبط',
            'price' => 1200000,
            'stock' => 4,
            'status' => 'active',
            'installment_enabled' => false,
        ]);

        $this->actingAs($admin)->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSee('ساختار محتوای قابل مدیریت')
            ->assertSee('جدول مقایسه و معیارهای انتخاب')
            ->assertSee('دسته‌بندی‌های مرتبط');

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'مبلمان نشیمن',
            'slug' => 'living-room-furniture-content',
            'description' => 'معرفی اختصاصی دسته نشیمن',
            'image_alt' => 'مجموعه مبلمان نشیمن در فضای مدرن',
            'focus_keyword' => 'مبلمان نشیمن',
            'meta_title' => 'خرید مبلمان نشیمن مدرن',
            'meta_description' => 'راهنمای انتخاب مبلمان نشیمن مدرن برای خانه‌های امروزی.',
            'is_active' => '1',
            'sort_order' => '1',
            'content_sections' => [
                ['heading' => 'کاربردها و انواع', 'body' => 'این بخش درباره انتخاب و کاربرد مبلمان است. <script>alert(1)</script>'],
                ['heading' => 'راهنمای انتخاب', 'body' => 'ابعاد فضا و نوع استفاده را بررسی کنید.'],
            ],
            'comparison_rows' => [
                ['label' => 'متریال', 'value' => 'چوب خشک‌شده و پارچه مقاوم'],
                ['label' => 'ابعاد', 'value' => 'متناسب با فضای نشیمن'],
            ],
            'faq_items' => [
                ['question' => 'چطور اندازه مناسب را انتخاب کنم؟', 'answer' => 'ابتدا ابعاد فضای قابل استفاده را اندازه بگیرید.'],
            ],
            'related_category_ids' => [$relatedCategory->id],
            'related_product_ids' => [$relatedProduct->id],
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('slug', 'living-room-furniture-content')->firstOrFail();

        $this->assertSame('مجموعه مبلمان نشیمن در فضای مدرن', $category->image_alt);
        $this->assertCount(2, $category->content_sections);
        $this->assertCount(2, $category->comparison_rows);
        $this->assertCount(1, $category->faq_items);
        $this->assertDatabaseHas('category_related_categories', [
            'category_id' => $category->id,
            'related_category_id' => $relatedCategory->id,
        ]);
        $this->assertDatabaseHas('category_recommended_products', [
            'category_id' => $category->id,
            'product_id' => $relatedProduct->id,
        ]);

        $response = $this->get(route('categories.show', $category->slug));
        $response->assertOk()
            ->assertSee('aspect-[12/5]', false)
            ->assertSee('کاربردها و انواع')
            ->assertSee('راهنمای انتخاب')
            ->assertSee('معیارهای انتخاب')
            ->assertSee('سؤالات متداول')
            ->assertSee('چطور اندازه مناسب را انتخاب کنم؟')
            ->assertSee('پاتختی مرتبط')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_can_manage_product_specs_care_faq_and_related_products(): void
    {
        $admin = $this->user('admin', 'product-content-admin@example.test');
        $category = Category::query()->create([
            'name' => 'مبلمان',
            'slug' => 'product-editorial-category',
            'description' => 'دسته تست',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'مبل راحتی',
            'slug' => 'editorial-sofa',
            'sku' => 'SG-EDITORIAL-001',
            'short_description' => 'مبل راحتی مدرن',
            'description' => 'توضیح قبلی محصول',
            'price' => 48500000,
            'stock' => 5,
            'status' => 'active',
            'installment_enabled' => false,
        ]);
        $related = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'میز جلو مبلی',
            'slug' => 'editorial-coffee-table',
            'sku' => 'SG-EDITORIAL-002',
            'short_description' => 'میز جلو مبلی',
            'description' => 'محصول مرتبط',
            'price' => 8500000,
            'stock' => 3,
            'status' => 'active',
            'installment_enabled' => false,
        ]);

        $this->actingAs($admin)->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('ساختار محتوای قابل مدیریت')
            ->assertSee('مشخصات فنی محصول')
            ->assertSee('راهنمای نگهداری و استفاده')
            ->assertSee('محصولات مرتبط');

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'name' => 'مبل راحتی',
            'slug' => 'editorial-sofa',
            'sku' => 'SG-EDITORIAL-001',
            'short_description' => 'مبل راحتی مدرن',
            'description' => 'توضیح قبلی محصول',
            'price' => '48500000',
            'stock' => '5',
            'status' => 'active',
            'is_featured' => '0',
            'is_new' => '0',
            'installment_enabled' => '0',
            'focus_keyword' => 'مبل راحتی مدرن',
            'meta_title' => 'خرید مبل راحتی مدرن با مشخصات کامل',
            'meta_description' => 'مشخصات و راهنمای خرید مبل راحتی مدرن را در سیلا گالری بررسی کنید.',
            'content_sections' => [
                ['heading' => 'معرفی محصول', 'body' => 'این مبل برای فضای نشیمن طراحی شده است.'],
            ],
            'key_features' => [
                ['label' => 'طراحی', 'value' => 'مدرن و مینیمال'],
                ['label' => 'کاربری', 'value' => 'مناسب نشیمن روزمره'],
            ],
            'specifications' => [
                ['label' => 'جنس بدنه', 'value' => 'چوب راش'],
                ['label' => 'رنگ', 'value' => 'طوسی'],
                ['label' => 'ابعاد', 'value' => 'عرض ۲۲۰ سانتی‌متر'],
            ],
            'care_guide' => 'با دستمال نرم و خشک تمیز شود.',
            'faq_items' => [
                ['question' => 'آیا روکش قابل شست‌وشو است؟', 'answer' => 'طبق دستورالعمل پارچه و برچسب محصول اقدام کنید.'],
            ],
            'related_product_ids' => [$related->id],
        ])->assertRedirect(route('admin.products.edit', $product));

        $product->refresh();
        $this->assertSame('مبل راحتی مدرن', $product->focus_keyword);
        $this->assertCount(1, $product->content_sections);
        $this->assertCount(2, $product->key_features);
        $this->assertCount(3, $product->specifications);
        $this->assertSame('با دستمال نرم و خشک تمیز شود.', $product->care_guide);
        $this->assertCount(1, $product->faq_items);
        $this->assertDatabaseHas('product_related_products', [
            'product_id' => $product->id,
            'related_product_id' => $related->id,
        ]);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('معرفی محصول')
            ->assertSee('ویژگی‌های کلیدی')
            ->assertSee('طراحی')
            ->assertSee('مدرن و مینیمال')
            ->assertSee('additionalProperty', false)
            ->assertSee('مشخصات فنی')
            ->assertSee('جنس بدنه')
            ->assertSee('چوب راش')
            ->assertSee('راهنمای نگهداری و استفاده')
            ->assertSee('با دستمال نرم و خشک تمیز شود.')
            ->assertSee('سؤالات متداول درباره')
            ->assertSee('آیا روکش قابل شست‌وشو است؟')
            ->assertSee('میز جلو مبلی');
    }

    public function test_category_filters_apply_only_to_the_current_category(): void
    {
        $category = Category::query()->create([
            'name' => 'میزها',
            'slug' => 'category-filter-test',
            'description' => 'دسته فیلتر',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'میز عسلی چوبی',
            'slug' => 'filtered-wood-table',
            'sku' => 'SG-FILTER-001',
            'short_description' => 'میز چوبی',
            'description' => 'میز کوچک',
            'price' => 1000000,
            'stock' => 2,
            'status' => 'active',
            'installment_enabled' => false,
        ]);
        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'میز تلویزیون',
            'slug' => 'filtered-tv-stand',
            'sku' => 'SG-FILTER-002',
            'short_description' => 'میز تلویزیون',
            'description' => 'میز بزرگ',
            'price' => 9000000,
            'stock' => 1,
            'status' => 'active',
            'installment_enabled' => false,
        ]);

        $this->get(route('categories.show', $category->slug) . '?search=عسلی&sort=price_asc')
            ->assertOk()
            ->assertSee('میز عسلی چوبی')
            ->assertDontSee('میز تلویزیون')
            ->assertSee('name="search"', false);
    }

    public function test_checkout_rejects_aggregate_quantity_above_base_stock_without_creating_order(): void
    {
        $customer = $this->user('customer', 'checkout-aggregate-stock@example.test');
        $category = Category::query()->create([
            'name' => 'موجودی',
            'slug' => 'aggregate-stock-category',
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'محصول با موجودی محدود',
            'slug' => 'aggregate-stock-product',
            'sku' => 'SG-STOCK-AGG-001',
            'short_description' => 'محصول تست',
            'description' => 'محصول تست',
            'price' => 1000000,
            'stock' => 3,
            'status' => 'active',
            'installment_enabled' => false,
        ]);
        $cart = Cart::query()->create([
            'user_id' => $customer->id,
            'session_id' => null,
            'status' => 'active',
        ]);
        foreach (['base-a', 'base-b'] as $variantKey) {
            CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => null,
                'variant_key' => $variantKey,
                'variant_options' => null,
                'quantity' => 2,
                'unit_price' => 1000000,
            ]);
        }

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.place'), $this->validCheckoutPayload($customer))
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame('active', $cart->fresh()->status);
        $this->assertSame(2, $cart->items()->count());
    }

    public function test_checkout_creates_one_order_and_consumes_cart_atomically(): void
    {
        $customer = $this->user('customer', 'checkout-idempotency@example.test');
        $category = Category::query()->create([
            'name' => 'سفارش',
            'slug' => 'checkout-order-category',
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'محصول سفارش',
            'slug' => 'checkout-order-product',
            'sku' => 'SG-ORDER-001',
            'short_description' => 'محصول تست',
            'description' => 'محصول تست',
            'price' => 1250000,
            'stock' => 5,
            'status' => 'active',
            'installment_enabled' => false,
        ]);
        $cart = Cart::query()->create([
            'user_id' => $customer->id,
            'session_id' => null,
            'status' => 'active',
        ]);
        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 2,
            'unit_price' => 1250000,
        ]);

        $response = $this->actingAs($customer)
            ->post(route('checkout.place'), $this->validCheckoutPayload($customer));

        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('checkout.payment', $order));
        $this->assertSame('2500000.00', $order->fresh()->subtotal);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame('converted', $cart->fresh()->status);
        $this->assertSame(0, $cart->items()->count());

        // A repeated browser submission cannot consume the converted cart again.
        $this->actingAs($customer)
            ->post(route('checkout.place'), $this->validCheckoutPayload($customer));

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_gateway_start_redirects_to_provider_and_creates_one_exact_amount_attempt(): void
    {
        $customer = $this->user('customer', 'gateway-start@example.test');
        $order = $this->orderFor($customer);

        $gateway = \Mockery::mock(InstallmentPaymentGatewayInterface::class);
        $gateway->shouldReceive('createPayment')->once()->andReturn([
            'success' => true,
            'payment_url' => 'https://pay.example.test/session/abc',
            'authority' => 'AUTH-SG-001',
            'transaction_id' => null,
            'message' => 'created',
            'data' => [],
        ]);
        $manager = \Mockery::mock(PaymentManager::class);
        $manager->shouldReceive('driver')->once()->with('digipay')->andReturn($gateway);
        $this->instance(PaymentManager::class, $manager);

        $this->actingAs($customer)
            ->post(route('checkout.payment.installment', $order), ['gateway' => 'digipay'])
            ->assertRedirect('https://pay.example.test/session/abc');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'gateway' => 'digipay',
            'amount' => '48500000.00',
            'authority' => 'AUTH-SG-001',
            'status' => 'initiated',
        ]);
        $this->assertSame('digipay', $order->fresh()->payment_provider);
    }

    public function test_verified_gateway_callback_is_idempotent_and_deducts_stock_once(): void
    {
        $customer = $this->user('customer', 'gateway-callback@example.test');
        $order = $this->orderFor($customer);
        $category = Category::query()->create([
            'name' => 'پرداخت',
            'slug' => 'gateway-callback-category',
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'محصول پرداخت',
            'slug' => 'gateway-callback-product',
            'sku' => 'SG-CALLBACK-001',
            'price' => 48500000,
            'stock' => 6,
            'status' => 'active',
            'installment_enabled' => false,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 48500000,
            'total' => 48500000,
        ]);
        Payment::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'gateway' => 'digipay',
            'authority' => 'AUTH-CALLBACK-001',
            'amount' => 48500000,
            'status' => 'initiated',
            'metadata' => ['payment_method' => 'online'],
        ]);

        $gateway = \Mockery::mock(InstallmentPaymentGatewayInterface::class);
        $gateway->shouldReceive('verifyPayment')->once()->andReturn([
            'success' => true,
            'amount' => 48500000,
            'transaction_id' => 'TXN-CALLBACK-001',
            'message' => 'verified',
            'data' => [],
        ]);
        $manager = \Mockery::mock(PaymentManager::class);
        $manager->shouldReceive('driver')->twice()->with('digipay')->andReturn($gateway);
        $this->instance(PaymentManager::class, $manager);

        $this->get('/checkout/payment/callback/digipay?authority=AUTH-CALLBACK-001')
            ->assertRedirect(route('account.orders.show', $order));
        $this->get('/checkout/payment/callback/digipay?authority=AUTH-CALLBACK-001')
            ->assertRedirect(route('account.orders.show', $order));

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('paid', Payment::query()->where('order_id', $order->id)->firstOrFail()->status);
        $this->assertSame('deducted', $order->fresh()->inventory_status);
        $this->assertSame(5, (int) $product->fresh()->stock);
    }

    public function test_gateway_amount_mismatch_never_marks_order_paid_or_deducts_stock(): void
    {
        $customer = $this->user('customer', 'gateway-mismatch@example.test');
        $order = $this->orderFor($customer);
        $category = Category::query()->create([
            'name' => 'تطبیق مبلغ',
            'slug' => 'gateway-amount-category',
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'محصول مبلغ',
            'slug' => 'gateway-amount-product',
            'sku' => 'SG-AMOUNT-001',
            'price' => 48500000,
            'stock' => 6,
            'status' => 'active',
            'installment_enabled' => false,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 48500000,
            'total' => 48500000,
        ]);
        Payment::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'gateway' => 'digipay',
            'authority' => 'AUTH-CALLBACK-002',
            'amount' => 48500000,
            'status' => 'initiated',
            'metadata' => ['payment_method' => 'online'],
        ]);

        $gateway = \Mockery::mock(InstallmentPaymentGatewayInterface::class);
        $gateway->shouldReceive('verifyPayment')->once()->andReturn([
            'success' => true,
            'amount' => 48500001,
            'transaction_id' => 'TXN-CALLBACK-002',
            'data' => [],
        ]);
        $manager = \Mockery::mock(PaymentManager::class);
        $manager->shouldReceive('driver')->once()->with('digipay')->andReturn($gateway);
        $this->instance(PaymentManager::class, $manager);

        $this->get('/checkout/payment/callback/digipay?authority=AUTH-CALLBACK-002')
            ->assertRedirect(route('home'));

        $this->assertSame('failed', Payment::query()->where('order_id', $order->id)->firstOrFail()->status);
        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertSame(6, (int) $product->fresh()->stock);
    }

    public function test_transient_gateway_verification_failure_keeps_attempt_pending_for_reconciliation(): void
    {
        $customer = $this->user('customer', 'gateway-timeout@example.test');
        $order = $this->orderFor($customer);
        Payment::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'gateway' => 'digipay',
            'authority' => 'AUTH-CALLBACK-003',
            'amount' => 48500000,
            'status' => 'initiated',
            'metadata' => ['payment_method' => 'online'],
        ]);

        $gateway = \Mockery::mock(InstallmentPaymentGatewayInterface::class);
        $gateway->shouldReceive('verifyPayment')->once()->andReturn([
            'success' => false,
            'message' => 'Gateway timeout',
            'data' => [],
        ]);
        $manager = \Mockery::mock(PaymentManager::class);
        $manager->shouldReceive('driver')->once()->with('digipay')->andReturn($gateway);
        $this->instance(PaymentManager::class, $manager);

        $this->get('/checkout/payment/callback/digipay?authority=AUTH-CALLBACK-003')
            ->assertRedirect(route('account.orders.show', $order))
            ->assertSessionHas('warning');

        $payment = Payment::query()->where('order_id', $order->id)->firstOrFail();
        $this->assertSame('initiated', $payment->status);
        $this->assertTrue((bool) data_get($payment->metadata, 'verification_pending'));
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    private function validCheckoutPayload(User $customer): array
    {
        return [
            'first_name' => 'مشتری',
            'last_name' => 'آزمون',
            'phone' => '09120000000',
            'email' => $customer->email,
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'خیابان نمونه، پلاک ۱۲، واحد ۳',
            'postal_code' => '1234567890',
            'unit' => '۳',
            'notes' => null,
        ];
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
