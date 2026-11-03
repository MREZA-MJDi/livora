<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_session_expires_after_thirty_minutes_of_inactivity(): void
    {
        config(['admin.session_timeout_minutes' => 30]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.session.heartbeat'))
            ->assertNoContent();

        $this->travel(31)->minutes();

        $this->post(route('admin.session.heartbeat'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('warning');

        $this->assertGuest();
    }

    public function test_expired_admin_ajax_session_returns_a_structured_419_response(): void
    {
        config(['admin.session_timeout_minutes' => 30]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson(route('admin.session.heartbeat'))
            ->assertNoContent();

        $this->travel(31)->minutes();

        $this->postJson(route('admin.session.heartbeat'))
            ->assertStatus(419)
            ->assertJsonPath('code', 'admin_session_expired')
            ->assertJsonPath('login_url', route('login'));

        $this->assertGuest();
    }

    public function test_admin_timeout_can_be_configured_without_changing_customer_session_policy(): void
    {
        config(['admin.session_timeout_minutes' => 15]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.session.heartbeat'))
            ->assertNoContent();

        $this->travel(16)->minutes();

        $this->post(route('admin.session.heartbeat'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('warning', fn (string $message) => str_contains($message, '15'));

        $this->assertGuest();
    }

    public function test_customer_session_is_not_shortened_by_the_admin_idle_timeout(): void
    {
        config(['admin.session_timeout_minutes' => 30]);

        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('account.index'))
            ->assertOk();

        $this->travel(31)->minutes();

        $this->get(route('account.index'))
            ->assertOk();

        $this->assertAuthenticatedAs($customer);
    }
}
