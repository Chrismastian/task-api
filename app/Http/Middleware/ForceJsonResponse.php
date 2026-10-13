<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This app serves JSON only. Without an `Accept: application/json` header
 * Laravel assumes a browser and tries to render HTML error pages — which for
 * a 401 means redirecting to a named `login` route that does not exist here,
 * blowing up as a 500 instead. Forcing JSON makes every error render as JSON.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
