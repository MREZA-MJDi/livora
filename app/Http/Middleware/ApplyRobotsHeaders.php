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

        if (str_contains($contentType, 'text/html')) {
            $routeName = (string) $request->route()?->getName();
            $privateRoute = preg_match('/^(account|cart|checkout|login|register|password|logout)(\\.|$)/', $routeName) === 1
                || $request->is('account/*', 'cart/*', 'checkout/*', 'login', 'register', 'forgot-password', 'reset-password/*');

            if ($privateRoute) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            } elseif (! $settings->indexing_enabled || ! $settings->following_enabled) {
                $response->headers->set('X-Robots-Tag', $settings->robots());
            } else {
                $response->headers->remove('X-Robots-Tag');
            }
        }

        return $response;
    }
}
