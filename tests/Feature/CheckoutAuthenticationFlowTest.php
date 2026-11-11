<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutAuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_redirects_to_login_and_login_returns_to_intended_checkout(): void
    {
        $customer = User::query()->create([
            'name' => 'مشتری تست',
            'email' => 'checkout-login@example.test',
            'password' => Hash::make('StrongPass123'),
            'role' => 'customer',
        ]);

        $this->get(route('checkout.index'))
            ->assertRedirect(route('login'));

        $this->withSession(['url.intended' => route('checkout.index')])
            ->post(route('login.store'), [
                'email' => $customer->email,
                'password' => 'StrongPass123',
            ])
            ->assertRedirect(route('checkout.index'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_guest_checkout_redirects_to_login_and_registration_returns_to_intended_checkout(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('login'));

        $this->withSession(['url.intended' => route('checkout.index')])
            ->post(route('register.store'), [
                'first_name' => 'مشتری',
                'last_name' => 'جدید',
                'email' => 'checkout-register@example.test',
                'phone' => '09121234567',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms' => '1',
            ])
            ->assertRedirect(route('checkout.index'));

        $customer = User::query()
            ->where('email', 'checkout-register@example.test')
            ->firstOrFail();

        $this->assertSame('customer', $customer->role);
        $this->assertAuthenticatedAs($customer);
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

        $this->withoutMiddleware(ValidateCsrfToken::class);
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


    private function createProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'دسته تست Checkout',
            'slug' => 'checkout-auth-flow-category-' . uniqid(),
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return Product::query()->create([
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
    }
}
