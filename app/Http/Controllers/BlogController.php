<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\SeoSetting;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::published()
            ->with('tags')
            ->latest('published_at')
            ->paginate(12);

        $tags = BlogTag::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->orderBy('name')
            ->limit(30)
            ->get();

        return view('blog.index', compact('posts', 'tags'));
    }

    public function tag(BlogTag $tag): View
    {
        $posts = $tag->posts()
            ->published()
            ->with('tags')
            ->latest('published_at')
            ->paginate(12);

        $tags = BlogTag::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->orderBy('name')
            ->limit(30)
            ->get();

        return view('blog.index', [
            'posts' => $posts,
            'tags' => $tags,
            'activeTag' => $tag,
        ]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::published()->with(['tags', 'author'])->where('slug', $slug)->firstOrFail();
        $robots = SeoSetting::current()->robotsFor($post->seo_index, $post->seo_follow);

        return view('blog.show', compact('post', 'robots'));
    }
}