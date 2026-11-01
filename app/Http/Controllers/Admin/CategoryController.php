<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\Seo\ContentSeoAnalyzer;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
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

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $relatedCategoryIds = $data['related_category_ids'] ?? [];
        $relatedProductIds = $data['related_product_ids'] ?? [];
        unset($data['related_category_ids'], $data['related_product_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('categories', 'public');
        }

        DB::transaction(function () use ($data, $relatedCategoryIds, $relatedProductIds): void {
            $category = Category::create($data);
            $category->relatedCategories()->sync(array_values(array_diff($relatedCategoryIds, [$category->id])));
            $category->recommendedProducts()->sync($relatedProductIds);
        });
        Cache::forget('layout.search_categories');

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): View
    {
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
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

    /**
     * Update the specified category.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): RedirectResponse {
        $data = $request->validated();
        $relatedCategoryIds = array_values(array_diff($data['related_category_ids'] ?? [], [$category->id]));
        $relatedProductIds = $data['related_product_ids'] ?? [];
        unset($data['related_category_ids'], $data['related_product_ids']);

        $oldImage = $category->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $request
                ->file('image')
                ->store('categories', 'public');

            $data['image'] = $newImage;
        }

        DB::transaction(function () use ($category, $data, $relatedCategoryIds, $relatedProductIds): void {
            $category->update($data);
            $category->relatedCategories()->sync($relatedCategoryIds);
            $category->recommendedProducts()->sync($relatedProductIds);
        });
        Cache::forget('layout.search_categories');

        if (
            $newImage
            && $oldImage
            && ! str_starts_with($oldImage, 'http://')
            && ! str_starts_with($oldImage, 'https://')
            && ! str_starts_with($oldImage, '//')
        ) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with(
                    'error',
                    'این دسته‌بندی هنوز محصول دارد و تا حذف یا انتقال محصولات قابل حذف نیست.'
                );
        }

        $image = $category->image;

        $category->delete();
        Cache::forget('layout.search_categories');

        if (
            $image
            && ! str_starts_with($image, 'http://')
            && ! str_starts_with($image, 'https://')
            && ! str_starts_with($image, '//')
        ) {
            Storage::disk('public')->delete($image);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }
}
