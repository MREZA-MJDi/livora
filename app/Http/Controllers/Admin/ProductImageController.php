<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductImage\StoreProductImageRequest;
use App\Http\Requests\Admin\ProductImage\UpdateProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductImageController extends Controller
{
    /**
     * Display a listing of product images.
     */
    public function index(Request $request): View
    {
        $query = ProductImage::query()
            ->with('product')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->integer('product_id')
            );
        }

        $images = $query
            ->paginate(20)
            ->withQueryString();

        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view(
            'admin.product-images.index',
            compact('images', 'products')
        );
    }

    /**
     * Show the form for creating a new product image.
     */
    public function create(Request $request): View
    {
        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $selectedProductId = $request->filled('product_id')
            ? $request->integer('product_id')
            : null;

        return view(
            'admin.product-images.create',
            compact(
                'products',
                'selectedProductId'
            )
        );
    }

    /**
     * Store a newly created product image.
     */
    public function store(
        StoreProductImageRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $file = $request->file('image');

        unset($data['image']);

        $path = $file->store(
            'products/images',
            'public'
        );

        $data['path'] = $path;

        DB::transaction(function () use ($data) {
            if ($data['is_primary']) {
                ProductImage::query()
                    ->where(
                        'product_id',
                        $data['product_id']
                    )
                    ->update([
                        'is_primary' => false,
                    ]);
            }

            ProductImage::create($data);
        });

        return redirect()
            ->route(
                'admin.product-images.index',
                [
                    'product_id' => $data['product_id'],
                ]
            )
            ->with(
                'success',
                'تصویر محصول با موفقیت اضافه شد.'
            );
    }

    /**
     * Display the specified product image.
     */
    public function show(
        ProductImage $productImage
    ): View {
        $productImage->load('product');

        return view(
            'admin.product-images.show',
            compact('productImage')
        );
    }

    /**
     * Show the form for editing the specified product image.
     */
    public function edit(
        ProductImage $productImage
    ): View {
        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view(
            'admin.product-images.edit',
            compact(
                'productImage',
                'products'
            )
        );
    }

    /**
     * Update the specified product image.
     */
    public function update(
        UpdateProductImageRequest $request,
        ProductImage $productImage
    ): RedirectResponse {
        $data = $request->validated();

        $oldProductId = $productImage->product_id;
        $oldPath = $productImage->path;

        $newPath = null;

        if ($request->hasFile('image')) {
            $newPath = $request
                ->file('image')
                ->store(
                    'products/images',
                    'public'
                );

            $data['path'] = $newPath;
        }

        DB::transaction(function () use (
            $data,
            $productImage
        ) {
            if ($data['is_primary']) {
                ProductImage::query()
                    ->where(
                        'product_id',
                        $data['product_id']
                    )
                    ->whereKeyNot(
                        $productImage->id
                    )
                    ->update([
                        'is_primary' => false,
                    ]);
            }

            $productImage->update($data);
        });

        if (
            $newPath !== null &&
            $oldPath &&
            $oldPath !== $newPath
        ) {
            Storage::disk('public')->delete(
                $oldPath
            );
        }

        return redirect()
            ->route(
                'admin.product-images.index',
                [
                    'product_id' =>
                        $productImage->product_id,
                ]
            )
            ->with(
                'success',
                'تصویر محصول با موفقیت بروزرسانی شد.'
            );
    }

    /**
     * Remove the specified product image.
     */
    public function destroy(
        ProductImage $productImage
    ): RedirectResponse {
        $productId = $productImage->product_id;
        $path = $productImage->path;

        DB::transaction(function () use (
            $productImage
        ) {
            $productImage->delete();
        });

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        return redirect()
            ->route(
                'admin.product-images.index',
                [
                    'product_id' => $productId,
                ]
            )
            ->with(
                'success',
                'تصویر محصول با موفقیت حذف شد.'
            );
    }
}
