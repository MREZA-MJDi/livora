<?php

namespace App\Http\Middleware;

use App\Models\SlugRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacySlug
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $oldPath = '/'.ltrim($request->path(), '/');
        $redirect = SlugRedirect::query()
            ->where('old_path', $oldPath)
            ->where('is_active', true)
            ->first();

        if ($redirect && $redirect->new_path !== $oldPath) {
            $target = $redirect->new_path;
            if ($request->getQueryString()) {
                $target .= '?'.$request->getQueryString();
            }

            return redirect()->to($target, 301);
        }

        return $next($request);
    }
}
