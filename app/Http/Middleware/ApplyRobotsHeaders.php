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
        $settings = SeoSetting::current();

        $privateRoute = $request->is(
            'admin', 'admin/*',
            'account', 'account/*',
            'cart', 'cart/*',
            'checkout', 'checkout/*',
            'login', 'register', 'forgot-password', 'reset-password/*', 'logout'
        );

        // Admin, authentication and customer-specific responses must never be
        // indexed, even after the public SEO release switch is enabled.
        if ($privateRoute) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

            return $response;
        }

        // Faceted/search/sort catalogue URLs are crawlable links, not indexable
        // landing pages. Their clean /shop canonical is emitted by the view.
        if ($request->routeIs('shop.index') && $request->hasAny([
            'category',
            'search',
            'min_price',
            'max_price',
            'installment',
            'featured',
            'new',
            'in_stock',
            'sort',
        ])) {
            $response->headers->set('X-Robots-Tag', $settings->robotsFor(false, true));

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
