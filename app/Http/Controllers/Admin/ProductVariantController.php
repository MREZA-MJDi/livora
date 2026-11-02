<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProductVariant::query()
            ->with(['product', 'images'])
            ->orderBy('type')
            ->orderBy('name')
            ->orderBy('value');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $variants = $query->paginate(20)->withQueryString();
        $products = Product::query()->orderBy('name')->get(['id', 'name']);
        $types = ProductVariant::query()->select('type')->distinct()->orderBy('type')->pluck('type');

        return view('admin.product-variants.index', compact('variants', 'products', 'types'));
    }

    public function create(Request $request): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);
        $selectedProductId = $request->filled('product_id') ? $request->integer('product_id') : null;
        $selectedProductImages = $selectedProductId
            ? ProductImage::query()->where('product_id', $selectedProductId)->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        return view('admin.product-variants.create', compact('products', 'selectedProductId', 'selectedProductImages'));
    }

    public function store(StoreProductVariantRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imageIds = $data['image_ids'] ?? [];
        unset($data['image_ids']);

        $productVariant = DB::transaction(function () use ($data, $imageIds): ProductVariant {
            $variant = ProductVariant::create($data);
            $variant->images()->sync($imageIds);

            return $variant;
        });

        return redirect()
            ->route('admin.product-variants.index', ['product_id' => $productVariant->product_id])
            ->with('success', 'تنوع محصول با موفقیت ایجاد شد.');
    }

    public function show(ProductVariant $productVariant): View
    {
        $productVariant->load(['product', 'images']);

        return view('admin.product-variants.show', compact('productVariant'));
    }

    public function edit(ProductVariant $productVariant): View
    {
        $productVariant->load('images');
        $products = Product::query()->orderBy('name')->get(['id', 'name']);
        $selectedProductId = (int) $productVariant->product_id;
        $selectedProductImages = ProductImage::query()->where('product_id', $selectedProductId)->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.product-variants.edit', compact('productVariant', 'products', 'selectedProductId', 'selectedProductImages'));
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant): RedirectResponse
    {
        $data = $request->validated();
        $imageIds = $data['image_ids'] ?? [];
        unset($data['image_ids']);

        DB::transaction(function () use ($data, $imageIds, $productVariant): void {
            $productVariant->update($data);
            $productVariant->images()->sync($imageIds);
        });

        return redirect()
            ->route('admin.product-variants.index', ['product_id' => $productVariant->product_id])
            ->with('success', 'تنوع محصول با موفقیت بروزرسانی شد.');
    }

    public function destroy(ProductVariant $productVariant): RedirectResponse
    {
        $productId = $productVariant->product_id;
        $productVariant->delete();

        return redirect()
            ->route('admin.product-variants.index', ['product_id' => $productId])
            ->with('success', 'تنوع محصول با موفقیت حذف شد.');
    }
}
