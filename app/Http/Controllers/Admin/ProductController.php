<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Seo\ContentSeoAnalyzer;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProductController extends Controller
{
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

    public function create(ContentSeoAnalyzer $seoAnalyzer): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $product = new Product();
        $product->setRelation('images', collect());

        return view('admin.products.create', [
            'categories' => $categories,
            'product' => $product,
            'seoAnalysis' => $seoAnalyzer->analyze($product),
            'availableRelatedProducts' => Product::query()->active()->orderBy('name')->limit(200)->get(['id', 'name']),
            'selectedRelatedProductIds' => [],
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $relatedProductIds = $data['related_product_ids'] ?? [];
        unset($data['related_product_ids']);

        $uploadedImage = $request->file('image');
        $uploadedBanner = $request->file('banner_image');
        unset($data['image'], $data['banner_image']);

        $storedPaths = [];

        try {
            $bannerPath = $uploadedBanner?->store('products/banners', 'public');
            if ($uploadedBanner && ! $bannerPath) {
                throw new RuntimeException('Product banner could not be stored.');
            }
            if ($bannerPath) {
                $storedPaths[] = $bannerPath;
            }

            $imagePath = $uploadedImage?->store('products/images', 'public');
            if ($uploadedImage && ! $imagePath) {
                throw new RuntimeException('Product image could not be stored.');
            }
            if ($imagePath) {
                $storedPaths[] = $imagePath;
            }

            DB::transaction(function () use ($data, $relatedProductIds, $bannerPath, $imagePath): void {
                $product = Product::create($data);
                $product->relatedProducts()->sync(array_values(array_diff($relatedProductIds, [$product->id])));

                if ($bannerPath) {
                    $product->update([
                        'banner_image' => $bannerPath,
                        'banner_image_alt' => $data['banner_image_alt'] ?? $product->name,
                    ]);
                }

                if ($imagePath) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $imagePath,
                        'alt' => $product->name,
                        'sort_order' => 0,
                        'is_primary' => true,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            throw $exception;
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول با موفقیت ایجاد شد.');
    }

    public function show(Product $product): View
    {
        $product->load([
            'category',
            'images',
            'allVariants.images',
        ]);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product, ContentSeoAnalyzer $seoAnalyzer): View
    {
        $product->load(['images', 'relatedProducts:id,name']);
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'seoAnalysis' => $seoAnalyzer->analyze($product),
            'availableRelatedProducts' => Product::query()->active()->where('id', '!=', $product->id)->orderBy('name')->limit(200)->get(['id', 'name']),
            'selectedRelatedProductIds' => $product->relatedProducts->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $relatedProductIds = $data['related_product_ids'] ?? [];
        unset($data['related_product_ids']);

        $uploadedImage = $request->file('image');
        $uploadedBanner = $request->file('banner_image');
        unset($data['image'], $data['banner_image']);

        $product->loadMissing('images');
        $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
        $oldImagePath = $primaryImage?->path;
        $oldBannerPath = $product->banner_image;
        $storedPaths = [];

        try {
            $bannerPath = $uploadedBanner?->store('products/banners', 'public');
            if ($uploadedBanner && ! $bannerPath) {
                throw new RuntimeException('Product banner could not be stored.');
            }
            if ($bannerPath) {
                $storedPaths[] = $bannerPath;
            }

            $imagePath = $uploadedImage?->store('products/images', 'public');
            if ($uploadedImage && ! $imagePath) {
                throw new RuntimeException('Product image could not be stored.');
            }
            if ($imagePath) {
                $storedPaths[] = $imagePath;
            }

            DB::transaction(function () use ($product, $data, $relatedProductIds, $bannerPath, $imagePath, $primaryImage): void {
                $product->update($data);
                $product->relatedProducts()->sync(array_values(array_diff($relatedProductIds, [$product->id])));

                if ($bannerPath) {
                    $product->update([
                        'banner_image' => $bannerPath,
                        'banner_image_alt' => $data['banner_image_alt'] ?? $product->name,
                    ]);
                }

                if ($imagePath) {
                    if ($primaryImage) {
                        $primaryImage->update([
                            'path' => $imagePath,
                            'alt' => $product->name,
                            'is_primary' => true,
                        ]);

                        $product->images()
                            ->where('id', '!=', $primaryImage->id)
                            ->update(['is_primary' => false]);
                    } else {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $imagePath,
                            'alt' => $product->name,
                            'sort_order' => 0,
                            'is_primary' => true,
                        ]);
                    }
                }
            });
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            throw $exception;
        }

        if ($bannerPath && $oldBannerPath !== $bannerPath) {
            $this->deleteStoredPath($oldBannerPath);
        }
        if ($imagePath && $oldImagePath !== $imagePath) {
            $this->deleteStoredPath($oldImagePath);
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'محصول با موفقیت بروزرسانی شد.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول با موفقیت حذف شد.');
    }

    private function deleteStoredPaths(array $paths): void
    {
        foreach (array_unique($paths) as $path) {
            $this->deleteStoredPath($path);
        }
    }

    private function deleteStoredPath(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://') && ! str_starts_with($path, '//')) {
            Storage::disk('public')->delete($path);
        }
    }
}
