<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\MediaUploadRequest;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class MediaController extends Controller
{
    public function index(): View
    {
        $media = Media::with('user')->latest()->paginate(24);

        return view('admin.media.index', compact('media'));
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
                    'extension' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'alt' => $request->input('alt'),
                    'description' => $request->input('description'),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'رسانه با موفقیت آپلود شد.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        if (
            $media->disk
            && $media->path
            && ! str_starts_with($media->path, 'http://')
            && ! str_starts_with($media->path, 'https://')
            && ! str_starts_with($media->path, '//')
        ) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'رسانه با موفقیت حذف شد.');
    }
}
