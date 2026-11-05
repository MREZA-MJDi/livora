<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileProtectionTest extends TestCase
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.turnstile.site_key' => 'test-site-key',
            'services.turnstile.secret' => 'test-secret-key',
            'services.turnstile.hostnames' => ['localhost'],
        ]);

        Http::fake([
            self::VERIFY_URL => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);
    }

    public function test_login_is_blocked_when_turnstile_verification_fails(): void
    {
        User::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.test',
            'password' => bcrypt('StrongPass123'),
            'role' => 'customer',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'customer@example.test',
                'password' => 'StrongPass123',
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('captcha');

        $this->assertGuest();
        $this->assertTurnstileWasCalled('invalid-token');
    }

    public function test_registration_is_blocked_when_turnstile_verification_fails(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'robot@example.test',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms' => '1',
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('users', 0);
        $this->assertTurnstileWasCalled('invalid-token');
    }

    public function test_registration_accepts_a_valid_signup_token(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response([
                'success' => true,
                'hostname' => parse_url(route('register'), PHP_URL_HOST),
                'action' => 'signup',
                'challenge_ts' => now()->toIso8601String(),
            ], 200),
        ]);

        $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'human@example.test',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms' => '1',
            'cf-turnstile-response' => 'valid-test-token',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'human@example.test',
            'role' => 'customer',
        ]);
        $this->assertTurnstileWasCalled('valid-test-token');
    }

    public function test_registration_rejects_a_token_for_a_different_action(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response([
                'success' => true,
                'hostname' => parse_url(route('register'), PHP_URL_HOST),
                'action' => 'contact',
            ], 200),
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'wrong-action@example.test',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms' => '1',
                'cf-turnstile-response' => 'wrong-action-token',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_checkout_order_creation_is_blocked_when_turnstile_verification_fails(): void
    {
        $customer = User::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.test',
            'password' => bcrypt('StrongPass123'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.place'), [
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('orders', 0);
        $this->assertTurnstileWasCalled('invalid-token');
    }

    public function test_contact_messages_are_blocked_when_turnstile_verification_fails(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertTurnstileWasCalled('invalid-token');
    }

    private function assertTurnstileWasCalled(string $token): void
    {
        Http::assertSent(fn ($request): bool =>
            $request->url() === self::VERIFY_URL
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === $token
        );
    }
}
