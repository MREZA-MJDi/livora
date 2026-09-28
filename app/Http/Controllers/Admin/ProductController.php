<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(): View
    {
        $query = Product::query()
            ->with('category');

        if (request()->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if (request()->boolean('new')) {
            $query->where('is_new', true);
        }

        if (request()->boolean('installment')) {
            $query->where('installment_enabled', true);
        }

        if (request()->get('stock') === 'low') {
            $query->whereBetween('stock', [1, 5]);
        }

        if (request()->get('stock') === 'out') {
            $query->where('stock', 0);
        }

        $products = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
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
            'images',
            'allVariants.images',
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
