<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
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
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:120',
            ],
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],
            'type' => [
                'nullable',
                'string',
                'max:100',
            ],
            'is_active' => [
                'nullable',
                'in:0,1',
            ],
            'stock' => [
                'nullable',
                'in:in_stock,low_stock,out_of_stock',
            ],
            'sort' => [
                'nullable',
                'in:newest,oldest,type,name,stock_low,stock_high,price_low,price_high',
            ],
        ]);

        $query = ProductVariant::query()
            ->with('product:id,name');

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($variantQuery) use ($search) {
                $variantQuery
                    ->where('type', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (! empty($validated['product_id'])) {
            $query->where('product_id', $validated['product_id']);
        }

        if (! empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (array_key_exists('is_active', $validated) && $validated['is_active'] !== null) {
            $query->where(
                'is_active',
                (bool) (int) $validated['is_active']
            );
        }

        match ($validated['stock'] ?? null) {
            'in_stock' => $query->where('stock', '>', 0),
            'low_stock' => $query
                ->where('stock', '>', 0)
                ->where('stock', '<=', 3),
            'out_of_stock' => $query->where('stock', '<=', 0),
            default => null,
        };

        match ($validated['sort'] ?? 'type') {
            'newest' => $query->latest('id'),
            'oldest' => $query->oldest('id'),
            'name' => $query->orderBy('name')->orderBy('value')->orderBy('id'),
            'stock_low' => $query->orderBy('stock')->orderBy('id'),
            'stock_high' => $query->orderByDesc('stock')->orderBy('id'),
            'price_low' => $query->orderBy('price_adjustment')->orderBy('id'),
            'price_high' => $query->orderByDesc('price_adjustment')->orderBy('id'),
            default => $query->orderBy('type')->orderBy('name')->orderBy('value')->orderBy('id'),
        };

        $variants = $query
            ->paginate(20)
            ->withQueryString();

        $selectedProduct = ! empty($validated['product_id'])
            ? Product::query()
                ->select(['id', 'name', 'sku'])
                ->find($validated['product_id'])
            : null;

        $types = ProductVariant::query()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view(
            'admin.product-variants.index',
            compact(
                'variants',
                'selectedProduct',
                'types'
            )
        );
    }

    /**
     * Show the form for creating a new product variant.
     */
    /**
     * Return the current product image set for the variant image picker.
     */
    public function productImages(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
        ]);

        $images = ProductImage::query()
            ->with('media')
            ->where('product_id', $validated['product_id'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(100)
            ->get();

        return response()->json([
            'data' => $images->map(fn (ProductImage $image) => [
                'id' => $image->id,
                'url' => $image->url,
                'alt' => $image->alt,
                'sort_order' => $image->sort_order,
            ])->values(),
            'has_more' => $images->count() === 100,
        ]);
    }

    public function create(Request $request): View
    {
        $selectedProductId = $request->filled('product_id')
            ? $request->integer('product_id')
            : null;

        $selectedProduct = $selectedProductId
            ? Product::query()
                ->select(['id', 'name', 'sku'])
                ->find($selectedProductId)
            : null;

        $selectedProductImages = $selectedProductId
            ? ProductImage::query()
                ->with('media')
                ->where('product_id', $selectedProductId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(100)
                ->get()
            : collect();

        return view(
            'admin.product-variants.create',
            compact(
                'selectedProduct',
                'selectedProductImages'
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

        $imageIds = $data['image_ids'] ?? [];
        unset($data['image_ids']);

        DB::transaction(function () use ($data, $imageIds) {
            $productVariant = ProductVariant::create($data);
            $productVariant->images()->sync($imageIds);
        });

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
        $productVariant->load([
            'product:id,name,sku',
            'images.media',
        ]);

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
        $productVariant->load([
            'images.media',
            'product:id,name,sku',
        ]);

        $selectedProduct = $productVariant->product;
        $selectedProductId = (int) $productVariant->product_id;

        $selectedProductImages = ProductImage::query()
            ->with('media')
            ->where('product_id', $selectedProductId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(100)
            ->get();

        return view(
            'admin.product-variants.edit',
            compact(
                'productVariant',
                'selectedProduct',
                'selectedProductImages'
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

        $imageIds = $data['image_ids'] ?? [];
        unset($data['image_ids']);

        DB::transaction(function () use ($data, $imageIds, $productVariant) {
            $productVariant->update($data);
            $productVariant->images()->sync($imageIds);
        });

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
