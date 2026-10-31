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
        $settings = SeoSetting::current();

        $routeName = (string) $request->route()?->getName();
        $privateRoute = $request->is('admin', 'admin/*')
            || preg_match('/^(account|cart|checkout|login|register|password|logout)(\\\\.|$)/', $routeName) === 1
            || $request->is(
                'account', 'account/*',
                'cart', 'cart/*',
                'checkout', 'checkout/*',
                'login', 'register', 'forgot-password', 'reset-password/*'
            );

        // Admin, authentication and customer-specific responses must never be
        // indexed, even after the public SEO release switch is enabled.
        if ($privateRoute) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

            return $response;
        }

        // Apply the site-wide SEO gate to every Laravel response, not only HTML.
        // This also covers XML sitemaps, JSON responses and controller-served files.
        if (! $settings->indexing_enabled || ! $settings->following_enabled) {
            $response->headers->set('X-Robots-Tag', $settings->robots());
        } else {
            $response->headers->remove('X-Robots-Tag');
        }

        return $response;
    }
}
