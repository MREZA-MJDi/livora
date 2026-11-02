<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductImage\StoreProductImageRequest;
use App\Http\Requests\Admin\ProductImage\UpdateProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProductImageController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProductImage::query()
            ->with('product')
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        $images = $query->paginate(20)->withQueryString();
        $products = Product::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.product-images.index', compact('images', 'products'));
    }

    public function create(Request $request): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);
        $selectedProductId = $request->filled('product_id') ? $request->integer('product_id') : null;

        return view('admin.product-images.create', compact('products', 'selectedProductId'));
    }

    public function store(StoreProductImageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $file = $request->file('image');
        unset($data['image']);

        $path = $file->store('products/images', 'public');
        if (! $path) {
            throw new RuntimeException('Product image could not be stored.');
        }
        $data['path'] = $path;

        try {
            DB::transaction(function () use (&$data): void {
                $hasExistingImage = ProductImage::query()
                    ->where('product_id', $data['product_id'])
                    ->exists();

                $data['is_primary'] = $data['is_primary'] || ! $hasExistingImage;

                if ($data['is_primary']) {
                    ProductImage::query()
                        ->where('product_id', $data['product_id'])
                        ->update(['is_primary' => false]);
                }

                ProductImage::create($data);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        return redirect()
            ->route('admin.product-images.index', ['product_id' => $data['product_id']])
            ->with('success', 'تصویر محصول با موفقیت اضافه شد.');
    }

    public function show(ProductImage $productImage): View
    {
        $productImage->load('product');

        return view('admin.product-images.show', compact('productImage'));
    }

    public function edit(ProductImage $productImage): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.product-images.edit', compact('productImage', 'products'));
    }

    public function update(UpdateProductImageRequest $request, ProductImage $productImage): RedirectResponse
    {
        $data = $request->validated();
        $oldProductId = (int) $productImage->product_id;
        $oldPath = $productImage->path;
        $newPath = null;

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('products/images', 'public');
            if (! $newPath) {
                throw new RuntimeException('Product image could not be stored.');
            }
            $data['path'] = $newPath;
        }

        try {
            DB::transaction(function () use ($data, $productImage, $oldProductId): void {
                $newProductId = (int) $data['product_id'];
                $isMovingProduct = $oldProductId !== $newProductId;

                if ($data['is_primary']) {
                    ProductImage::query()
                        ->where('product_id', $newProductId)
                        ->where('id', '!=', $productImage->id)
                        ->update(['is_primary' => false]);
                }

                $productImage->update($data);

                if ($isMovingProduct) {
                    // A moved image must not remain attached to variants of its old product.
                    $productImage->variants()->detach();
                }

                // Keep one primary image for every affected product whenever it has images.
                foreach (array_unique([$oldProductId, $newProductId]) as $affectedProductId) {
                    $hasPrimary = ProductImage::query()
                        ->where('product_id', $affectedProductId)
                        ->where('is_primary', true)
                        ->exists();

                    if (! $hasPrimary) {
                        $replacement = ProductImage::query()
                            ->where('product_id', $affectedProductId)
                            ->orderBy('sort_order')
                            ->orderBy('id')
                            ->first();

                        $replacement?->update(['is_primary' => true]);
                    }
                }
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath && ! Str::startsWith($oldPath, ['http://', 'https://', '//'])) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('admin.product-images.index', ['product_id' => $productImage->product_id])
            ->with('success', 'تصویر محصول با موفقیت بروزرسانی شد.');
    }

    public function destroy(ProductImage $productImage): RedirectResponse
    {
        $productId = $productImage->product_id;
        $path = $productImage->path;
        $wasPrimary = (bool) $productImage->is_primary;

        DB::transaction(function () use ($productImage, $productId, $wasPrimary): void {
            $productImage->delete();

            if ($wasPrimary) {
                $replacement = ProductImage::query()
                    ->where('product_id', $productId)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                $replacement?->update(['is_primary' => true]);
            }
        });

        if ($path && ! Str::startsWith($path, ['http://', 'https://', '//'])) {
            Storage::disk('public')->delete($path);
        }

        return redirect()
            ->route('admin.product-images.index', ['product_id' => $productId])
            ->with('success', 'تصویر محصول با موفقیت حذف شد.');
    }
}
