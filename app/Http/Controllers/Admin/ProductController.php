<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Throwable;
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
     * Return a small searchable product dataset for admin selectors.
     */
    public function options(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
            'id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],
        ]);

        $query = Product::query()
            ->select([
                'id',
                'name',
                'sku',
            ])
            ->orderBy('name');

        if (! empty($validated['id'])) {
            $query->where('id', $validated['id']);
        } else {
            $search = trim($validated['q'] ?? '');

            if (mb_strlen($search) < 2) {
                return response()->json([
                    'data' => [],
                    'has_more' => false,
                ]);
            }

            $query->where(function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $products->map(
                fn (Product $product) => [
                    'id' => $product->id,
                    'label' => $product->name,
                    'sku' => $product->sku,
                ]
            )->values(),
            'has_more' => $products->count() === 20,
        ]);
    }


    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

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

        $storedImage = null;

        try {
            $storedImage = $uploadedImage?->store(
                'products/images',
                'public'
            );

            DB::transaction(function () use ($data, $storedImage, &$product) {
                $product = Product::create($data);

                if ($storedImage) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $storedImage,
                        'alt' => $product->name,
                        'sort_order' => 0,
                        'is_primary' => true,
                    ]);
                }
            });
        } catch (Throwable $e) {
            if ($storedImage) {
                Storage::disk('public')->delete($storedImage);
            }

            throw $e;
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
        ]);

        $variants = $product
            ->allVariants()
            ->with('images.media')
            ->orderBy('type')
            ->orderBy('name')
            ->orderBy('value')
            ->orderBy('id')
            ->paginate(20, ['*'], 'variants_page')
            ->withQueryString();

        return view(
            'admin.products.show',
            compact('product', 'variants')
        );
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load('images.media');

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

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

        $storedImage = null;

        try {
            if ($uploadedImage) {
                $storedImage = $uploadedImage->store(
                    'products/images',
                    'public'
                );
            }

            DB::transaction(function () use (
                $product,
                $data,
                $storedImage
            ) {
                $product->update($data);

                if (! $storedImage) {
                    return;
                }

                $primaryImage =
                    $product->images()
                        ->where('is_primary', true)
                        ->first()
                    ?? $product->images()
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->first();

                if ($primaryImage) {
                    $primaryImage->update([
                        'path' => $storedImage,
                        'alt' => $product->name,
                        'is_primary' => true,
                    ]);

                    $product->images()
                        ->where('id', '!=', $primaryImage->id)
                        ->update(['is_primary' => false]);
                } else {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $storedImage,
                        'alt' => $product->name,
                        'sort_order' => 0,
                        'is_primary' => true,
                    ]);
                }
            });
        } catch (Throwable $e) {
            if ($storedImage) {
                Storage::disk('public')->delete($storedImage);
            }

            throw $e;
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
