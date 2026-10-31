<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\SeoSetting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = ContentPage::published()->where('slug', $slug)->firstOrFail();
        $settings = SeoSetting::current();

        return view('pages.show', [
            'page' => $page,
            'robots' => $settings->robotsFor($page->seo_index, $page->seo_follow),
        ]);
    }
}