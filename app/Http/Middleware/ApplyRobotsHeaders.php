<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyRobotsHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Keep every non-production deployment out of search results,
        // regardless of the SEO switches stored in the database.
        if (! config('app.env') !== 'production') {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

            return $response;
        }

        $privateRoute = $request->is(
            'admin', 'admin/*',
            'account', 'account/*',
            'cart', 'cart/*',
            'checkout', 'checkout/*',
            'login', 'register', 'forgot-password', 'reset-password/*', 'logout'
        );

        if ($privateRoute) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

            return $response;
        }

        // Public production pages must remain crawlable. Per-page SEO metadata
        // is still rendered in the document; private commerce routes stay blocked.
        $response->headers->remove('X-Robots-Tag');

        return $response;
    }
}
