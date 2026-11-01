<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\SeoSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactSettingController extends Controller
{
    public function edit(): View
    {
        $settings = ContactSetting::query()->firstOrNew(['id' => 1]);

        return view('admin.contact-settings.edit', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'hours' => ['nullable', 'string', 'max:1000'],
            'phone_label' => ['required', 'string', 'max:120'],
            'email_label' => ['required', 'string', 'max:120'],
            'support_label' => ['required', 'string', 'max:120'],
            'online_label' => ['required', 'string', 'max:120'],
            'support_description' => ['nullable', 'string', 'max:1000'],
            'online_description' => ['nullable', 'string', 'max:1000'],
            'faqs' => ['nullable', 'array', 'max:20'],
            'faqs.*.question' => ['required', 'string', 'max:255'],
            'faqs.*.answer' => ['required', 'string', 'max:2000'],
            'page_content' => ['nullable', 'array'],
            'page_content.hero_kicker' => ['nullable', 'string', 'max:120'],
            'page_content.hero_title' => ['nullable', 'string', 'max:255'],
            'page_content.hero_highlight' => ['nullable', 'string', 'max:120'],
            'page_content.hero_description' => ['nullable', 'string', 'max:1000'],
            'page_content.hero_cta' => ['nullable', 'string', 'max:120'],
            'page_content.breadcrumb_label' => ['nullable', 'string', 'max:120'],
            'page_content.address_label' => ['nullable', 'string', 'max:120'],
            'page_content.hours_label' => ['nullable', 'string', 'max:120'],
            'page_content.installment_label' => ['nullable', 'string', 'max:120'],
            'page_content.info_kicker' => ['nullable', 'string', 'max:120'],
            'page_content.info_title' => ['nullable', 'string', 'max:255'],
            'page_content.info_description' => ['nullable', 'string', 'max:1000'],
            'page_content.installment_note' => ['nullable', 'string', 'max:1000'],
            'page_content.form_kicker' => ['nullable', 'string', 'max:120'],
            'page_content.form_title' => ['nullable', 'string', 'max:255'],
            'page_content.form_description' => ['nullable', 'string', 'max:1000'],
            'page_content.faq_kicker' => ['nullable', 'string', 'max:120'],
            'page_content.faq_title' => ['nullable', 'string', 'max:255'],
            'page_content.faq_description' => ['nullable', 'string', 'max:1000'],
            'page_content.final_kicker' => ['nullable', 'string', 'max:120'],
            'page_content.final_title' => ['nullable', 'string', 'max:255'],
            'page_content.final_description' => ['nullable', 'string', 'max:1000'],
            'page_content.final_cta' => ['nullable', 'string', 'max:120'],
            'page_content.success_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['faqs'] = collect($validated['faqs'] ?? [])
            ->map(fn (array $faq): array => [
                'question' => trim($faq['question']),
                'answer' => trim($faq['answer']),
            ])
            ->filter(fn (array $faq): bool => $faq['question'] !== '' && $faq['answer'] !== '')
            ->values()
            ->all();
        $validated['page_content'] = collect($validated['page_content'] ?? [])
            ->map(fn ($value): string => trim((string) $value))
            ->all();
        $validated['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($validated): void {
            ContactSetting::query()->updateOrCreate(['id' => 1], $validated);

            $seo = SeoSetting::current();
            $seo->update([
                'contact_email' => $validated['is_active'] ? ($validated['email'] ?? null) : null,
                'contact_phone' => $validated['is_active'] ? ($validated['phone'] ?? null) : null,
            ]);
        });
        Cache::forget('seo_settings.current');

        return redirect()
            ->route('admin.contact-settings.edit')
            ->with('success', 'اطلاعات تماس و پرسش‌های متداول ذخیره شد.');
    }
}
