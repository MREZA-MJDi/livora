<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Product;
use App\Models\SeoPageSetting;
use App\Models\SeoSetting;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SitemapController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $settings = SeoSetting::current();

        return response()->stream(function () use ($settings): void {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            if (! $settings->indexing_enabled) {
                echo '</urlset>';
                return;
            }

            $base = rtrim(config('app.url'), '/');

            SeoPageSetting::query()
                ->where('seo_index', true)
                ->orderBy('id')
                ->cursor()
                ->each(function (SeoPageSetting $page) use ($base): void {
                    $loc = $page->canonical_url ?: $base . $page->path;
                    $this->writeUrl($loc, $page->updated_at);
                });

            ContentPage::published()
                ->where('seo_index', true)
                ->select(['id', 'slug', 'updated_at', 'canonical_url'])
                ->chunkById(300, function ($pages) use ($base): void {
                    foreach ($pages as $page) {
                        $this->writeUrl($page->canonical_url ?: $base . '/' . $page->slug, $page->updated_at);
                    }
                });

            Product::query()
                ->where('status', 'active')
                ->where('seo_index', true)
                ->select(['id', 'slug', 'updated_at', 'canonical_url'])
                ->chunkById(300, function ($products) use ($base): void {
                    foreach ($products as $product) {
                        $this->writeUrl($product->canonical_url ?: $base . '/product/' . $product->slug, $product->updated_at);
                    }
                });

            Category::query()
                ->where('is_active', true)
                ->where('seo_index', true)
                ->select(['id', 'slug', 'updated_at', 'canonical_url'])
                ->chunkById(300, function ($categories) use ($base): void {
                    foreach ($categories as $category) {
                        $this->writeUrl($category->canonical_url ?: $base . '/categories/' . $category->slug, $category->updated_at);
                    }
                });

            BlogPost::published()
                ->where('seo_index', true)
                ->select(['id', 'slug', 'updated_at', 'canonical_url'])
                ->chunkById(300, function ($posts) use ($base): void {
                    foreach ($posts as $post) {
                        $this->writeUrl($post->canonical_url ?: $base . '/blog/' . $post->slug, $post->updated_at);
                    }
                });

            echo '</urlset>';
        }, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300, s-maxage=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function writeUrl(string $url, mixed $updatedAt): void
    {
        // Canonical URLs outside the configured SillaGallery origin are
        // intentionally excluded from the SillaGallery sitemap.
        $originHost = parse_url(config('app.url'), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);
        if (! $originHost || ! $urlHost || strcasecmp($originHost, $urlHost) !== 0) {
            return;
        }

        echo '<url><loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
        if ($updatedAt) {
            echo '<lastmod>' . e($updatedAt->toAtomString()) . '</lastmod>';
        }
        echo '</url>';
    }
}