<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Support\MediaLibrary;
use App\Models\Product;
use App\Services\Seo\ContentSeoAnalyzer;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);

        $categories = Category::query()
            ->with(['media'])
            ->withCount('products')
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters): void {
                $term = trim($filters['search']);
                $query->where(function ($search) use ($term): void {
                    $search->where('name', 'like', '%'.$term.'%')
                        ->orWhere('slug', 'like', '%'.$term.'%');
                });
            })
            ->when(($filters['status'] ?? 'all') === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? 'all') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'filters'));
    }

    public function create(ContentSeoAnalyzer $seoAnalyzer): View
    {
        $category = new Category();
        $relatedCategoryIds = $this->boundedIds(old('related_category_ids', []));
        $relatedProductIds = $this->boundedIds(old('related_product_ids', []));
        $parentId = (int) old('parent_id', 0);
        $selectedParentCategory = $parentId > 0 ? Category::query()->find($parentId) : null;

        return view('admin.categories.create', [
            'category' => $category,
            'seoAnalysis' => $seoAnalyzer->analyze($category),
            'selectedParentCategory' => $selectedParentCategory,
            'selectedRelatedCategoryOptions' => $this->categoryOptions($relatedCategoryIds),
            'selectedRelatedProductOptions' => $this->productOptions($relatedProductIds),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $relatedCategoryIds = $data['related_category_ids'] ?? [];
        $relatedProductIds = $data['related_product_ids'] ?? [];
        unset($data['related_category_ids'], $data['related_product_ids']);

        $media = $request->hasFile('image')
            ? MediaLibrary::store($request->file('image'), (int) $request->user()->id, 'categories', $data['image_alt'] ?? null)
            : MediaLibrary::image($data['media_id'] ?? null);
        if ($media) {
            $data['media_id'] = $media->id;
            $data['image'] = $media->path;
        }
        unset($data['media_id']);

        $cardMedia = $request->hasFile('card_image')
            ? MediaLibrary::store($request->file('card_image'), (int) $request->user()->id, 'categories/cards', $data['image_alt'] ?? null)
            : MediaLibrary::image($data['card_media_id'] ?? null);
        $bannerMedia = $request->hasFile('banner_image')
            ? MediaLibrary::store($request->file('banner_image'), (int) $request->user()->id, 'categories/banners', $data['image_alt'] ?? null)
            : MediaLibrary::image($data['banner_media_id'] ?? null);
        unset($data['card_media_id'], $data['banner_media_id'], $data['card_image'], $data['banner_image'], $data['remove_card_image'], $data['remove_banner_image']);
        $data['card_image'] = $cardMedia?->path;
        $data['card_media_id'] = $cardMedia?->id;
        $data['banner_image'] = $bannerMedia?->path;
        $data['banner_media_id'] = $bannerMedia?->id;

        DB::transaction(function () use ($data, $relatedCategoryIds, $relatedProductIds, $media): void {
            $category = Category::create($data + ['media_id' => $media?->id]);
            $category->relatedCategories()->sync(array_values(array_diff($relatedCategoryIds, [$category->id])));
            $category->recommendedProducts()->sync($relatedProductIds);
        });

        Cache::forget('layout.search_categories');

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function show(Category $category): View
    {
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category, ContentSeoAnalyzer $seoAnalyzer): View
    {
        $relatedCategoryIds = $this->boundedIds(old('related_category_ids', $category->relatedCategories()->limit(30)->pluck('categories.id')->all()));
        $relatedProductIds = $this->boundedIds(old('related_product_ids', $category->recommendedProducts()->limit(30)->pluck('products.id')->all()));
        $parentId = (int) old('parent_id', $category->parent_id);
        $selectedParentCategory = $parentId > 0 ? Category::query()->find($parentId) : null;

        return view('admin.categories.edit', [
            'category' => $category,
            'seoAnalysis' => $seoAnalyzer->analyze($category),
            'selectedParentCategory' => $selectedParentCategory,
            'selectedRelatedCategoryOptions' => $this->categoryOptions($relatedCategoryIds),
            'selectedRelatedProductOptions' => $this->productOptions($relatedProductIds),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $relatedCategoryIds = array_values(array_diff($data['related_category_ids'] ?? [], [$category->id]));
        $relatedProductIds = $data['related_product_ids'] ?? [];
        unset($data['related_category_ids'], $data['related_product_ids']);

        $media = $request->hasFile('image')
            ? MediaLibrary::store($request->file('image'), (int) $request->user()->id, 'categories', $data['image_alt'] ?? null)
            : (array_key_exists('media_id', $data) ? MediaLibrary::image($data['media_id']) : null);
        if ($request->boolean('remove_image')) {
            $data['image'] = null;
            $data['media_id'] = null;
        } elseif ($request->hasFile('image') || ! empty($data['media_id'])) {
            $data['media_id'] = $media?->id;
            $data['image'] = $media?->path;
        } else {
            unset($data['media_id']);
        }

        foreach (['card', 'banner'] as $kind) {
            $fileKey = $kind.'_image';
            $mediaKey = $kind.'_media_id';
            $removeKey = 'remove_'.$kind.'_image';
            if ($request->boolean($removeKey)) {
                $data[$fileKey] = null;
                $data[$mediaKey] = null;
            } elseif ($request->hasFile($fileKey) || ! empty($data[$mediaKey])) {
                $selectedMedia = $request->hasFile($fileKey)
                    ? MediaLibrary::store($request->file($fileKey), (int) $request->user()->id, 'categories/'.$kind.'s', $data['image_alt'] ?? null)
                    : MediaLibrary::image($data[$mediaKey]);
                $data[$fileKey] = $selectedMedia?->path;
                $data[$mediaKey] = $selectedMedia?->id;
            } else {
                unset($data[$fileKey], $data[$mediaKey]);
            }
        }
        unset($data['remove_card_image'], $data['remove_banner_image']);

        DB::transaction(function () use ($category, $data, $relatedCategoryIds, $relatedProductIds): void {
            $category->update($data);
            $category->relatedCategories()->sync($relatedCategoryIds);
            $category->recommendedProducts()->sync($relatedProductIds);
        });

        Cache::forget('layout.search_categories');

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت بروزرسانی شد.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Product uses SoftDeletes; a trashed product still owns the category FK.
        // Check trashed rows too, otherwise the database RESTRICT constraint
        // turns this action into a production 500.
        if ($category->products()->withTrashed()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'این دسته‌بندی هنوز محصول دارد و تا حذف یا انتقال محصولات قابل حذف نیست.');
        }

        $category->delete();
        Cache::forget('layout.search_categories');

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://') && ! str_starts_with($path, '//')) {
            Storage::disk('public')->delete($path);
        }
    }
    private function boundedIds(mixed $ids): array
    {
        return collect(is_array($ids) ? $ids : [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->take(30)
            ->values()
            ->all();
    }

    private function categoryOptions(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return Category::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'is_active'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'label' => $category->name,
                'meta' => $category->is_active ? null : 'غیرفعال',
            ])
            ->values()
            ->all();
    }

    private function productOptions(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return Product::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'sku'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'label' => $product->name,
                'meta' => $product->sku,
            ])
            ->values()
            ->all();
    }


}
