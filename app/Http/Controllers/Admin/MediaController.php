<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\MediaUploadRequest;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:all,images,documents'],
            'sort' => ['nullable', 'in:newest,oldest,name'],
        ]);

        $query = Media::query()->with(['user', 'productImages.product']);

        if (filled($filters['q'] ?? null)) {
            $term = trim($filters['q']);
            $query->where(function ($builder) use ($term): void {
                $builder->where('original_name', 'like', "%{$term}%")
                    ->orWhere('alt', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (($filters['type'] ?? 'all') === 'images') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif (($filters['type'] ?? 'all') === 'documents') {
            $query->where('mime_type', 'not like', 'image/%');
        }

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->oldest(),
            'name' => $query->orderBy('original_name'),
            default => $query->latest(),
        };

        $media = $query->paginate(24)->withQueryString();

        return view('admin.media.index', compact('media', 'filters'));
    }

    public function picker(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $media = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim($filters['q']);
                $query->where(function ($builder) use ($term): void {
                    $builder->where('original_name', 'like', "%{$term}%")
                        ->orWhere('alt', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(30);

        return response()->json([
            'data' => $media->getCollection()->map(fn (Media $item): array => [
                'id' => $item->id,
                'name' => $item->original_name,
                'alt' => $item->alt,
                'url' => $item->url,
                'size' => $item->human_size,
            ])->values(),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'total' => $media->total(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.media.create');
    }

    public function store(MediaUploadRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $disk = 'public';
        $path = $file->store('media', $disk);

        if (! $path) {
            throw new RuntimeException('Media file could not be stored.');
        }

        try {
            DB::transaction(function () use ($request, $file, $disk, $path): void {
                Media::create([
                    'user_id' => $request->user()->id,
                    'disk' => $disk,
                    'path' => $path,
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => strtolower($file->getClientOriginalExtension()),
                    'size' => $file->getSize(),
                    'alt' => $request->input('alt'),
                    'description' => $request->input('description'),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }

        return redirect()->route('admin.media.index')
            ->with('success', 'رسانه با موفقیت به کتابخانه اضافه شد.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $usages = [
            'تصاویر محصولات' => $media->productImages()->count(),
            'بنر محصولات' => $media->productBanners()->count(),
            'دسته‌بندی‌ها' => $media->categories()->count(),
            'مقاله‌ها' => $media->blogPosts()->count(),
            'صفحه‌ها' => $media->contentPages()->count(),
            'بنر فروشگاه' => $media->shopBannerSettings()->count(),
        ];
        $usedBy = collect($usages)->filter()->map(fn ($count, $label) => $label.' ('.$count.')')->values();

        if ($usedBy->isNotEmpty()) {
            return redirect()->route('admin.media.index')
                ->with('error', 'این رسانه هنوز استفاده می‌شود: '.$usedBy->implode('، ').'. ابتدا اتصال‌های آن را حذف یا جایگزین کنید.');
        }

        DB::transaction(fn () => $media->delete());

        if ($media->disk && $media->path
            && ! str_starts_with($media->path, 'http://')
            && ! str_starts_with($media->path, 'https://')
            && ! str_starts_with($media->path, '//')) {
            Storage::disk($media->disk)->delete($media->path);
        }

        return redirect()->route('admin.media.index')
            ->with('success', 'رسانه حذف شد.');
    }
}
