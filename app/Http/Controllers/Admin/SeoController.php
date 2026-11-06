<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Models\ContactSetting;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Product;
use App\Support\MediaLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function edit(): View
    {
        return view('admin.seo.edit', [
            'settings' => SeoSetting::current()->load('defaultOgMedia'),
            'brandSettings' => ContactSetting::query()->first(),
        ]);
    }


    public function publicRoutes(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        $routes = collect([
            ['label' => 'صفحه اصلی', 'url' => route('home'), 'kind' => 'صفحه ثابت'],
            ['label' => 'فروشگاه', 'url' => route('shop.index'), 'kind' => 'صفحه ثابت'],
            ['label' => 'فهرست دسته‌بندی‌ها', 'url' => route('categories.index'), 'kind' => 'صفحه ثابت'],
            ['label' => 'وبلاگ', 'url' => route('blog.index'), 'kind' => 'صفحه ثابت'],
            ['label' => 'درباره ما', 'url' => route('about'), 'kind' => 'صفحه ثابت'],
            ['label' => 'قوانین و شرایط', 'url' => route('terms'), 'kind' => 'صفحه ثابت'],
            ['label' => 'تماس با ما', 'url' => route('contact'), 'kind' => 'صفحه ثابت'],
        ]);

        $products = Product::query()->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when($term !== '', fn ($query) => $query->where(fn ($search) => $search->where('name', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%")))
            ->orderBy('name')->limit(40)->get(['name', 'slug']);

        $categories = Category::query()->active()
            ->when($term !== '', fn ($query) => $query->where(fn ($search) => $search->where('name', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%")))
            ->orderBy('name')->limit(40)->get(['name', 'slug']);

        $articles = BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($term !== '', fn ($query) => $query->where(fn ($search) => $search->where('title', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%")))
            ->orderByDesc('published_at')->limit(40)->get(['title', 'slug']);

        $pages = ContentPage::query()
            ->where('status', 'published')
            ->when($term !== '', fn ($query) => $query->where(fn ($search) => $search->where('title', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%")))
            ->orderBy('title')->limit(40)->get(['title', 'slug']);

        foreach ($pages as $page) {
            $routes->push(['label' => 'صفحه: '.$page->title, 'url' => route('pages.show', $page->slug), 'kind' => 'صفحه منتشرشده']);
        }

        foreach ($articles as $article) {
            $routes->push(['label' => 'مقاله: '.$article->title, 'url' => route('blog.show', $article->slug), 'kind' => 'مقاله منتشرشده']);
        }

        foreach ($categories as $category) {
            $routes->push(['label' => 'دسته‌بندی: '.$category->name, 'url' => route('categories.show', $category->slug), 'kind' => 'دسته‌بندی']);
        }
        foreach ($products as $product) {
            $routes->push(['label' => 'محصول: '.$product->name, 'url' => route('product.show', $product->slug), 'kind' => 'محصول']);
        }

        if ($term !== '') {
            $routes = $routes->filter(fn ($item) => str_contains(mb_strtolower($item['label']), mb_strtolower($term))
                || str_contains(mb_strtolower($item['url']), mb_strtolower($term)));
        }

        return response()->json(['data' => $routes->values()->take(100)]);
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
            'default_og_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'remove_default_og_image' => ['nullable', 'boolean'],
            'organization_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $settings = SeoSetting::current();
        $brandSettings = ContactSetting::query()->first();
        $oldFiles = [];
        $newFiles = [];

        foreach ([] as $field => $folder) {
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
            'site_name' => $brandSettings?->brand_name ?: config('app.name'),
            'default_title' => $validated['default_title'] ?? null,
            'default_description' => $validated['default_description'] ?? null,
            'twitter_handle' => $validated['twitter_handle'] ?? null,
            'google_site_verification' => $validated['google_site_verification'] ?? null,
            'bing_site_verification' => $validated['bing_site_verification'] ?? null,
            'organization_name' => $brandSettings?->brand_name ?: config('app.name'),
            'organization_description' => $brandSettings?->brand_description,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'social_links' => array_filter($validated['social_links'] ?? [], fn ($url) => filled($url)),
        ];

        if ($request->boolean('remove_default_og_image')) {
            $data['default_og_image'] = null;
            $data['default_og_media_id'] = null;
        } elseif ($request->hasFile('default_og_image')) {
            $ogMedia = MediaLibrary::store($request->file('default_og_image'), (int) $request->user()->id, 'seo', null);
            $data['default_og_image'] = $ogMedia->path;
            $data['default_og_media_id'] = $ogMedia->id;
        } elseif (filled($request->input('default_og_media_id'))) {
            $ogMedia = MediaLibrary::image($request->input('default_og_media_id'));
            $data['default_og_image'] = $ogMedia->path;
            $data['default_og_media_id'] = $ogMedia->id;
        }

        $data['organization_logo'] = $brandSettings?->logo_path;

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