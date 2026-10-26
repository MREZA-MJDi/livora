<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
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
