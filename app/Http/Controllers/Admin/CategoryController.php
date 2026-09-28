<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:120',
            ],
            'status' => [
                'nullable',
                'in:active,inactive',
            ],
            'sort' => [
                'nullable',
                'in:position,name_asc,name_desc,newest,oldest',
            ],
        ]);

        $query = Category::query()
            ->withCount('products');

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($categoryQuery) use ($search) {
                $categoryQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (($validated['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        }

        if (($validated['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        match ($validated['sort'] ?? 'position') {
            'name_asc' => $query
                ->orderBy('name')
                ->orderBy('id'),

            'name_desc' => $query
                ->orderByDesc('name')
                ->orderByDesc('id'),

            'newest' => $query->latest('id'),

            'oldest' => $query->oldest('id'),

            default => $query
                ->orderBy('sort_order')
                ->orderBy('name')
                ->orderBy('id'),
        };

        $categories = $query
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('categories', 'public');
        }

        Category::create($data);

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
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): RedirectResponse {
        $data = $request->validated();

        $oldImage = $category->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $request
                ->file('image')
                ->store('categories', 'public');

            $data['image'] = $newImage;
        }

        $category->update($data);

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
