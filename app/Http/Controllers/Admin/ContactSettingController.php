<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\SeoSetting;
use App\Support\MediaLibrary;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\ContactSettingRequest;
use Illuminate\View\View;

class ContactSettingController extends Controller
{
    public function edit(): View
    {
        $settings = ContactSetting::query()->with('logoMedia')->firstOrNew(['id' => 1]);

        return view('admin.contact-settings.edit', [
            'settings' => $settings,
        ]);
    }

    public function update(ContactSettingRequest $request): RedirectResponse
    {
        $validated = array_merge($request->validated(), $request->validate([
            'brand_description' => ['nullable', 'string', 'max:3000'],
            'enamad_url' => ['nullable', 'url', 'max:2048'],
            'samandehi_url' => ['nullable', 'url', 'max:2048'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'footer_menu' => ['nullable', 'array'],
            'footer_menu.shop' => ['nullable', 'array', 'max:12'],
            'footer_menu.guide' => ['nullable', 'array', 'max:12'],
            'footer_menu.contact' => ['nullable', 'array', 'max:12'],
            'footer_menu.*.*.label' => ['required', 'string', 'max:120'],
            'footer_menu.*.*.url' => ['required', 'string', 'max:2048', function ($attribute, $value, $fail): void { if (! str_starts_with($value, '/') && ! str_starts_with($value, 'https://')) $fail('لینک فوتر باید مسیر داخلی یا HTTPS باشد.'); }],
            'payment_methods' => ['nullable', 'array', 'max:12'],
            'payment_methods.*.label' => ['required', 'string', 'max:120'],
            'payment_methods.*.image' => ['nullable', 'url', 'max:2048'],
            'payment_methods.*.url' => ['nullable', 'url', 'max:2048'],
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
        ]));

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
        $validated['footer_menu'] = collect($validated['footer_menu'] ?? [])
            ->map(fn ($links) => collect($links ?? [])->filter(fn ($link) => filled($link['label'] ?? null) && filled($link['url'] ?? null))->map(fn ($link) => ['label' => trim($link['label']), 'url' => trim($link['url'])])->values()->all())
            ->all();
        $validated['payment_methods'] = collect($validated['payment_methods'] ?? [])
            ->filter(fn ($item) => filled($item['label'] ?? null))
            ->map(fn ($item) => ['label' => trim($item['label']), 'image' => filled($item['image'] ?? null) ? trim($item['image']) : null, 'url' => filled($item['url'] ?? null) ? trim($item['url']) : null])
            ->values()->all();
        if ($request->boolean('remove_logo')) {
            $validated['logo_path'] = null;
            $validated['logo_media_id'] = null;
        } elseif ($request->hasFile('logo_image')) {
            $logoMedia = MediaLibrary::store($request->file('logo_image'), (int) $request->user()->id, 'brand', $validated['brand_name'] ?? null);
            $validated['logo_path'] = $logoMedia->path;
            $validated['logo_media_id'] = $logoMedia->id;
        } elseif (filled($validated['logo_media_id'] ?? null)) {
            $logoMedia = MediaLibrary::image($validated['logo_media_id']);
            $validated['logo_path'] = $logoMedia?->path;
            $validated['logo_media_id'] = $logoMedia?->id;
        } else {
            unset($validated['logo_media_id']);
        }
        unset($validated['logo_image'], $validated['remove_logo']);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['floating_actions_enabled'] = $request->boolean('floating_actions_enabled');

        DB::transaction(function () use ($validated): void {
            $brandSettings = ContactSetting::query()->updateOrCreate(['id' => 1], $validated);

            $seo = SeoSetting::current();
            $brandName = trim((string) ($brandSettings->brand_name ?? config('app.name')));
            $seo->update([
                'site_name' => $brandName,
                'organization_name' => $brandName,
                'organization_description' => $brandSettings->brand_description,
                'organization_logo' => $brandSettings->logo_path,
                'contact_email' => $brandSettings->is_active ? $brandSettings->email : null,
                'contact_phone' => $brandSettings->is_active ? $brandSettings->phone : null,
            ]);
        });
        Cache::forget('seo_settings.current');
        Cache::forget('storefront.settings');

        return redirect()
            ->route('admin.contact-settings.edit')
            ->with('success', 'اطلاعات تماس و پرسش‌های متداول ذخیره شد.');
    }
}
