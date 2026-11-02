<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopBannerSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ShopBannerController extends Controller
{
    public function edit(): View
    {
        return view('admin.shop-banner.edit', [
            'settings' => ShopBannerSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'eyebrow' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1200'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'banner_image_alt' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'remove_banner' => ['nullable', 'boolean'],
        ], [
            'banner_image.image' => 'فایل بنر باید یک تصویر معتبر باشد.',
            'banner_image.mimes' => 'فرمت بنر باید JPG، PNG یا WEBP باشد.',
            'banner_image.max' => 'حجم بنر نباید بیشتر از ۱۰ مگابایت باشد.',
            'title.required' => 'عنوان بنر الزامی است.',
        ]);

        $settings = ShopBannerSetting::current();
        $oldImage = $settings->banner_image;
        $newImage = null;

        if ($request->hasFile('banner_image')) {
            $newImage = $request->file('banner_image')->store('shop/banner', 'public');

            if (! $newImage) {
                throw new \RuntimeException('Shop banner could not be stored.');
            }
        }

        $removeBanner = $request->boolean('remove_banner');
        $data = [
            'eyebrow' => trim($validated['eyebrow']),
            'title' => trim($validated['title']),
            'description' => trim((string) ($validated['description'] ?? '')),
            'banner_image_alt' => trim((string) ($validated['banner_image_alt'] ?? '')),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($newImage) {
            $data['banner_image'] = $newImage;
        } elseif ($removeBanner) {
            $data['banner_image'] = null;
        }

        try {
            DB::transaction(fn () => $settings->update($data));
        } catch (\Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }
            throw $exception;
        }

        $imageChanged = $newImage !== null || $removeBanner;
        if ($imageChanged && $oldImage && ! str_starts_with($oldImage, 'http://') && ! str_starts_with($oldImage, 'https://') && ! str_starts_with($oldImage, '//')) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('admin.shop-banner.edit')
            ->with('success', 'بنر صفحه فروشگاه ذخیره شد.');
    }
}
