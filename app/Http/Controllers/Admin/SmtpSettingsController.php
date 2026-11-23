<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use App\Models\ContactSetting;
use App\Services\Mail\ImapInboxReader;
use App\Services\Mail\MailProviderClient;
use RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SmtpSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = SmtpSetting::current();
        $enamadSettings = ContactSetting::query()->first();
        $enamadQuery = [];
        if (filled($enamadSettings?->enamad_url)) {
            parse_str((string) parse_url($enamadSettings->enamad_url, PHP_URL_QUERY), $enamadQuery);
        }

        return view('admin.smtp-settings.edit', [
            'settings' => $settings,
            'mailProviderConfigured' => filled(config('mail-provider.api_url')) && filled(config('mail-provider.api_token')),
            'enamadSettings' => $enamadSettings,
            'enamadId' => $enamadQuery['id'] ?? '',
            'enamadCode' => $enamadQuery['Code'] ?? $enamadQuery['code'] ?? '',
            'hasPassword' => filled($settings->password),
            'mailboxPassword' => $settings->password,
            'trustBadgeImagePath' => $this->trustBadgeImagePath(),
            'inboxMessages' => cache()->get('enamad.inbox.latest', []),
            'mailInboxMessages' => cache()->get('mail.inbox.latest', []),
            'mailInboxLastSync' => cache()->get('mail.inbox.last_sync'),
            'inboxLastSync' => cache()->get('enamad.inbox.last_sync'),
            'inboxLastError' => cache()->get('enamad.inbox.last_error'),
            'inforFileExists' => is_file(public_path('infor')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'host' => is_string($request->input('host')) ? strtolower(rtrim(trim($request->input('host')), '.')) : $request->input('host'),
            'imap_host' => is_string($request->input('imap_host')) ? strtolower(rtrim(trim($request->input('imap_host')), '.')) : $request->input('imap_host'),
        ]);

        $data = $request->validate([
            'host' => ['nullable', 'string', 'max:255', 'regex:/^(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)(?:\\.(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?))*|(?:\\d{1,3}\\.){3}\\d{1,3})\\.?$/'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', 'in:tls,ssl,none'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:1024'],
            'from_address' => ['nullable', 'email:rfc', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'enabled' => ['nullable', 'boolean'],
            'mailbox_address' => ['nullable', 'email:rfc', 'max:255'],
            'imap_host' => ['nullable', 'string', 'max:255', 'regex:/^(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)(?:\\.(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?))*|(?:\\d{1,3}\\.){3}\\d{1,3})\\.?$/'],
            'imap_port' => ['required', 'integer', 'between:1,65535'],
            'imap_encryption' => ['required', 'in:ssl,tls,none'],
            'imap_username' => ['nullable', 'string', 'max:255'],
            'imap_password' => ['nullable', 'string', 'max:1024'],
            'trust_badge_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'host.regex' => 'میزبان SMTP باید نام دامنه یا IP باشد؛ مانند mail.sillashop.com، نه آدرس ایمیل یا URL.',
            'imap_host.regex' => 'میزبان IMAP باید نام دامنه یا IP باشد؛ مانند mail.sillashop.com، نه آدرس ایمیل یا URL.',
        ]);

        $settings = SmtpSetting::current();
        unset($data['password'], $data['imap_password'], $data['trust_badge_image']);

        if ($request->filled('password')) {
            $settings->password = $request->input('password');
        }
        if ($request->filled('imap_password')) {
            $settings->imap_password = $request->input('imap_password');
        }

        $settings->fill($data);
        $settings->enabled = $request->boolean('enabled');
        $settings->save();

        if ($request->hasFile('trust_badge_image')) {
            $newPath = $request->file('trust_badge_image')->store('trust-badges', 'public');
            if (! $newPath) {
                return back()->withErrors(['trust_badge_image' => 'ذخیره تصویر نماد ناموفق بود. تنظیمات ایمیل ذخیره شد؛ تصویر قبلی حفظ شد.']);
            }
            $oldPath = $this->saveTrustBadgeImagePath($newPath);
            if ($oldPath && $oldPath !== $newPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return redirect()->route('admin.smtp-settings.edit')
            ->with('success', 'تنظیمات ایمیل و نماد اعتماد ذخیره شد.');
    }

    public function connectAndVerify(Request $request, ImapInboxReader $reader): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $email = strtolower(trim($data['email']));
        $settings = SmtpSetting::current();
        $mailboxUsername = substr($email, 0, strpos($email, '@'));
        $settings->forceFill([
            'mailbox_address' => $email,
            'host' => 'mail.sillashop.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => $mailboxUsername,
            'password' => $data['password'],
            'from_address' => $email,
            'from_name' => $settings->from_name ?: config('app.name'),
            'imap_host' => 'mail.sillashop.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'imap_username' => $mailboxUsername,
            'imap_password' => $data['password'],
            'enabled' => true,
        ]);

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $settings->host,
            'port' => $settings->port,
            'scheme' => 'smtp',
            'auto_tls' => true,
            'username' => $settings->username,
            'password' => $settings->password,
            'timeout' => 15,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name);
        Mail::purge('smtp');

        $token = (string) \Illuminate\Support\Str::uuid();
        $subject = 'Livora mailbox verification '.$token;

        try {
            Mail::mailer('smtp')->raw(
                'This is an automatic connection test for '.$email.'. Reference: '.$token,
                fn ($message) => $message->to($email)->subject($subject)
            );

            $messages = [];
            $received = false;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $messages = $reader->recentMessages($settings, 20);
                foreach ($messages as $message) {
                    if (str_contains((string) ($message['subject'] ?? ''), $token)) {
                        $received = true;
                        break 2;
                    }
                }
                if ($attempt < 2) {
                    sleep(2);
                }
            }

            $settings->save();
            cache()->put('mail.inbox.latest', $messages, now()->addDays(7));
            cache()->put('mail.inbox.last_sync', now()->toIso8601String(), now()->addDays(7));
            cache()->forget('mail.inbox.last_error');

            if (! $received) {
                return redirect()->route('admin.smtp-settings.edit')
                    ->with('warning', 'ارسال SMTP موفق بود؛ اما پیام آزمایشی هنوز در IMAP دیده نشد. تنظیم تحویل محلی و احراز هویت صندوق را بررسی کنید.');
            }
        } catch (Throwable $exception) {
            report($exception);
            cache()->put('mail.inbox.last_error', 'بررسی اتصال ناموفق بود؛ گزارش فنی در لاگ سرور ثبت شد.', now()->addHours(2));

            return redirect()->route('admin.smtp-settings.edit')
                ->withErrors(['mail' => 'اتصال کامل نشد. میزبان، رمز صندوق و تنظیمات TLS سرور را بررسی کنید.']);
        } finally {
            Mail::purge('smtp');
        }

        return redirect()->route('admin.smtp-settings.edit')
            ->with('success', 'اتصال SMTP و IMAP تأیید شد؛ پیام آزمایشی ارسال و از صندوق دریافت شد.');
    }
    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_recipient' => ['required', 'email:rfc', 'max:255'],
        ]);

        $settings = SmtpSetting::current();
        if (! $settings->enabled || blank($settings->host) || blank($settings->from_address)) {
            return back()->withErrors(['smtp' => 'ابتدا SMTP را فعال و میزبان و ایمیل فرستنده را کامل کنید.']);
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $settings->host,
            'port' => $settings->port,
            'scheme' => $settings->encryption === 'ssl' ? 'smtps' : 'smtp',
            'auto_tls' => $settings->encryption !== 'none',
            'username' => $settings->username,
            'password' => $settings->password,
            'timeout' => 15,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name ?: config('app.name'));
        Mail::purge('smtp');

        try {
            Mail::mailer('smtp')->raw(
                'این پیام برای بررسی اتصال SMTP سایت ارسال شده است.',
                fn ($message) => $message->to($data['test_recipient'])->subject('تست اتصال ایمیل سایت')
            );
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['smtp' => 'ارسال پیام آزمایشی ناموفق بود. میزبان، پورت، رمزنگاری و اطلاعات ورود را بررسی کنید.']);
        } finally {
            Mail::purge('smtp');
        }

        return back()->with('success', 'پیام آزمایشی با موفقیت ارسال شد.');
    }

    public function syncInbox(ImapInboxReader $reader): RedirectResponse
    {
        try {
            $messages = $reader->recentVerificationMessages(SmtpSetting::current());
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['imap' => $exception->getMessage()]);
        }

        cache()->put('enamad.inbox.latest', $messages, now()->addDays(7));
        cache()->put('enamad.inbox.last_sync', now()->toIso8601String(), now()->addDays(7));
        cache()->forget('enamad.inbox.last_error');

        return back()
            ->with('success', count($messages) ? 'ایمیل‌های مرتبط با اینماد دریافت شدند.' : 'ایمیل مرتبط با اینماد در پیام‌های اخیر پیدا نشد.');
    }



    public function createMailbox(Request $request, MailProviderClient $provider): RedirectResponse
    {
        $data = $request->validate([
            'local_part' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9._+-]{0,63}$/', 'not_regex:/\.\./', 'not_regex:/\.$/'],
            'quota_mb' => ['nullable', 'integer', 'between:64,4096'],
        ]);

        $settings = SmtpSetting::current();
        if (filled($settings->mailbox_address)) {
            return back()->withErrors(['mailbox' => 'برای این پروژه یک صندوق اصلی ثبت شده است: '.$settings->mailbox_address]);
        }

        $password = (string) config('mail-provider.mailbox_password');
        if ($password === '' || strlen($password) < 16) {
            return back()->withErrors(['mailbox' => 'رمز ثابت صندوق باید در MAIL_PROVIDER_MAILBOX_PASSWORD تنظیم شود و حداقل ۱۶ نویسه داشته باشد.']);
        }

        try {
            $mailbox = $provider->createMailbox(
                $data['local_part'],
                $password,
                (int) ($data['quota_mb'] ?? config('mail-provider.quota_mb', 512))
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'mailbox' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'ایجاد صندوق ایمیل ناموفق بود. اتصال سرویس مرکزی را بررسی کنید.',
            ]);
        }

        $address = $mailbox['email'];
        $settings->forceFill([
            'mailbox_address' => $address,
            'username' => $address,
            'password' => $password,
            'imap_username' => $address,
            'imap_password' => $password,
            'host' => config('mail-provider.smtp_host'),
            'port' => config('mail-provider.smtp_port', 587),
            'encryption' => config('mail-provider.smtp_encryption', 'tls'),
            'imap_host' => config('mail-provider.imap_host'),
            'imap_port' => config('mail-provider.imap_port', 993),
            'imap_encryption' => config('mail-provider.imap_encryption', 'ssl'),
            'from_address' => $address,
            'from_name' => $settings->from_name ?: config('app.name'),
            'enabled' => true,
        ])->save();

        return redirect()->route('admin.smtp-settings.edit')
            ->with('success', 'صندوق '.$address.' با موفقیت ساخته شد.')
            ->with('mailbox_credentials', [
                'email' => $address,
                'password' => $password,
            ]);
    }

    public function saveEnamadSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enamad_id' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'enamad_code' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
        ], [
            'enamad_id.required' => 'شناسه نماد اینماد الزامی است.',
            'enamad_id.regex' => 'شناسه اینماد باید فقط شامل رقم باشد.',
            'enamad_code.required' => 'کد تأیید/Code اینماد الزامی است.',
            'enamad_code.regex' => 'فرمت کد اینماد معتبر نیست.',
        ]);

        $url = 'https://trustseal.enamad.ir/?'.http_build_query([
            'id' => $data['enamad_id'],
            'Code' => $data['enamad_code'],
        ], '', '&', PHP_QUERY_RFC3986);

        $settings = ContactSetting::query()->first() ?? new ContactSetting();
        $settings->enamad_url = $url;
        $settings->save();

        cache()->forget('storefront.settings');

        return redirect()->route('admin.smtp-settings.edit')
            ->with('success', 'لینک استعلام رسمی اینماد ساخته و برای نمایش در فوتر ذخیره شد.');
    }

    public function uploadVerificationFile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'verification_file' => ['required', 'file', 'max:256'],
        ]);

        $file = $data['verification_file'];
        $originalName = strtolower($file->getClientOriginalName());
        $mime = $file->getMimeType();

        if (! in_array($originalName, ['infor', 'infor.txt'], true)
            || ! in_array($mime, ['text/plain', 'application/octet-stream'], true)) {
            return back()->withErrors([
                'verification_file' => 'فقط فایل متنی infor یا infor.txt قابل بارگذاری است.',
            ]);
        }

        try {
            $file->move(public_path(), 'infor');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['verification_file' => 'ذخیره فایل تأیید در ریشه سایت ناموفق بود.']);
        }

        return back()->with('success', 'فایل تأیید اینماد در ریشه سایت قرار گرفت: '.url('/infor'));
    }

    public function syncFullInbox(ImapInboxReader $reader): RedirectResponse
    {
        try {
            $messages = $reader->recentMessages(SmtpSetting::current(), 30);
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['imap' => 'خواندن صندوق ناموفق بود. اطلاعات ورود و اتصال IMAP را بررسی کنید.']);
        }

        cache()->put('mail.inbox.latest', $messages, now()->addDays(7));
        cache()->put('mail.inbox.last_sync', now()->toIso8601String(), now()->addDays(7));

        return back()->with('success', 'صندوق ورودی به‌روزرسانی شد؛ '.count($messages).' پیام دریافت شد.');
    }

    public function inboxStatus(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'messages' => cache()->get('enamad.inbox.latest', []),
            'last_sync' => cache()->get('enamad.inbox.last_sync'),
            'error' => cache()->get('enamad.inbox.last_error'),
        ]);
    }

    private function trustBadgeImagePath(): ?string
    {
        return ContactSetting::query()->value('enamad_image_path');
    }

    private function saveTrustBadgeImagePath(string $path): ?string
    {
        $setting = ContactSetting::query()->first() ?? new ContactSetting();
        $oldPath = $setting->enamad_image_path;
        $setting->enamad_image_path = $path;
        $setting->save();
        cache()->forget('storefront.settings');
        return $oldPath;
    }
}
