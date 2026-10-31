<?php

namespace App\Http\Middleware;

use App\Models\SeoSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyRobotsHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('admin') || $request->is('admin/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

            return $response;
        }

        $settings = SeoSetting::current();
        $contentType = (string) $response->headers->get('Content-Type');

        // The global switch is authoritative for every public HTML page.
        // A per-page index/follow checkbox cannot override a closed global gate.
        if (str_contains($contentType, 'text/html')) {
            if (! $settings->indexing_enabled || ! $settings->following_enabled) {
                $response->headers->set('X-Robots-Tag', $settings->robots());
            } else {
                $response->headers->remove('X-Robots-Tag');
            }
        }

        return $response;
    }
}
