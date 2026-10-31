<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use App\Support\HtmlSanitizer;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    private const RESERVED = [
        'admin', 'api', 'shop', 'product', 'categories', 'blog', 'contact', 'about', 'terms',
        'cart', 'checkout', 'account', 'login', 'register', 'logout',
        'sitemap.xml', 'storage', 'up', 'password', 'reset-password',
    ];

    public function index(Request $request): View
    {
        $pages = ContentPage::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->input('q'));
                $query->where(fn ($sub) => $sub
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%"));
            })
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'bodyHtml' => HtmlSanitizer::sanitize((string) old('body', '')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $image = $request->file('featured_image')?->store('pages', 'public');
        if ($request->hasFile('featured_image') && ! $image) {
            throw new \RuntimeException('Page image could not be stored.');
        }

        try {
            DB::transaction(function () use ($request, $data, $image): void {
                $data['user_id'] = $request->user()->id;
                $data['body'] = HtmlSanitizer::sanitize($data['body']);
                $data['featured_image'] = $image;
                $data['published_at'] = $data['status'] === 'published'
                    ? ($data['published_at'] ?? now())
                    : null;
                ContentPage::create($data);
            });
        } catch (\Throwable $exception) {
            if ($image) {
                Storage::disk('public')->delete($image);
            }
            throw $exception;
        }

        return redirect()->route('admin.pages.index')->with('success', 'صفحه ذخیره شد.');
    }

    public function edit(ContentPage $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'bodyHtml' => HtmlSanitizer::sanitize((string) old('body', $page->body)),
        ]);
    }

    public function update(Request $request, ContentPage $page): RedirectResponse
    {
        $data = $this->validated($request, $page);
        $oldImage = $page->featured_image;
        unset($data['featured_image']);
        $newImage = $request->file('featured_image')?->store('pages', 'public');
        if ($request->hasFile('featured_image') && ! $newImage) {
            throw new \RuntimeException('Page image could not be stored.');
        }

        try {
            DB::transaction(function () use ($request, $data, $page, $newImage): void {
                $data['body'] = HtmlSanitizer::sanitize($data['body']);
                if ($newImage) {
                    $data['featured_image'] = $newImage;
                }
                $data['published_at'] = $data['status'] === 'published'
                    ? ($data['published_at'] ?? $page->published_at ?? now())
                    : null;
                $page->update($data);
            });
        } catch (\Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }
            throw $exception;
        }

        if ($newImage && $oldImage && $oldImage !== $newImage && ! str_starts_with($oldImage, 'http') && ! str_starts_with($oldImage, '//')) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.pages.edit', $page)->with('success', 'صفحه بروزرسانی شد.');
    }

    public function destroy(ContentPage $page): RedirectResponse
    {
        if (in_array($page->slug, ['about', 'terms'], true)) {
            return back()->with('error', 'صفحات اصلی درباره ما و قوانین را حذف نکنید؛ می‌توانید آن‌ها را ویرایش یا پیش‌نویس کنید.');
        }

        $image = $page->featured_image;
        $page->delete();
        if ($image && ! str_starts_with($image, 'http') && ! str_starts_with($image, '//')) {
            Storage::disk('public')->delete($image);
        }

        return redirect()->route('admin.pages.index')->with('success', 'صفحه حذف شد.');
    }

    private function validated(Request $request, ?ContentPage $page = null): array
    {
        // Built-in pages are bound to explicit /about and /terms routes.
        // Their slugs are immutable so editing their content cannot break navigation.
        if ($page && in_array($page->slug, ['about', 'terms'], true)) {
            $slug = $page->slug;
        } else {
            $slug = trim((string) $request->input('slug'));
            if ($slug === '') {
                $slug = UniqueSlug::make((string) $request->input('title'), 'content_pages');
            }
        }
        $request->merge([
            'slug' => $slug,
            'seo_index' => $request->boolean('seo_index'),
            'seo_follow' => $request->boolean('seo_follow'),
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(self::RESERVED),
                Rule::unique('content_pages', 'slug')->ignore($page),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:2000000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'seo_index' => ['boolean'],
            'seo_follow' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}