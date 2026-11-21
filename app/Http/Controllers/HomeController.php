<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use App\Services\Homepage\HomeSectionProductResolver;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(HomeSectionProductResolver $productResolver): View
    {
        $sections = HomeSection::query()
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(40)
            ->get();

        $finalCta = $sections->firstWhere('key', 'final-cta');
        $blogSection = HomeSection::query()->where('key', 'homepage-journal')->first();
        $sections = $sections
            ->reject(fn (HomeSection $section) => in_array($section->key, ['final-cta', 'homepage-journal'], true))
            ->values();

        $hasProductSection = false;

        foreach ($sections as $section) {
            $payload = $section->payload ?? [];
            $limit = match ($section->type) {
                'hero' => 6,
                'categories' => 4,
                default => max(1, min(24, (int) ($payload['limit'] ?? 8))),
            };

            if (in_array($section->type, ['hero', 'products'], true)) {
                $products = $productResolver->resolve($section);
                $section->setRelation('products', $products);

                if ($section->type === 'products' && $products->isNotEmpty()) {
                    $hasProductSection = true;
                }
            }

            if ($section->type === 'categories') {
                $categories = Category::query()
                    ->active()
                    ->withCount(['products' => fn ($productQuery) => $productQuery->active()])
                    ->with([
                        'media',
                        'cardMedia',
                        'latestActiveProduct.images.media',
                        'latestActiveProduct.variants.images.media',
                    ])
                    ->reorder()
                    ->orderByDesc('products_count')
                    ->orderBy('name')
                    ->limit($limit)
                    ->get();

                $section->setRelation('categories', $categories);
            }
        }

        if (! $hasProductSection) {
            $latestSection = new HomeSection([
                'key' => 'latest-products-fallback',
                'type' => 'products',
                'enabled' => true,
                'sort_order' => 60,
                'eyebrow' => 'تازه‌های فروشگاه',
                'title' => 'جدیدترین محصولات',
                'subtitle' => 'آخرین محصولات فعال ثبت‌شده در کاتالوگ فروشگاه.',
                'cta_label' => 'مشاهده همه محصولات',
                'cta_url' => '/shop',
                'payload' => ['product_source' => 'latest', 'limit' => 8],
            ]);

            $latestProducts = $productResolver->resolve($latestSection);
            if ($latestProducts->isNotEmpty()) {
                $latestSection->setRelation('products', $latestProducts);
                $sections->push($latestSection);
            }
        }

        $heroProducts = Product::query()
            ->active()
            ->where('is_home_hero', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->where(function ($query) {
                $query->whereHas('images')->orWhereHas('variants.images');
            })
            ->with(['images.media', 'variants.images.media'])
            ->orderBy('home_hero_sort_order')
            ->orderBy('id')
            ->limit(10)
            ->get();

        $blogPosts = BlogPost::query()
            ->published()
            ->with('media')
            ->latest('published_at')
            ->limit(max(1, min(6, (int) data_get($blogSection?->payload, 'limit', 3))))
            ->get();

        if ($blogSection && ! $blogSection->enabled) {
            $blogSection = null;
        } elseif (! $blogSection) {
            $blogSection = new HomeSection([
                'key' => 'homepage-journal',
                'type' => 'editorial',
                'enabled' => true,
                'eyebrow' => 'مجله سیلا',
                'title' => 'ایده‌هایی برای خانه‌ای دلنشین‌تر',
                'subtitle' => 'راهنماها و نوشته‌های تازه برای انتخاب آگاهانه‌تر.',
                'cta_label' => 'همه نوشته‌ها',
                'cta_url' => '/blog',
                'payload' => ['limit' => 3],
            ]);
        }

        return view('home.index', [
            'homeSections' => $sections,
            'heroProducts' => $heroProducts,
            'blogPosts' => $blogPosts,
            'blogSection' => $blogSection,
            'finalCta' => $finalCta,
        ]);
    }
}
