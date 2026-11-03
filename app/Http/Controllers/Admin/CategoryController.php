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
    public function index(): View
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(ContentSeoAnalyzer $seoAnalyzer): View
    {
        $category = new Category();

        return view('admin.categories.create', [
            'category' => $category,
            'seoAnalysis' => $seoAnalyzer->analyze($category),
            'availableRelatedCategories' => Category::query()->active()->orderBy('name')->limit(100)->get(['id', 'name']),
            'availableRelatedProducts' => Product::query()->active()->orderBy('name')->limit(200)->get(['id', 'name']),
            'selectedRelatedCategoryIds' => [],
            'selectedRelatedProductIds' => [],
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
        $category->load(['relatedCategories:id,name', 'recommendedProducts:id,name']);

        return view('admin.categories.edit', [
            'category' => $category,
            'seoAnalysis' => $seoAnalyzer->analyze($category),
            'availableRelatedCategories' => Category::query()->active()->where('id', '!=', $category->id)->orderBy('name')->limit(100)->get(['id', 'name']),
            'availableRelatedProducts' => Product::query()->active()->orderBy('name')->limit(200)->get(['id', 'name']),
            'selectedRelatedCategoryIds' => $category->relatedCategories->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'selectedRelatedProductIds' => $category->recommendedProducts->pluck('id')->map(fn ($id) => (int) $id)->all(),
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
}
