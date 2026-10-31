<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function edit(): View
    {
        return view('admin.seo.edit', ['settings' => SeoSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'default_title' => ['nullable', 'string', 'max:255'],
            'default_description' => ['nullable', 'string', 'max:1000'],
            'twitter_handle' => ['nullable', 'string', 'max:80', 'regex:/^@?[A-Za-z0-9_]*$/'],
            'google_site_verification' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]*$/'],
            'bing_site_verification' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]*$/'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_description' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url', 'max:2048'],
            'default_og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'organization_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $settings = SeoSetting::current();
        $oldFiles = [
            'default_og_image' => $settings->default_og_image,
            'organization_logo' => $settings->organization_logo,
        ];
        $newFiles = [];

        foreach (['default_og_image' => 'seo', 'organization_logo' => 'brand'] as $field => $folder) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store($folder, 'public');
                if (! $path) {
                    foreach ($newFiles as $newFile) {
                        Storage::disk('public')->delete($newFile);
                    }
                    throw new \RuntimeException("Unable to store {$field}.");
                }
                $newFiles[$field] = $path;
            }
        }

        $data = [
            'site_name' => 'SillaGallery',
            'default_title' => $validated['default_title'] ?? null,
            'default_description' => $validated['default_description'] ?? null,
            'twitter_handle' => $validated['twitter_handle'] ?? null,
            'google_site_verification' => $validated['google_site_verification'] ?? null,
            'bing_site_verification' => $validated['bing_site_verification'] ?? null,
            'organization_name' => $validated['organization_name'] ?? 'SillaGallery',
            'organization_description' => $validated['organization_description'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'social_links' => array_filter($validated['social_links'] ?? [], fn ($url) => filled($url)),
            'indexing_enabled' => $request->boolean('indexing_enabled'),
            'following_enabled' => $request->boolean('following_enabled'),
        ];

        foreach ($newFiles as $field => $path) {
            $data[$field] = $path;
        }

        $settings->update($data);
        \Illuminate\Support\Facades\Cache::forget('seo_settings.current');

        foreach ($newFiles as $field => $path) {
            $old = $oldFiles[$field] ?? null;
            if ($old && $old !== $path && ! str_starts_with($old, 'http://') && ! str_starts_with($old, 'https://')) {
                Storage::disk('public')->delete($old);
            }
        }

        return redirect()->route('admin.seo.edit')->with('success', 'تنظیمات SEO و اطلاعات برند ذخیره شد.');
    }
}