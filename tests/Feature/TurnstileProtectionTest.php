<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileProtectionTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.turnstile.site_key' => 'test-site-key',
            'services.turnstile.secret' => 'test-secret-key',
            'services.turnstile.hostnames' => ['localhost'],
        ]);

    }

    public function test_turnstile_widget_renders_with_public_site_key_when_secret_is_missing(): void
    {
        config(['services.turnstile.secret' => null]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('class="cf-turnstile"', false)
            ->assertSee('data-sitekey="test-site-key"', false);
    }

    public function test_turnstile_widget_uses_the_action_expected_by_each_submission_route(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-action="login"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-action="signup"', false);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('data-action="contact"', false);
    }

    public function test_login_is_blocked_when_turnstile_verification_fails(): void
    {
        $this->fakeTurnstileFailure();

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
        $this->fakeTurnstileFailure();

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
        config([
            'services.turnstile.hostnames' => [parse_url(route('register'), PHP_URL_HOST)],
        ]);

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
        ])->assertRedirect(route('account.index'))
            ->assertSessionHasNoErrors();

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
        $this->fakeTurnstileFailure();

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
        $this->fakeTurnstileFailure();

        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertTurnstileWasCalled('invalid-token');
    }

    private function fakeTurnstileFailure(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);
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
