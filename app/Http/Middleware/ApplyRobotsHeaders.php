<?php

namespace App\\Http\\Middleware;

use App\\Models\\SeoSetting;
use Closure;
use Illuminate\\Http\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

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

        // Keep the emergency global noindex policy in an HTTP header so it
        // still applies if a template is changed or serves a custom HTML page.
        // Per-page directives are emitted in the HTML when indexing is enabled.
        if (! $settings->indexing_enabled && str_contains($contentType, 'text/html')) {
            $response->headers->set('X-Robots-Tag', $settings->robots());
        }

        return $response;
    }
}
