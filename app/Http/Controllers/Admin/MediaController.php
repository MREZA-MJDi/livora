<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\MediaUploadRequest;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * Display media library.
     */
    public function index(): View
    {
        $media = Media::with('user')
            ->latest()
            ->paginate(24);

        return view('admin.media.index', compact('media'));
    }

    /**
     * @return View
     */
    public function create(): View
    {
        return view('admin.media.create');
    }

    /**
     * Upload a new media file.
     */
    public function store(MediaUploadRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        $disk = 'public';

        $path = $file->store('media', $disk);

        Media::create([
            'user_id' => auth()->id(),
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

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'رسانه با موفقیت آپلود شد.');
    }

    /**
     * Delete a media file.
     */
    public function destroy(Media $media): RedirectResponse
    {
        if ($media->disk && $media->path) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'رسانه با موفقیت حذف شد.');
    }
}
