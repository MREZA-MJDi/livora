<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Services\Inventory\OrderInventoryService;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StorefrontReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_without_catalogue_records(): void
    {
        foreach ([
            '/',
            '/shop',
            '/categories',
            '/about',
            '/contact',
            '/cart',
            '/login',
            '/register',
        ] as $uri) {
            $this->get($uri)
                ->assertOk();
        }
    }

    public function test_unknown_json_routes_keep_a_client_error_status(): void
    {
        $this->getJson('/route-that-does-not-exist')
            ->assertNotFound();
    }

    public function test_inactive_category_products_are_hidden_and_cannot_be_added_to_cart(): void
    {
        $activeCategory = $this->category([
            'name' => 'دسته فعال',
            'slug' => 'active-category',
            'is_active' => true,
        ]);
        $hiddenCategory = $this->category([
            'name' => 'دسته غیرفعال',
            'slug' => 'inactive-category',
            'is_active' => false,
        ]);

        $visibleProduct = $this->product($activeCategory, [
            'name' => 'محصول قابل نمایش',
            'slug' => 'visible-storefront-product',
        ]);
        $hiddenProduct = $this->product($hiddenCategory, [
            'name' => 'محصول پنهان',
            'slug' => 'hidden-storefront-product',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($visibleProduct->name)
            ->assertDontSee($hiddenProduct->name);

        $this->get('/shop')
            ->assertOk()
            ->assertSee($visibleProduct->name)
            ->assertDontSee($hiddenProduct->name);

        $this->get(route('product.show', $hiddenProduct->slug))
            ->assertNotFound();

        $this->postJson(route('cart.add', $hiddenProduct), [
            'quantity' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_requires_an_active_selection_for_each_configured_variant_type(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول با گزینه غیرفعال',
            'slug' => 'product-with-disabled-option-type',
            'stock' => 20,
        ]);

        $color = ProductVariant::create([
            'product_id' => $product->id,
            'type' => 'color',
            'name' => 'رنگ',
            'value' => 'cream',
            'sku' => 'LIV-TEST-COLOR-1',
            'price_adjustment' => 0,
            'stock' => 5,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'type' => 'size',
            'name' => 'سایز',
            'value' => 'large',
            'sku' => 'LIV-TEST-SIZE-1',
            'price_adjustment' => 0,
            'stock' => 5,
            'is_active' => false,
        ]);

        $this->postJson(route('cart.add', $product), [
            'quantity' => 1,
            'variants' => [
                'color' => $color->id,
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('variants.size');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_paid_order_inventory_deduction_is_idempotent_for_products_and_variants(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول موجودی پایه',
            'slug' => 'base-stock-inventory-product',
            'stock' => 8,
        ]);

        $variantProduct = $this->product($this->category(), [
            'name' => 'محصول موجودی تنوع',
            'slug' => 'variant-stock-inventory-product',
            'stock' => 30,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $variantProduct->id,
            'type' => 'color',
            'name' => 'رنگ',
            'value' => 'cream',
            'sku' => 'LIV-TEST-INVENTORY-VARIANT',
            'price_adjustment' => 0,
            'stock' => 6,
            'is_active' => true,
        ]);

        $order = $this->inventoryOrder();
        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_options' => null,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 100,
            'total' => 200,
        ]);
        $order->items()->create([
            'product_id' => $variantProduct->id,
            'product_variant_id' => $variant->id,
            'variant_options' => [[
                'id' => $variant->id,
                'type' => 'color',
                'name' => 'رنگ',
                'value' => 'cream',
            ]],
            'product_name' => $variantProduct->name,
            'sku' => $variant->sku,
            'quantity' => 3,
            'unit_price' => 100,
            'total' => 300,
        ]);

        $service = app(OrderInventoryService::class);
        $service->deductForPaidOrder($order->fresh());
        $service->deductForPaidOrder($order->fresh());

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 6,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $variantProduct->id,
            'stock' => 30,
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'stock' => 3,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'inventory_status' => 'deducted',
        ]);
    }

    public function test_inventory_conflict_does_not_partially_decrement_an_order(): void
    {
        $availableProduct = $this->product($this->category(), [
            'name' => 'محصول دارای موجودی',
            'slug' => 'inventory-conflict-available',
            'stock' => 5,
        ]);
        $emptyProduct = $this->product($this->category(), [
            'name' => 'محصول بدون موجودی',
            'slug' => 'inventory-conflict-empty',
            'stock' => 0,
        ]);

        $order = $this->inventoryOrder();
        foreach ([$availableProduct, $emptyProduct] as $product) {
            $order->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => null,
                'variant_options' => null,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => 1,
                'unit_price' => 100,
                'total' => 100,
            ]);
        }

        app(OrderInventoryService::class)->deductForPaidOrder($order->fresh());

        $this->assertDatabaseHas('products', [
            'id' => $availableProduct->id,
            'stock' => 5,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $emptyProduct->id,
            'stock' => 0,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'inventory_status' => 'stock_conflict',
        ]);
    }

    public function test_category_page_links_back_to_the_shop_using_its_slug(): void
    {
        $category = $this->category([
            'name' => 'دسته لینک',
            'slug' => 'category-link-slug',
        ]);

        $product = $this->product($category, [
            'name' => 'محصول دسته لینک',
            'slug' => 'category-link-product',
        ]);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee(route('shop.index', ['category' => $category->slug]), false)
            ->assertSee($product->name);

        $this->get(route('shop.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_public_category_index_uses_bounded_pagination(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            $this->category([
                'name' => 'دسته ' . $i,
                'slug' => 'category-' . $i,
                'sort_order' => $i,
            ]);
        }

        $this->get('/categories')
            ->assertOk()
            ->assertViewHas('categories', function ($categories) {
                return $categories instanceof LengthAwarePaginator
                    && $categories->perPage() === 12
                    && $categories->total() === 13;
            });
    }

    public function test_guests_cannot_reach_customer_or_admin_pages(): void
    {
        foreach ([
            '/checkout',
            '/account',
            '/account/orders',
            '/admin',
            '/admin/orders',
        ] as $uri) {
            $this->get($uri)
                ->assertRedirect(route('login'));
        }
    }

    public function test_checkout_rejects_a_stale_cart_price_instead_of_charging_silently(): void
    {
        $category = $this->category();
        $product = $this->product($category, [
            'name' => 'محصول با قیمت تغییرکرده',
            'slug' => 'changed-price-product',
            'price' => 1200000,
            'stock' => 5,
        ]);

        $customer = User::create([
            'name' => 'Livora Test Customer',
            'email' => 'livora-customer@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);

        $cart = Cart::create([
            'user_id' => $customer->id,
            'session_id' => null,
            'status' => 'active',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 1,
            'unit_price' => 1000000,
        ]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.place'), [
                'first_name' => 'آزمون',
                'last_name' => 'لیورا',
                'phone' => '09123456789',
                'email' => 'buyer@example.test',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان نمونه، پلاک ۱',
                'postal_code' => '1234567890',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'unit_price' => 1000000,
        ]);
    }

    public function test_guest_can_update_remove_and_clear_only_their_own_session_cart(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول سبد مهمان',
            'slug' => 'guest-cart-product',
            'stock' => 8,
        ]);

        // The test client encrypts raw cookie values before sending them,
        // matching the framework's real encrypted session-cookie contract.
        $this->get(route('cart.index'))->assertOk();

        $cart = Cart::query()
            ->whereNull('user_id')
            ->where('status', 'active')
            ->latest('id')
            ->firstOrFail();

        $this->withCookie(
            config('session.cookie'),
            (string) $cart->session_id
        );
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        // Laravel's test client omits cookies on JSON requests unless
        // credentials are explicitly enabled. Preserve the same guest session
        // for this request and subsequent JSON mutations.
        $this->withCredentials()
            ->patchJson(route('cart.update', $item), ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('cart_count', 3);

        $this->patchJson(route('cart.update', $item), ['quantity' => 4])
            ->assertOk()
            ->assertJsonPath('cart_count', 4);

        $this->deleteJson(route('cart.remove', $item))
            ->assertOk()
            ->assertJsonPath('cart_count', 0);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);

        $secondItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $this->deleteJson(route('cart.clear'))
            ->assertOk()
            ->assertJsonPath('cart_count', 0);

        $this->assertDatabaseMissing('cart_items', ['id' => $secondItem->id]);
    }

    public function test_guest_cannot_modify_another_browser_session_cart(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول نشست دیگر',
            'slug' => 'foreign-session-cart-product',
        ]);

        $cart = Cart::create([
            'user_id' => null,
            'session_id' => 'unrelated-browser-session',
            'status' => 'active',
        ]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $this->patchJson(route('cart.update', $item), ['quantity' => 2])
            ->assertForbidden();
        $this->deleteJson(route('cart.remove', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
    }

    public function test_customer_account_profile_and_address_pages_are_real_and_work(): void
    {
        $customer = User::create([
            'name' => 'Livora Account Customer',
            'email' => 'account-customer@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('account.profile.edit'))
            ->assertOk()
            ->assertSee('ویرایش پروفایل');

        $this->actingAs($customer)
            ->get(route('account.addresses.index'))
            ->assertOk()
            ->assertSee('افزودن آدرس جدید');

        $this->actingAs($customer)
            ->put(route('account.profile.update'), [
                'name' => 'Updated Account Customer',
                'email' => 'account-customer@example.test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Updated Account Customer',
        ]);

        $this->actingAs($customer)
            ->post(route('account.addresses.store'), [
                'title' => 'خانه',
                'first_name' => 'آزمون',
                'last_name' => 'کاربر',
                'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان نمونه، کوچه یک، پلاک ده',
                'postal_code' => '۱۲۳۴۵۶۷۸۹۰',
                'unit' => '۲',
                'is_default' => 1,
            ])
            ->assertRedirect();

        $address = Address::query()
            ->where('user_id', $customer->id)
            ->firstOrFail();

        $this->assertSame('09123456789', $address->phone);
        $this->assertSame('1234567890', $address->postal_code);
        $this->assertTrue($address->is_default);

        $this->actingAs($customer)
            ->get(route('account.addresses.edit', $address))
            ->assertOk()
            ->assertSee('ویرایش آدرس ارسال');

        $this->actingAs($customer)
            ->put(route('account.addresses.update', $address), [
                'title' => 'خانه جدید',
                'first_name' => 'آزمون',
                'last_name' => 'کاربر',
                'phone' => '09123456789',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان نمونه، کوچه دو، پلاک یازده',
                'postal_code' => '1234567890',
                'unit' => '۳',
                'is_default' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'title' => 'خانه جدید',
            'is_default' => true,
        ]);
    }

    public function test_customer_cannot_view_or_modify_another_customers_address(): void
    {
        $owner = User::create([
            'name' => 'Address Owner',
            'email' => 'address-owner@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);
        $otherCustomer = User::create([
            'name' => 'Other Customer',
            'email' => 'other-address-customer@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);

        $address = Address::create([
            'user_id' => $owner->id,
            'title' => 'خصوصی',
            'first_name' => 'صاحب',
            'last_name' => 'آدرس',
            'phone' => '09123456789',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'نشانی خصوصی برای آزمون',
            'postal_code' => '1234567890',
            'is_default' => true,
        ]);

        $this->actingAs($otherCustomer)
            ->get(route('account.addresses.edit', $address))
            ->assertForbidden();

        $this->actingAs($otherCustomer)
            ->put(route('account.addresses.update', $address), [
                'title' => 'دستکاری',
                'first_name' => 'دستکاری',
                'last_name' => 'دستکاری',
                'phone' => '09111111111',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'نشانی دستکاری شده',
                'postal_code' => '1111111111',
                'is_default' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'user_id' => $owner->id,
            'title' => 'خصوصی',
        ]);
    }

    private function inventoryOrder(): Order
    {
        static $sequence = 0;
        $sequence++;

        return Order::create([
            'user_id' => null,
            'order_number' => 'LV-INVENTORY-TEST-' . $sequence,
            'status' => 'pending',
            'payment_status' => 'paid',
            'payment_method' => 'online',
            'payment_provider' => 'test-gateway',
            'subtotal' => 500,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 500,
            'first_name' => 'آزمون',
            'last_name' => 'موجودی',
            'phone' => '09123456789',
            'email' => 'inventory@example.test',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'آدرس آزمایشی',
            'postal_code' => '1234567890',
            'unit' => null,
            'notes' => null,
            'installment_enabled' => false,
            'inventory_status' => 'pending',
        ]);
    }

    private function category(array $attributes = []): Category
    {
        static $sequence = 0;
        $sequence++;

        return Category::create(array_merge([
            'name' => 'دسته آزمایشی ' . $sequence,
            'slug' => 'test-category-' . $sequence,
            'description' => 'دسته آزمایشی برای بررسی فروشگاه',
            'image' => null,
            'is_active' => true,
            'sort_order' => $sequence,
        ], $attributes));
    }

    private function product(Category $category, array $attributes = []): Product
    {
        static $sequence = 0;
        $sequence++;

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'محصول آزمایشی ' . $sequence,
            'slug' => 'test-product-' . $sequence,
            'sku' => 'LIV-TEST-' . $sequence,
            'short_description' => 'توضیح کوتاه آزمایشی',
            'description' => 'توضیح کامل آزمایشی',
            'price' => 1000000,
            'compare_at_price' => null,
            'stock' => 5,
            'status' => 'active',
            'is_featured' => false,
            'is_new' => false,
            'meta_title' => null,
            'meta_description' => null,
            'installment_enabled' => false,
        ], $attributes));
    }
}
