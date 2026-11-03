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
            ->with(['category', 'images' => fn ($query) => $query->limit(1)])
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
        $mediaId = $data['media_id'] ?? null;
        unset($data['related_product_ids'], $data['media_id'], $data['banner_media_id'], $data['image'], $data['banner_image']);

        $imageFile = $request->file('image');
        $imagePath = $imageFile?->store('products/images', 'public');
        if ($imageFile && ! $imagePath) {
            throw new RuntimeException('Product image could not be stored.');
        }
        $bannerMedia = $request->hasFile('banner_image')
            ? MediaLibrary::store($request->file('banner_image'), (int) $request->user()->id, 'products/banners', $data['banner_image_alt'] ?? null)
            : MediaLibrary::image($request->input('banner_media_id'));

        try {
            DB::transaction(function () use ($request, $data, $relatedProductIds, $mediaId, $imageFile, $imagePath, $bannerMedia): void {
                $product = Product::create($data);
                $product->relatedProducts()->sync(array_values(array_diff($relatedProductIds, [$product->id])));

                if ($bannerMedia) {
                    $product->update([
                        'banner_image' => $bannerMedia->path,
                        'banner_media_id' => $bannerMedia->id,
                        'banner_image_alt' => $data['banner_image_alt'] ?? $product->name,
                    ]);
                }

                if ($imageFile || $mediaId) {
                    $media = $imageFile
                        ? $this->registerUploadedMedia($request, $imageFile, $imagePath)
                        : Media::query()->whereKey($mediaId)->where('mime_type', 'like', 'image/%')->firstOrFail();

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
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            throw $exception;
        }

        return redirect()->route('admin.products.index')->with('success', 'محصول با موفقیت ایجاد شد.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'images.media', 'allVariants.images']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product, ContentSeoAnalyzer $seoAnalyzer): View
    {
        $product->load(['images.media', 'relatedProducts:id,name']);
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
        unset($data['related_product_ids'], $data['media_id'], $data['banner_media_id'], $data['image'], $data['banner_image']);

        $imageFile = $request->file('image');
        $imagePath = $imageFile?->store('products/images', 'public');
        if ($imageFile && ! $imagePath) {
            throw new RuntimeException('Product image could not be stored.');
        }
        $bannerMedia = $request->hasFile('banner_image')
            ? MediaLibrary::store($request->file('banner_image'), (int) $request->user()->id, 'products/banners', $data['banner_image_alt'] ?? null)
            : (filled($request->input('banner_media_id')) ? MediaLibrary::image($request->input('banner_media_id')) : null);

        $product->loadMissing('images.media');
        $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

        try {
            DB::transaction(function () use ($request, $product, $data, $relatedProductIds, $mediaId, $imageFile, $imagePath, $bannerMedia, $primaryImage): void {
                $product->update($data);
                $product->relatedProducts()->sync(array_values(array_diff($relatedProductIds, [$product->id])));

                if ($bannerMedia) {
                    $product->update([
                        'banner_image' => $bannerMedia->path,
                        'banner_media_id' => $bannerMedia->id,
                        'banner_image_alt' => $data['banner_image_alt'] ?? $product->name,
                    ]);
                }

                if ($imageFile || $mediaId) {
                    $media = $imageFile
                        ? $this->registerUploadedMedia($request, $imageFile, $imagePath)
                        : Media::query()->whereKey($mediaId)->where('mime_type', 'like', 'image/%')->firstOrFail();

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
                }
            });
        } catch (Throwable $exception) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            throw $exception;
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'محصول با موفقیت بروزرسانی شد.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'محصول با موفقیت حذف شد.');
    }

    private function registerUploadedMedia(StoreProductRequest|UpdateProductRequest $request, $file, string $path): Media
    {
        return Media::create([
            'user_id' => $request->user()->id,
            'disk' => 'public',
            'path' => $path,
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => strtolower($file->getClientOriginalExtension()),
            'size' => $file->getSize(),
            'alt' => $request->input('image_alt'),
        ]);
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
