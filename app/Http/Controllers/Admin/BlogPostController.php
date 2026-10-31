<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::query()->with('author')->latest()->paginate(15);

        return view('admin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.blog.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $imagePath = null;

        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('blog', 'public');

            if (! $imagePath) {
                throw new \RuntimeException('Blog featured image could not be stored.');
            }
        }

        $validated['featured_image'] = $imagePath;
        $validated['user_id'] = $request->user()->id;
        $validated['published_at'] = $validated['status'] === 'published'
            ? ($validated['published_at'] ?? now())
            : null;

        try {
            BlogPost::create($validated);
        } catch (\Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()->route('admin.blog.index')
            ->with('success', 'مقاله ذخیره شد.');
    }

    public function edit(BlogPost $blog): View
    {
        return view('admin.blog.edit', ['post' => $blog]);
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $validated = $this->validatedData($request, $blog);
        $oldImage = $blog->featured_image;
        $newImage = null;

        if ($request->hasFile('featured_image')) {
            $newImage = $request->file('featured_image')->store('blog', 'public');

            if (! $newImage) {
                throw new \RuntimeException('Blog featured image could not be stored.');
            }

            $validated['featured_image'] = $newImage;
        }

        $validated['published_at'] = $validated['status'] === 'published'
            ? ($validated['published_at'] ?? $blog->published_at ?? now())
            : null;

        try {
            $blog->update($validated);
        } catch (\Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage && $oldImage && $oldImage !== $newImage && ! Str::startsWith($oldImage, ['http://', 'https://', '//'])) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.blog.edit', $blog)
            ->with('success', 'مقاله بروزرسانی شد.');
    }

    public function destroy(BlogPost $blog): RedirectResponse
    {
        $image = $blog->featured_image;
        $blog->delete();

        if ($image && ! Str::startsWith($image, ['http://', 'https://', '//'])) {
            Storage::disk('public')->delete($image);
        }

        return redirect()->route('admin.blog.index')
            ->with('success', 'مقاله حذف شد.');
    }

    private function validatedData(Request $request, ?BlogPost $post = null): array
    {
        $slug = trim((string) $request->input('slug'));
        if ($slug === '') {
            $slug = Str::slug((string) $request->input('title'));
        }
        if ($slug === '') {
            $slug = Str::lower(Str::random(10));
        }

        $request->merge([
            'slug' => $slug,
            'seo_index' => $request->boolean('seo_index'),
            'seo_follow' => $request->boolean('seo_follow'),
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('blog_posts', 'slug')->ignore($post)],
            'category' => ['nullable', 'string', 'max:120'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'seo_index' => ['boolean'],
            'seo_follow' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
