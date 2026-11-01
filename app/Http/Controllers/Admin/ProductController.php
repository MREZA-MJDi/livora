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
        $products = Product::query()
            ->with([
                'category',
                'images' => fn ($imageQuery) => $imageQuery->limit(1),
            ])
            ->latest()
            ->paginate(15);

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
        $uploadedBanner = $request->file('banner_image');
        unset($data['image'], $data['banner_image']);

        $product = Product::create($data);

        if ($uploadedBanner) {
            $bannerPath = $uploadedBanner->store('products/banners', 'public');

            if (! $bannerPath) {
                throw new \RuntimeException('Product banner could not be stored.');
            }

            $product->update([
                'banner_image' => $bannerPath,
                'banner_image_alt' => $data['banner_image_alt'] ?? $product->name,
            ]);
        }

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
        $uploadedBanner = $request->file('banner_image');
        unset($data['image'], $data['banner_image']);

        $product->update($data);

        if ($uploadedBanner) {
            $oldBannerPath = $product->banner_image;
            $newBannerPath = $uploadedBanner->store('products/banners', 'public');

            if (! $newBannerPath) {
                throw new \RuntimeException('Product banner could not be stored.');
            }

            $product->update([
                'banner_image' => $newBannerPath,
                'banner_image_alt' => $data['banner_image_alt'] ?? $product->name,
            ]);

            if (
                $oldBannerPath
                && ! str_starts_with($oldBannerPath, 'http://')
                && ! str_starts_with($oldBannerPath, 'https://')
                && ! str_starts_with($oldBannerPath, '//')
                && $oldBannerPath !== $newBannerPath
            ) {
                Storage::disk('public')->delete($oldBannerPath);
            }
        }

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
