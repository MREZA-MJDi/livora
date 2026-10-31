<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\SeoSetting;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::published()
            ->latest('published_at')
            ->paginate(12);

        return view('blog.index', compact('posts'));
    }

    public function show(string $slug): View
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();
        $robots = SeoSetting::current()->robotsFor($post->seo_index, $post->seo_follow);

        return view('blog.show', compact('post', 'robots'));
    }
}
