<?php

namespace Tests\Feature;

use App\Models\SmtpSetting;
use App\Models\ContactSetting;
use App\Services\Mail\ImapInboxReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMailSettingsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_mailbox_is_created_by_the_scoped_vps_provider_and_credentials_are_encrypted(): void
    {
        $this->withoutMiddleware();

        config([
            'mail-provider.api_url' => 'https://mail-api.example.test',
            'mail-provider.api_token' => 'project-scoped-secret',
            'mail-provider.domain' => 'sillashop.com',
            'mail-provider.smtp_host' => 'mail.farajihome.com',
            'mail-provider.imap_host' => 'mail.farajihome.com',
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 'mailbox-test-1',
                    'email' => 'info@sillashop.com',
                ],
            ], 201),
        ]);

        $this->post(route('admin.smtp-settings.mailbox.create'), [
            'local_part' => 'info',
            'quota_mb' => 256,
        ])->assertRedirect(route('admin.smtp-settings.edit'))
            ->assertSessionHas('success');

        $settings = SmtpSetting::query()->firstOrFail();

        $this->assertSame('info@sillashop.com', $settings->mailbox_address);
        $this->assertSame('info@sillashop.com', $settings->username);
        $this->assertSame('info@sillashop.com', $settings->imap_username);
        $this->assertSame($settings->password, $settings->imap_password);
        $this->assertSame('mail.farajihome.com', $settings->host);
        $this->assertSame('mail.farajihome.com', $settings->imap_host);
        $this->assertTrue($settings->enabled);
        $this->assertNotSame($settings->password, DB::table('smtp_settings')->value('password'));

        $generatedPassword = session('mailbox_credentials.password');
        $this->assertNotEmpty($generatedPassword);
        $this->get(route('admin.smtp-settings.edit'))
            ->assertOk()
            ->assertSee($generatedPassword);
        $this->get(route('admin.smtp-settings.edit'))
            ->assertOk()
            ->assertDontSee($generatedPassword);

        Http::assertSent(function (ClientRequest $request): bool {
            return str_ends_with($request->url(), '/api/v1/mailboxes')
                && $request->hasHeader('Authorization', 'Bearer project-scoped-secret')
                && $request['email'] === 'info@sillashop.com'
                && $request['quota_mb'] === 256
                && filled($request['password']);
        });
    }


    public function test_enamad_link_is_generated_from_official_id_and_code_and_rendered_with_uploaded_badge(): void
    {
        $this->withoutMiddleware();
        Storage::fake('public');

        $this->put(route('admin.smtp-settings.update'), [
            'port' => 587,
            'encryption' => 'tls',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'trust_badge_image' => UploadedFile::fake()->image('enamad-badge.png'),
        ])->assertRedirect(route('admin.smtp-settings.edit'));

        $this->put(route('admin.smtp-settings.enamad.update'), [
            'enamad_id' => '123456',
            'enamad_code' => 'OfficialCode_ABC-123',
        ])->assertRedirect(route('admin.smtp-settings.edit'))
            ->assertSessionHas('success');

        $setting = ContactSetting::query()->firstOrFail();
        $this->assertSame(
            'https://trustseal.enamad.ir/?id=123456&Code=OfficialCode_ABC-123',
            $setting->enamad_url
        );

        $this->get('/')
            ->assertOk()
            ->assertSee('https://trustseal.enamad.ir/?id=123456&amp;Code=OfficialCode_ABC-123', false)
            ->assertSee(Storage::disk('public')->url($setting->enamad_image_path), false);
    }

    public function test_enamad_link_builder_rejects_malformed_ids_and_codes(): void
    {
        $this->withoutMiddleware();

        $this->put(route('admin.smtp-settings.enamad.update'), [
            'enamad_id' => 'not-a-number',
            'enamad_code' => 'contains spaces',
        ])->assertRedirect()
            ->assertSessionHasErrors(['enamad_id', 'enamad_code']);
    }

    public function test_enamad_code_extraction_supports_persian_and_arabic_digits(): void
    {
        $reader = new ImapInboxReader();

        $this->assertSame(
            '123456',
            $reader->extractVerificationCode('no-reply@enamad.ir', 'کد تأیید', 'کد شما: ۱۲۳۴۵۶')
        );

        $this->assertSame(
            '654321',
            $reader->extractVerificationCode('no-reply@enamad.ir', 'Verification code', '654321')
        );

        $this->assertNull(
            $reader->extractVerificationCode('orders@example.com', 'Order shipped', 'Your reference is 123456')
        );
    }

    public function test_infor_verification_file_is_written_to_the_public_document_root(): void
    {
        $this->withoutMiddleware();

        $path = public_path('infor');
        $hadExistingFile = is_file($path);
        $existingContents = $hadExistingFile ? file_get_contents($path) : null;

        try {
            $this->post(route('admin.smtp-settings.infor.upload'), [
                'verification_file' => UploadedFile::fake()->createWithContent('infor', 'test-verification-token'),
            ])->assertRedirect()
                ->assertSessionHas('success');

            $this->assertFileExists($path);
            $this->assertSame('test-verification-token', file_get_contents($path));
            $this->assertTrue(str_contains(session('success'), url('/infor')));
        } finally {
            if ($hadExistingFile) {
                file_put_contents($path, $existingContents);
            } elseif (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_infor_upload_rejects_unexpected_file_names(): void
    {
        $this->withoutMiddleware();

        $this->post(route('admin.smtp-settings.infor.upload'), [
            'verification_file' => UploadedFile::fake()->create('unexpected.txt', 1, 'text/plain'),
        ])->assertRedirect()
            ->assertSessionHasErrors('verification_file');
    }

    public function test_enamad_badge_image_is_saved_for_the_storefront_footer(): void
    {
        $this->withoutMiddleware();
        Storage::fake('public');

        $this->put(route('admin.smtp-settings.update'), [
            'port' => 587,
            'encryption' => 'tls',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'trust_badge_image' => UploadedFile::fake()->image('enamad-badge.png'),
        ])->assertRedirect(route('admin.smtp-settings.edit'))
            ->assertSessionHas('success');

        $setting = ContactSetting::query()->first();

        $this->assertNotEmpty($setting?->enamad_image_path);
        Storage::disk('public')->assertExists($setting->enamad_image_path);

        $setting->update([
            'enamad_url' => 'https://trustseal.enamad.ir/?id=12345&Code=verified-code',
        ]);
        cache()->forget('storefront.settings');

        $this->get('/')
            ->assertOk()
            ->assertSee('https://trustseal.enamad.ir/?id=12345&amp;Code=verified-code', false)
            ->assertSee(Storage::disk('public')->url($setting->enamad_image_path), false)
            ->assertSee('alt="نماد اعتماد الکترونیکی"', false);
    }

}
