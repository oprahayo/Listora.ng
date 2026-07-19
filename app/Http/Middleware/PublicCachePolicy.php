<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicCachePolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (auth()->check()) {
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->headers->set('X-Listora-Private', '1');
        } else {
            $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
            $response->headers->set('X-Listora-Private', '0');
        }

        return $response;
    }
}
