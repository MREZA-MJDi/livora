<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.recaptcha.site_key' => 'test-site-key',
            'services.recaptcha.secret_key' => 'test-secret-key',
            'services.recaptcha.allowed_hosts' => ['localhost'],
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);
    }

    public function test_registration_is_blocked_when_recaptcha_verification_fails(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'robot@example.test',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms' => '1',
                'g-recaptcha-response' => 'invalid-token',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('users', 0);
        $this->assertRecaptchaWasCalled();
    }

    public function test_checkout_order_creation_is_blocked_when_recaptcha_verification_fails(): void
    {
        $customer = User::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.test',
            'password' => 'hashed-test-password',
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.place'), [
                'g-recaptcha-response' => 'invalid-token',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('orders', 0);
        $this->assertRecaptchaWasCalled();
    }

    public function test_contact_messages_are_blocked_when_recaptcha_verification_fails(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'g-recaptcha-response' => 'invalid-token',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertRecaptchaWasCalled();
    }

    protected function assertRecaptchaWasCalled(): void
    {
        Http::assertSent(fn ($request): bool =>
            $request->url() === 'https://www.google.com/recaptcha/api/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'invalid-token'
        );
    }
}
