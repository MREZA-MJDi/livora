<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\HomeSection;
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
            ->get();

        $finalCta = $sections->firstWhere('key', 'final-cta');
        $sections = $sections
            ->reject(fn (HomeSection $section) => $section->key === 'final-cta')
            ->values();

        $usedProductIds = [];

        foreach ($sections as $section) {
            $payload = $section->payload ?? [];
            $limit = match ($section->type) {
                'hero' => 6,
                'categories' => 4,
                default => max(1, min(24, (int) ($payload['limit'] ?? 8))),
            };

            if (in_array($section->type, ['hero', 'products'], true)) {
                $products = $productResolver->resolve($section, $usedProductIds);
                $section->setRelation('products', $products);
                $usedProductIds = array_merge($usedProductIds, $products->modelKeys());
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

        $blogPosts = BlogPost::query()
            ->published()
            ->with('media')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('home.index', [
            'homeSections' => $sections,
            'blogPosts' => $blogPosts,
            'finalCta' => $finalCta,
        ]);
    }
}
