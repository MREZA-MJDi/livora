<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Support\HtmlSanitizer;
use App\Support\MediaLibrary;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->with(['author', 'media'])
            ->withCount('tags')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->input('q'));
                $query->where(fn ($sub) => $sub
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.blog.create', [
            'post' => new BlogPost(),
            'bodyHtml' => HtmlSanitizer::sanitize((string) old('body', '')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $media = $request->hasFile('featured_image')
            ? MediaLibrary::store($request->file('featured_image'), (int) $request->user()->id, 'blog', $validated['featured_image_alt'] ?? null)
            : MediaLibrary::image($validated['media_id'] ?? null);
        $imagePath = $media?->path;
        $validated['media_id'] = $media?->id;

        try {
            DB::transaction(function () use ($request, $validated, $imagePath): void {
                $validated['body'] = HtmlSanitizer::sanitize($validated['body']);
                $validated['featured_image'] = $imagePath;
                $validated['user_id'] = $request->user()->id;
                $validated['published_at'] = $validated['status'] === 'published'
                    ? ($validated['published_at'] ?? now())
                    : null;
                $tags = $this->syncTags($validated['tags'] ?? '');
                unset($validated['tags']);
                $post = BlogPost::create($validated);
                $post->tags()->sync($tags);
            });
        } catch (\Throwable $exception) {
            throw $exception;
        }

        return redirect()->route('admin.blog.index')->with('success', 'مقاله ذخیره شد.');
    }

    public function edit(BlogPost $blog): View
    {
        $blog->load(['tags', 'media']);

        return view('admin.blog.edit', [
            'post' => $blog,
            'bodyHtml' => HtmlSanitizer::sanitize((string) old('body', $blog->body)),
            'tagNames' => old('tags', $blog->tags->pluck('name')->implode(', ')),
        ]);
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $validated = $this->validatedData($request, $blog);
        $newMedia = $request->hasFile('featured_image')
            ? MediaLibrary::store($request->file('featured_image'), (int) $request->user()->id, 'blog', $request->input('featured_image_alt'))
            : (filled($request->input('media_id')) ? MediaLibrary::image($request->input('media_id')) : null);
        $newImage = $newMedia?->path;

        $removeFeaturedImage = $request->boolean('remove_featured_image');

        DB::transaction(function () use ($validated, $blog, $newImage, $newMedia, $removeFeaturedImage): void {
                $validated['body'] = HtmlSanitizer::sanitize($validated['body']);
                if ($removeFeaturedImage) {
                    $validated['featured_image'] = null;
                    $validated['media_id'] = null;
                } elseif ($newImage) {
                    $validated['featured_image'] = $newImage;
                    $validated['media_id'] = $newMedia?->id;
                } else {
                    unset($validated['media_id'], $validated['featured_image']);
                }
                $validated['published_at'] = $validated['status'] === 'published'
                    ? ($validated['published_at'] ?? $blog->published_at ?? now())
                    : null;
                $tags = $this->syncTags($validated['tags'] ?? '');
                unset($validated['tags']);
                $blog->update($validated);
                $blog->tags()->sync($tags);
            });
        return redirect()->route('admin.blog.edit', $blog)->with('success', 'مقاله بروزرسانی شد.');
    }

    public function destroy(BlogPost $blog): RedirectResponse
    {
        $blog->delete();

        return redirect()->route('admin.blog.index')->with('success', 'مقاله حذف شد.');
    }

    private function validatedData(Request $request, ?BlogPost $post = null): array
    {
        $slug = trim((string) $request->input('slug'));
        if ($slug === '') {
            $slug = UniqueSlug::make((string) $request->input('title'), 'blog_posts', $post?->id);
        }

        $request->merge([
            'slug' => $slug,
            'seo_index' => $request->boolean('seo_index'),
            'seo_follow' => $request->boolean('seo_follow'),
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_posts', 'slug')->ignore($post)],
            'category' => ['nullable', 'string', 'max:120'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:2000000'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
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

    private function syncTags(string $input): array
    {
        $names = collect(preg_split('/[,،]/u', $input) ?: [])
            ->map(fn ($name) => trim(preg_replace('/\s+/u', ' ', $name) ?? ''))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->take(20);

        $ids = [];
        foreach ($names as $name) {
            $existing = BlogTag::query()->where('name', $name)->first();
            if ($existing) {
                $ids[] = $existing->id;
                continue;
            }

            $slug = UniqueSlug::make($name, 'blog_tags');
            $tag = BlogTag::create(['name' => $name, 'slug' => $slug]);
            $ids[] = $tag->id;
        }

        return $ids;
    }
}