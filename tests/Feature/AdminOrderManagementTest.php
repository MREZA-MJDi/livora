<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        return Order::query()->create([
            'order_number' => 'SG-ORDER-TEST-001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 2500000,
            'shipping_cost' => 150000,
            'discount' => 0,
            'total' => 2650000,
            'first_name' => 'مشتری',
            'last_name' => 'آزمایشی',
            'phone' => '09120000000',
            'email' => 'customer@example.test',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'خیابان نمونه، پلاک ۱۰',
            'postal_code' => '1234567890',
        ]);
    }

    public function test_admin_can_find_an_order_and_update_its_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['search' => 'SG-ORDER-TEST-001']))
            ->assertOk()
            ->assertSee('SG-ORDER-TEST-001')
            ->assertSee('مشتری آزمایشی');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('وضعیت سفارش')
            ->assertSee('وضعیت پرداخت');

        $this->patch(route('admin.orders.status', $order), [
            'status' => 'processing',
        ])->assertRedirect(route('admin.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'payment_status' => 'pending',
        ]);
    }

    public function test_admin_order_status_rejects_unknown_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->fresh()->status);
    }
}
