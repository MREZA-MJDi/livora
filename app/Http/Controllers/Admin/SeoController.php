<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function edit(): View
    {
        return view('admin.seo.edit', [
            'settings' => SeoSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'default_title' => ['nullable', 'string', 'max:255'],
            'default_description' => ['nullable', 'string', 'max:1000'],
        ]);

        SeoSetting::current()->update([
            ...$validated,
            'indexing_enabled' => $request->boolean('indexing_enabled'),
            'following_enabled' => $request->boolean('following_enabled'),
        ]);

        return redirect()->route('admin.seo.edit')
            ->with('success', 'تنظیمات SEO ذخیره شد.');
    }
}
