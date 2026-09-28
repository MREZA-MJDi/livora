<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:120',
            ],

            'category' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'status' => [
                'nullable',
                'in:active,draft,archived',
            ],

            'stock' => [
                'nullable',
                'in:in_stock,low_stock,out_of_stock',
            ],

            'feature' => [
                'nullable',
                'in:featured,new,installment',
            ],

            'sort' => [
                'nullable',
                'in:newest,name_asc,price_asc,price_desc,stock_low',
            ],
        ]);

        $query = Product::query()
            ->with([
                'category:id,name',
                'primaryImage',
            ]);

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (! empty($validated['category'])) {
            $query->where(
                'category_id',
                $validated['category']
            );
        }

        if (! empty($validated['status'])) {
            $query->where(
                'status',
                $validated['status']
            );
        }

        match ($validated['stock'] ?? null) {
            'in_stock' => $query->where('stock', '>', 0),
            'low_stock' => $query
                ->where('stock', '>', 0)
                ->where('stock', '<=', 5),
            'out_of_stock' => $query->where('stock', '<=', 0),
            default => null,
        };

        match ($validated['feature'] ?? null) {
            'featured' => $query->where('is_featured', true),
            'new' => $query->where('is_new', true),
            'installment' => $query->where(
                'installment_enabled',
                true
            ),
            default => null,
        };

        match ($validated['sort'] ?? 'newest') {
            'name_asc' => $query
                ->orderBy('name')
                ->orderByDesc('id'),

            'price_asc' => $query
                ->orderBy('price')
                ->orderByDesc('id'),

            'price_desc' => $query
                ->orderByDesc('price')
                ->orderByDesc('id'),

            'stock_low' => $query
                ->orderBy('stock')
                ->orderByDesc('id'),

            default => $query->latest('id'),
        };

        $products = $query
            ->paginate(25)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view(
            'admin.products.index',
            compact('products', 'categories')
        );
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $uploadedImage = $request->file('image');
        unset($data['image']);

        $product = Product::create($data);

        if ($uploadedImage) {
            $path = $uploadedImage->store(
                'products/images',
                'public'
            );

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'alt' => $product->name,
                'sort_order' => 0,
                'is_primary' => true,
            ]);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول با موفقیت ایجاد شد.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        $product->load([
            'category',
            'primaryImage',
            'images.media',
            'allVariants.images.media',
        ]);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load('images');

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view(
            'admin.products.edit',
            compact('product', 'categories')
        );
    }

    /**
     * Update the specified product.
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): RedirectResponse {
        $data = $request->validated();

        $uploadedImage = $request->file('image');
        unset($data['image']);

        $product->update($data);

        if ($uploadedImage) {
            $primaryImage =
                $product->images
                    ->firstWhere('is_primary', true)
                ?? $product->images->first();

            $oldPath = $primaryImage?->path;

            $newPath = $uploadedImage->store(
                'products/images',
                'public'
            );

            if ($primaryImage) {
                $primaryImage->update([
                    'path' => $newPath,
                    'alt' => $product->name,
                    'is_primary' => true,
                ]);

                $product->images()
                    ->where('id', '!=', $primaryImage->id)
                    ->update(['is_primary' => false]);
            } else {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $newPath,
                    'alt' => $product->name,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            }

            if (
                $oldPath
                && ! str_starts_with($oldPath, 'http://')
                && ! str_starts_with($oldPath, 'https://')
                && ! str_starts_with($oldPath, '//')
                && $oldPath !== $newPath
            ) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'محصول با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول با موفقیت حذف شد.');
    }
}
