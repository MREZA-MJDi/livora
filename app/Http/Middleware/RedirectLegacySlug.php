<?php

namespace App\Http\Middleware;

use App\Models\SlugRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacySlug
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $oldPath = '/'.ltrim($request->path(), '/');
        $newPath = Cache::remember('slug_redirect:'.$oldPath, now()->addHour(), fn () => SlugRedirect::query()
            ->where('old_path', $oldPath)
            ->where('is_active', true)
            ->value('new_path'));

        if ($newPath && $newPath !== $oldPath) {
            $target = $newPath;
            if ($request->getQueryString()) {
                $target .= '?'.$request->getQueryString();
            }

            return redirect()->to($target, 301);
        }

        return $next($request);
    }
}
