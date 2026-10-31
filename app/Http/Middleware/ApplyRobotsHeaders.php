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

        $response->headers->set('X-Robots-Tag', SeoSetting::current()->robots());

        return $response;
    }
}
