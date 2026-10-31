<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoPageSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageSeoController extends Controller
{
    public function index(): View
    {
        return view('admin.seo.pages', [
            'pages' => SeoPageSetting::query()->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pages' => ['required', 'array'],
            'pages.*.meta_title' => ['nullable', 'string', 'max:255'],
            'pages.*.meta_description' => ['nullable', 'string', 'max:1000'],
            'pages.*.canonical_url' => ['nullable', 'url', 'max:2048'],
            'pages.*.seo_index' => ['nullable', 'boolean'],
            'pages.*.seo_follow' => ['nullable', 'boolean'],
        ]);

        $settings = SeoPageSetting::query()->get()->keyBy('id');
        foreach ($validated['pages'] as $id => $data) {
            $page = $settings->get((int) $id);
            if (! $page) {
                continue;
            }

            $page->update([
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'canonical_url' => $data['canonical_url'] ?? null,
                'seo_index' => (bool) ($data['seo_index'] ?? false),
                'seo_follow' => (bool) ($data['seo_follow'] ?? false),
            ]);
        }

        foreach (['home', 'shop', 'categories', 'blog', 'contact'] as $routeKey) {
            Cache::forget("seo_page_settings.{$routeKey}");
        }

        return redirect()->route('admin.seo.pages')->with('success', 'تنظیمات SEO صفحات ثابت ذخیره شد.');
    }
}