<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoSetting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $settings = SeoSetting::current();
        $urls = [];

        if ($settings->indexing_enabled) {
            $base = rtrim(config('app.url'), '/');

            foreach (['/', '/shop', '/categories', '/blog', '/about', '/contact'] as $path) {
                $urls[] = ['loc' => $base . $path, 'lastmod' => now()->toAtomString()];
            }

            Product::query()
                ->where('status', 'active')
                ->where('seo_index', true)
                ->select(['id', 'slug', 'updated_at'])
                ->chunkById(500, function ($products) use (&$urls, $base): void {
                    foreach ($products as $product) {
                        $urls[] = [
                            'loc' => $base . '/product/' . $product->slug,
                            'lastmod' => $product->updated_at?->toAtomString(),
                        ];
                    }
                });

            Category::query()
                ->where('is_active', true)
                ->where('seo_index', true)
                ->select(['id', 'slug', 'updated_at'])
                ->chunkById(500, function ($categories) use (&$urls, $base): void {
                    foreach ($categories as $category) {
                        $urls[] = [
                            'loc' => $base . '/categories/' . $category->slug,
                            'lastmod' => $category->updated_at?->toAtomString(),
                        ];
                    }
                });

            BlogPost::published()
                ->where('seo_index', true)
                ->select(['id', 'slug', 'updated_at'])
                ->chunkById(500, function ($posts) use (&$urls, $base): void {
                    foreach ($posts as $post) {
                        $urls[] = [
                            'loc' => $base . '/blog/' . $post->slug,
                            'lastmod' => $post->updated_at?->toAtomString(),
                        ];
                    }
                });
        }

        return response()
            ->view('seo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=300');
    }
}
