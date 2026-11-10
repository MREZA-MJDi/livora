<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Media;
use App\Support\MediaLibrary;
use App\Services\Media\ProductImageVariantGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

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
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'price']);
        $types = ProductVariant::query()->select('type')->distinct()->orderBy('type')->pluck('type');

        return view('admin.product-variants.index', compact('variants', 'products', 'types'));
    }

    public function create(Request $request): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'price']);
        $selectedProductId = $request->filled('product_id') ? $request->integer('product_id') : null;
        $selectedProductImages = $selectedProductId
            ? ProductImage::query()->where('product_id', $selectedProductId)->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        $defaultType = $request->query('type', 'اندازه');
        $defaultName = $request->query('name', 'اندازه مبل');

        return view('admin.product-variants.create', compact('products', 'selectedProductId', 'selectedProductImages', 'defaultType', 'defaultName'));
    }

    public function store(StoreProductVariantRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imageIds = array_values(array_unique($data['image_ids'] ?? []));
        $variantImage = $data['variant_image'] ?? null;
        $originalImage = $data['variant_original_image'] ?? $variantImage;
        unset($data['image_ids'], $data['variant_image'], $data['variant_original_image']);

        $createdMedia = null;
        try {
            $variant = DB::transaction(function () use ($request, $data, &$imageIds, $variantImage, $originalImage, &$createdMedia): ProductVariant {
                $variant = ProductVariant::create($data);

                if ($variantImage instanceof UploadedFile) {
                    $image = $this->createVariantProductImage($request, $variant, $variantImage, $originalImage, $createdMedia);
                    array_unshift($imageIds, $image->id);
                }

                $variant->images()->sync($this->orderedImageSync($imageIds));

                return $variant;
            });
        } catch (Throwable $exception) {
            report($exception);
            $this->cleanupVariantMedia($createdMedia);

            return back()->withInput()->with('error', 'تنوع یا تصویر آن ذخیره نشد. فایل‌های قبلی حفظ شدند؛ لطفاً دوباره تلاش کنید.');
        }

        if ($request->boolean('continue')) {
            return redirect()
                ->route('admin.product-variants.create', [
                    'product_id' => $variant->product_id,
                    'type' => $variant->type,
                    'name' => $variant->name,
                ])
                ->with('success', 'این گزینه ذخیره شد؛ حالا مقدار بعدی را وارد کن.');
        }

        return redirect()
            ->route('admin.products.edit', $variant->product_id)
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
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'price']);
        $selectedProductId = (int) $productVariant->product_id;
        $selectedProductImages = ProductImage::query()->where('product_id', $selectedProductId)->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.product-variants.edit', compact('productVariant', 'products', 'selectedProductId', 'selectedProductImages'));
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant): RedirectResponse
    {
        $data = $request->validated();
        $imageIds = array_values(array_unique($data['image_ids'] ?? []));
        $variantImage = $data['variant_image'] ?? null;
        $originalImage = $data['variant_original_image'] ?? $variantImage;
        unset($data['image_ids'], $data['variant_image'], $data['variant_original_image']);

        $createdMedia = null;
        try {
            DB::transaction(function () use ($request, $data, &$imageIds, $variantImage, $originalImage, $productVariant, &$createdMedia): void {
                $productVariant->update($data);

                if ($variantImage instanceof UploadedFile) {
                    $image = $this->createVariantProductImage($request, $productVariant, $variantImage, $originalImage, $createdMedia);
                    array_unshift($imageIds, $image->id);
                }

                $productVariant->images()->sync($this->orderedImageSync($imageIds));
            });
        } catch (Throwable $exception) {
            report($exception);
            $this->cleanupVariantMedia($createdMedia);

            return back()->withInput()->with('error', 'تغییرات یا تصویر جدید ذخیره نشد. اطلاعات و تصاویر قبلی حفظ شدند.');
        }

        return redirect()
            ->route('admin.products.edit', $productVariant->product_id)
            ->with('success', 'تنوع محصول با موفقیت بروزرسانی شد.');
    }

    /**
     * Create a normal product image for the uploaded variant cover, then link it
     * to the variant. Keeping it in product_images preserves the existing gallery
     * and media pipeline while the pivot makes the image variant-specific.
     */
    private function createVariantProductImage(
        Request $request,
        ProductVariant $variant,
        UploadedFile $croppedFile,
        ?UploadedFile $originalFile,
        ?Media &$createdMedia
    ): ProductImage {
        $product = $variant->product;
        $alt = trim($product->name.' - '.$variant->type.': '.$variant->value);

        $createdMedia = MediaLibrary::store(
            $originalFile ?: $croppedFile,
            (int) $request->user()->id,
            'products/originals',
            $alt
        );

        try {
            $cropPath = $croppedFile->store('products/crops', 'public');
            if (! $cropPath) {
                throw new \RuntimeException('Variant image crop could not be stored.');
            }

            $createdMedia->update(['variant_source_path' => $cropPath]);
            $createdMedia = app(ProductImageVariantGenerator::class)->generate($createdMedia->refresh());

            return ProductImage::create([
                'product_id' => $variant->product_id,
                'media_id' => $createdMedia->id,
                'path' => $createdMedia->path,
                'alt' => $alt,
                'sort_order' => 0,
                'is_primary' => false,
            ]);
        } catch (Throwable $exception) {
            $this->cleanupVariantMedia($createdMedia);
            $createdMedia = null;
            throw $exception;
        }
    }

    /**
     * Keep the order chosen in the admin form deterministic.
     */
    private function orderedImageSync(array $imageIds): array
    {
        $sync = [];
        foreach (array_values(array_unique(array_map('intval', $imageIds))) as $position => $imageId) {
            if ($imageId > 0) {
                $sync[$imageId] = ['sort_order' => $position];
            }
        }

        return $sync;
    }

    private function cleanupVariantMedia(?Media $media): void
    {
        if (! $media) {
            return;
        }

        $disk = Storage::disk($media->disk ?: 'public');
        foreach (['path', 'variant_source_path', 'thumbnail_path', 'card_path', 'detail_path'] as $column) {
            if (filled($media->{$column})) {
                $disk->delete($media->{$column});
            }
        }

        $media->delete();
    }

    public function destroy(ProductVariant $productVariant): RedirectResponse
    {
        $productId = $productVariant->product_id;

        try {
            $productVariant->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'حذف تنوع انجام نشد. لطفاً دوباره تلاش کنید.');
        }

        return redirect()
            ->route('admin.products.edit', $productId)
            ->with('success', 'تنوع محصول با موفقیت حذف شد.');
    }
}
