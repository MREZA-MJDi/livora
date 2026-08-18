<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of product variants.
     */
    public function index(Request $request): View
    {
        $query = ProductVariant::query()
            ->with('product')
            ->orderBy('type')
            ->orderBy('name')
            ->orderBy('value');

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->integer('product_id')
            );
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('is_active')) {
            $query->where(
                'is_active',
                $request->boolean('is_active')
            );
        }

        $variants = $query
            ->paginate(20)
            ->withQueryString();

        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $types = ProductVariant::query()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view(
            'admin.product-variants.index',
            compact(
                'variants',
                'products',
                'types'
            )
        );
    }

    /**
     * Show the form for creating a new product variant.
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
            'admin.product-variants.create',
            compact(
                'products',
                'selectedProductId'
            )
        );
    }

    /**
     * Store a newly created product variant.
     */
    public function store(
        StoreProductVariantRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        ProductVariant::create($data);

        return redirect()
            ->route(
                'admin.product-variants.index',
                [
                    'product_id' => $data['product_id'],
                ]
            )
            ->with(
                'success',
                'تنوع محصول با موفقیت ایجاد شد.'
            );
    }

    /**
     * Display the specified product variant.
     */
    public function show(
        ProductVariant $productVariant
    ): View {
        $productVariant->load('product');

        return view(
            'admin.product-variants.show',
            compact('productVariant')
        );
    }

    /**
     * Show the form for editing the specified product variant.
     */
    public function edit(
        ProductVariant $productVariant
    ): View {
        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view(
            'admin.product-variants.edit',
            compact(
                'productVariant',
                'products'
            )
        );
    }

    /**
     * Update the specified product variant.
     */
    public function update(
        UpdateProductVariantRequest $request,
        ProductVariant $productVariant
    ): RedirectResponse {
        $data = $request->validated();

        $productVariant->update($data);

        return redirect()
            ->route(
                'admin.product-variants.index',
                [
                    'product_id' =>
                        $productVariant->product_id,
                ]
            )
            ->with(
                'success',
                'تنوع محصول با موفقیت بروزرسانی شد.'
            );
    }

    /**
     * Remove the specified product variant.
     */
    public function destroy(
        ProductVariant $productVariant
    ): RedirectResponse {
        $productId = $productVariant->product_id;

        $productVariant->delete();

        return redirect()
            ->route(
                'admin.product-variants.index',
                [
                    'product_id' => $productId,
                ]
            )
            ->with(
                'success',
                'تنوع محصول با موفقیت حذف شد.'
            );
    }
}
