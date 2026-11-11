<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutAuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_redirects_to_login_then_returns_to_checkout_and_keeps_cart(): void
    {
        $customer = User::query()->create([
            'name' => 'مشتری تست',
            'email' => 'checkout-login@example.test',
            'password' => Hash::make('StrongPass123'),
            'role' => 'customer',
        ]);

        $this->get(route('checkout.index'))
            ->assertRedirect(route('login'));

        $guestSessionId = session()->getId();
        $guestCart = $this->createGuestCart($guestSessionId);

        $this->post(route('login.store'), [
            'email' => $customer->email,
            'password' => 'StrongPass123',
        ])->assertRedirect(route('checkout.index'));

        $this->assertAuthenticatedAs($customer);
        $this->assertSame('converted', $guestCart->fresh()->status);

        $userCart = Cart::query()
            ->where('user_id', $customer->id)
            ->where('status', 'active')
            ->firstOrFail();

        $this->assertSame(1, $userCart->items()->count());
        $this->assertSame(2, (int) $userCart->items()->value('quantity'));

        $this->get(route('checkout.index'))->assertOk();
    }

    public function test_guest_checkout_redirects_to_login_then_register_returns_to_checkout_and_keeps_cart(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('login'));

        $guestSessionId = session()->getId();
        $guestCart = $this->createGuestCart($guestSessionId);

        $this->post(route('register.store'), [
            'first_name' => 'مشتری',
            'last_name' => 'جدید',
            'email' => 'checkout-register@example.test',
            'phone' => '09121234567',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms' => '1',
        ])->assertRedirect(route('checkout.index'));

        $customer = User::query()
            ->where('email', 'checkout-register@example.test')
            ->firstOrFail();

        $this->assertSame('customer', $customer->role);
        $this->assertAuthenticatedAs($customer);
        $this->assertSame('converted', $guestCart->fresh()->status);

        $userCart = Cart::query()
            ->where('user_id', $customer->id)
            ->where('status', 'active')
            ->firstOrFail();

        $this->assertSame(1, $userCart->items()->count());
        $this->assertSame(2, (int) $userCart->items()->value('quantity'));

        $this->get(route('checkout.index'))->assertOk();
    }

    public function test_production_gateway_attempt_returns_a_waiting_notice_until_gateway_is_approved(): void
    {
        $customer = User::query()->create([
            'name' => 'مشتری تست',
            'email' => 'checkout-gateway-disabled@example.test',
            'password' => Hash::make('StrongPass123'),
            'role' => 'customer',
        ]);

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'LV-CHECKOUT-GATEWAY-WAIT',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'online',
            'payment_provider' => null,
            'subtotal' => 1250000,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 1250000,
            'first_name' => 'مشتری',
            'last_name' => 'تست',
            'phone' => '09121234567',
            'email' => $customer->email,
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'آدرس تست',
            'postal_code' => '1234567890',
            'unit' => null,
            'notes' => null,
            'installment_enabled' => false,
        ]);

        $this->app->detectEnvironment(fn () => 'production');
        config([
            'payment.production_ready' => false,
            'payment.digipay.enabled' => true,
        ]);

        $this->actingAs($customer)
            ->from(route('checkout.payment', $order))
            ->post(route('checkout.payment.installment', $order), [
                'gateway' => 'digipay',
            ])
            ->assertRedirect(route('checkout.payment', $order))
            ->assertSessionHas('info');

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    private function createGuestCart(string $sessionId): Cart
    {
        $category = Category::query()->create([
            'name' => 'دسته تست Checkout',
            'slug' => 'checkout-auth-flow-category-' . uniqid(),
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'محصول تست Checkout',
            'slug' => 'checkout-auth-flow-product-' . uniqid(),
            'sku' => 'SG-CHECKOUT-AUTH-' . strtoupper(substr(uniqid(), -6)),
            'short_description' => 'محصول تست',
            'description' => 'محصول تست',
            'price' => 1250000,
            'stock' => 5,
            'status' => 'active',
            'installment_enabled' => false,
        ]);

        $cart = Cart::query()->create([
            'user_id' => null,
            'session_id' => $sessionId,
            'status' => 'active',
        ]);

        $cart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 2,
            'unit_price' => 1250000,
        ]);

        return $cart;
    }
}
