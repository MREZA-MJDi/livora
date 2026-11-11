<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\MediaLibrary;
use App\Services\Media\ProductImageVariantGenerator;
use App\Services\Seo\ContentSeoAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
                'images' => fn ($query) => $query->limit(1),
                // The list only needs one image-bearing variant to decide whether
                // a product can be selected for the home hero. Do not hydrate every
                // variant image for every row in the catalog.
                'variants' => fn ($query) => $query
                    ->whereHas('images')
                    ->orderByDesc('stock')
                    ->orderBy('id')
                    ->limit(1)
                    ->with(['images' => fn ($images) => $images->orderBy('sort_order')->orderBy('id')->limit(1)]),
            ])
            ->when(request()->filled('search'), function ($query): void {
                $term = trim((string) request()->query('search'));
                $query->where(function ($searchQuery) use ($term): void {
                    $searchQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $homeHeroProductCount = Product::query()->where('is_home_hero', true)->count();

        return view('admin.products.index', compact('products', 'homeHeroProductCount'));
    }

    public function toggleHomeHero(Product $product): RedirectResponse
    {
        if (! $product->is_home_hero) {
            $hasImage = $product->images()->exists()
                || $product->variants()->whereHas('images')->exists();
            $hasActiveCategory = $product->category()->where('is_active', true)->exists();

            if ($product->status !== 'active' || ! $hasActiveCategory || ! $hasImage) {
                return back()->with('error', 'فقط محصول فعال با دسته‌بندی فعال و تصویر معتبر می‌تواند وارد اسلایدر شود.');
            }

            $result = DB::transaction(function () use ($product): string {
                $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
                if ($lockedProduct->is_home_hero) {
                    return 'already-selected';
                }

                $selected = Product::query()
                    ->where('is_home_hero', true)
                    ->lockForUpdate()
                    ->get(['id', 'home_hero_sort_order']);

                if ($selected->count() >= 10) {
                    return 'limit';
                }

                $nextOrder = ((int) $selected->max('home_hero_sort_order')) + 1;
                $lockedProduct->update([
                    'is_home_hero' => true,
                    'home_hero_sort_order' => $nextOrder,
                ]);

                return 'selected';
            });

            if ($result === 'limit') {
                return back()->with('error', 'حداکثر ۱۰ محصول می‌توانند در اسلایدر صفحه اصلی باشند.');
            }
        } else {
            $product->update([
                'is_home_hero' => false,
                'home_hero_sort_order' => null,
            ]);
        }

        $product->refresh();

        return back()->with('success', $product->is_home_hero
            ? 'محصول به اسلایدر صفحه اصلی اضافه شد.'
            : 'محصول از اسلایدر صفحه اصلی حذف شد.');
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
        $mediaId = $data['media_id'] ?? null;
        unset($data['related_product_ids'], $data['media_id'], $data['banner_media_id'], $data['image'], $data['banner_image']);

        $imageFile = $request->file('image');
        $originalImageFile = $request->file('original_image') ?: $imageFile;
        $imagePath = $originalImageFile?->store('products/originals', 'public');
        $variantSourcePath = $request->hasFile('original_image') && $imageFile
            ? $imageFile->store('products/crops', 'public')
            : null;
        if ($imageFile && (! $imagePath || ($request->hasFile('original_image') && ! $variantSourcePath))) {
            throw new RuntimeException('Product image could not be stored.');
        }
        $bannerImageUploaded = $request->hasFile('banner_image');
        try {
            $bannerMedia = $bannerImageUploaded
                ? MediaLibrary::store($request->file('banner_image'), (int) $request->user()->id, 'products/banners', $data['banner_image_alt'] ?? null)
                : ($request->boolean('remove_banner_image') ? null : MediaLibrary::image($request->input('banner_media_id')));
        } catch (Throwable $exception) {
            $this->deleteStoredPaths([$imagePath, $variantSourcePath]);
            throw $exception;
        }

        try {
            $createdProductId = null;
            DB::transaction(function () use ($request, $data, $relatedProductIds, $mediaId, $imageFile, $originalImageFile, $imagePath, $variantSourcePath, $bannerMedia, &$createdProductId): void {
                $product = Product::create($data);
                $createdProductId = $product->id;
                $product->relatedProducts()->sync(array_values(array_diff($relatedProductIds, [$product->id])));

                if ($bannerMedia) {
                    $product->update([
                        'banner_image' => $bannerMedia->path,
                        'banner_media_id' => $bannerMedia->id,
                        'banner_image_alt' => $data['banner_image_alt'] ?? $product->name,
                    ]);
                } elseif ($request->boolean('remove_banner_image')) {
                    $product->update([
                        'banner_image' => null,
                        'banner_media_id' => null,
                        'banner_image_alt' => null,
                    ]);
                }

                if ($imageFile || $mediaId) {
                    $media = $imageFile
                        ? $this->registerUploadedMedia($request, $originalImageFile, $imagePath, $variantSourcePath)
                        : Media::query()->whereKey($mediaId)->where('mime_type', 'like', 'image/%')->firstOrFail();

                    if ($imageFile && (! $media->thumbnail_path || ! $media->card_path || ! $media->detail_path)) {
                        $media = app(ProductImageVariantGenerator::class)->generate($media);
                    }

                    ProductImage::create([
                        'product_id' => $product->id,
                        'media_id' => $media->id,
                        'path' => $media->path,
                        'alt' => $product->name,
                        'sort_order' => 0,
                        'is_primary' => true,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            $this->deleteStoredPaths([$imagePath, $variantSourcePath]);
            $this->cleanupUploadedMedia($bannerMedia, $bannerImageUploaded);
            throw $exception;
        }

        if ($request->boolean('continue_to_variants')) {
            return redirect()
                ->route('admin.product-variants.create', ['product_id' => $createdProductId])
                ->with('success', 'محصول ایجاد شد؛ حالا اولین رنگ، اندازه یا تنوع آن را ثبت کن.');
        }

        return redirect()->route('admin.products.edit', $createdProductId)->with('success', 'محصول با موفقیت ایجاد شد. حالا می‌توانی تنوع‌های رنگ، جنس و اندازه را ثبت کنی.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'images.media', 'allVariants.images']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product, ContentSeoAnalyzer $seoAnalyzer): View
    {
        $product->load(['images.media', 'allVariants.images.media', 'relatedProducts:id,name']);
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
        $mediaId = $data['media_id'] ?? null;
        unset($data['related_product_ids'], $data['media_id'], $data['banner_media_id'], $data['image'], $data['banner_image'], $data['remove_primary_image'], $data['remove_banner_image']);

        $imageFile = $request->file('image');
        $originalImageFile = $request->file('original_image') ?: $imageFile;
        $imagePath = $originalImageFile?->store('products/originals', 'public');
        $variantSourcePath = $request->hasFile('original_image') && $imageFile
            ? $imageFile->store('products/crops', 'public')
            : null;
        if ($imageFile && (! $imagePath || ($request->hasFile('original_image') && ! $variantSourcePath))) {
            throw new RuntimeException('Product image could not be stored.');
        }
        $bannerImageUploaded = $request->hasFile('banner_image');
        try {
            $bannerMedia = $bannerImageUploaded
                ? MediaLibrary::store($request->file('banner_image'), (int) $request->user()->id, 'products/banners', $data['banner_image_alt'] ?? null)
                : ($request->boolean('remove_banner_image') ? null : (filled($request->input('banner_media_id')) ? MediaLibrary::image($request->input('banner_media_id')) : null));
        } catch (Throwable $exception) {
            $this->deleteStoredPaths([$imagePath, $variantSourcePath]);
            throw $exception;
        }

        $product->loadMissing('images.media');
        $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

        try {
            DB::transaction(function () use ($request, $product, $data, $relatedProductIds, $mediaId, $imageFile, $originalImageFile, $imagePath, $variantSourcePath, $bannerMedia, $primaryImage): void {
                $product->update($data);
                $product->relatedProducts()->sync(array_values(array_diff($relatedProductIds, [$product->id])));

                if ($bannerMedia) {
                    $product->update([
                        'banner_image' => $bannerMedia->path,
                        'banner_media_id' => $bannerMedia->id,
                        'banner_image_alt' => $data['banner_image_alt'] ?? $product->name,
                    ]);
                } elseif ($request->boolean('remove_banner_image')) {
                    $product->update([
                        'banner_image' => null,
                        'banner_media_id' => null,
                        'banner_image_alt' => null,
                    ]);
                }

                if ($imageFile || ($mediaId && ! $request->boolean('remove_primary_image'))) {
                    $media = $imageFile
                        ? $this->registerUploadedMedia($request, $originalImageFile, $imagePath, $variantSourcePath)
                        : Media::query()->whereKey($mediaId)->where('mime_type', 'like', 'image/%')->firstOrFail();

                    if ($imageFile && (! $media->thumbnail_path || ! $media->card_path || ! $media->detail_path)) {
                        $media = app(ProductImageVariantGenerator::class)->generate($media);
                    }

                    if ($primaryImage) {
                        $primaryImage->update([
                            'media_id' => $media->id,
                            'path' => $media->path,
                            'alt' => $product->name,
                            'is_primary' => true,
                        ]);
                        $product->images()->where('id', '!=', $primaryImage->id)->update(['is_primary' => false]);
                    } else {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'media_id' => $media->id,
                            'path' => $media->path,
                            'alt' => $product->name,
                            'sort_order' => 0,
                            'is_primary' => true,
                        ]);
                    }
                } elseif ($request->boolean('remove_primary_image') && $primaryImage) {
                    // Detach the product usage, but preserve the shared Media Library asset.
                    $primaryImage->variants()->detach();
                    $primaryImage->delete();

                    $replacement = $product->images()
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->first();
                    $replacement?->update(['is_primary' => true]);
                }
            });
        } catch (Throwable $exception) {
            $this->deleteStoredPaths([$imagePath, $variantSourcePath]);
            $this->cleanupUploadedMedia($bannerMedia, $bannerImageUploaded);
            throw $exception;
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'محصول با موفقیت بروزرسانی شد.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'محصول با موفقیت حذف شد.');
    }

    private function cleanupUploadedMedia(?Media $media, bool $wasUploaded): void
    {
        if (! $wasUploaded || ! $media) {
            return;
        }

        Storage::disk($media->disk ?: 'public')->delete($media->path);
        $media->delete();
    }

    private function registerUploadedMedia(StoreProductRequest|UpdateProductRequest $request, $file, string $path, ?string $variantSourcePath = null): Media
    {
        $media = Media::create([
            'user_id' => $request->user()->id,
            'disk' => 'public',
            'path' => $path,
            'variant_source_path' => $variantSourcePath,
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => strtolower($file->getClientOriginalExtension()),
            'size' => $file->getSize(),
            'alt' => $request->input('image_alt'),
        ]);

        return app(ProductImageVariantGenerator::class)->generate($media);
    }

    private function deleteStoredPaths(array $paths): void
    {
        foreach (array_unique($paths) as $path) $this->deleteStoredPath($path);
    }

    private function deleteStoredPath(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://') && ! str_starts_with($path, '//')) {
            Storage::disk('public')->delete($path);
        }
    }
}
