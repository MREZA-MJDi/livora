<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SeoPageSetting;
use App\Models\SeoSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class SeoAuditController extends Controller
{
    public function index(): View
    {
        $settings = SeoSetting::current();
        $globalGateOpen = $settings->indexing_enabled && $settings->following_enabled;

        $sections = [
            [
                'key' => 'products',
                'label' => 'محصولات فعال',
                'url' => route('admin.products.index'),
                'metrics' => $this->metrics(Product::query()->where('status', 'active')),
            ],
            [
                'key' => 'categories',
                'label' => 'دسته‌بندی‌های فعال',
                'url' => route('admin.categories.index'),
                'metrics' => $this->metrics(Category::query()->where('is_active', true)),
            ],
            [
                'key' => 'blog',
                'label' => 'مقاله‌های منتشرشده',
                'url' => route('admin.blog.index'),
                'metrics' => $this->metrics(BlogPost::published()),
            ],
            [
                'key' => 'content',
                'label' => 'صفحات محتوایی منتشرشده',
                'url' => route('admin.pages.index'),
                'metrics' => $this->metrics(ContentPage::published()),
            ],
            [
                'key' => 'fixed',
                'label' => 'صفحات ثابت سایت',
                'url' => route('admin.seo.pages'),
                'metrics' => $this->metrics(SeoPageSetting::query()),
            ],
        ];

        $totals = [
            'records' => array_sum(array_column(array_column($sections, 'metrics'), 'total')),
            'eligible' => array_sum(array_column(array_column($sections, 'metrics'), 'eligible')),
            'missing_titles' => array_sum(array_column(array_column($sections, 'metrics'), 'missing_titles')),
            'missing_descriptions' => array_sum(array_column(array_column($sections, 'metrics'), 'missing_descriptions')),
        ];

        $missingImageAlt = ProductImage::query()
            ->whereHas('product', fn (Builder $query) => $query->where('status', 'active'))
            ->where(fn ($query) => $query->whereNull('alt')->orWhereRaw("TRIM(alt) = ''"))
            ->count()
            + BlogPost::published()
                ->whereNotNull('featured_image')
                ->where(fn ($query) => $query->whereNull('featured_image_alt')->orWhereRaw("TRIM(featured_image_alt) = ''"))
                ->count()
            + ContentPage::published()
                ->whereNotNull('featured_image')
                ->where(fn ($query) => $query->whereNull('featured_image_alt')->orWhereRaw("TRIM(featured_image_alt) = ''"))
                ->count();

        return view('admin.seo.audit', [
            'settings' => $settings,
            'globalGateOpen' => $globalGateOpen,
            'sections' => $sections,
            'totals' => $totals,
            'missingImageAlt' => $missingImageAlt,
            'googleVerificationConfigured' => filled($settings->google_site_verification),
            'bingVerificationConfigured' => filled($settings->bing_site_verification),
        ]);
    }

    private function metrics(Builder $query): array
    {
        $missingTitles = (clone $query)
            ->where(fn ($where) => $where->whereNull('meta_title')->orWhereRaw("TRIM(meta_title) = ''"))
            ->count();

        $missingDescriptions = (clone $query)
            ->where(fn ($where) => $where->whereNull('meta_description')->orWhereRaw("TRIM(meta_description) = ''"))
            ->count();

        return [
            'total' => (clone $query)->count(),
            'eligible' => (clone $query)->where('seo_index', true)->where('seo_follow', true)->count(),
            'missing_titles' => $missingTitles,
            'missing_descriptions' => $missingDescriptions,
        ];
    }
}
