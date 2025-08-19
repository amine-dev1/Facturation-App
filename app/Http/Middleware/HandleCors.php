<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get CORS configuration
        $allowedOrigins = config('cors.allowed_origins', ['*']);
        $allowedMethods = config('cors.allowed_methods', ['*']);
        $allowedHeaders = config('cors.allowed_headers', ['*']);
        $supportsCredentials = config('cors.supports_credentials', false);
        $maxAge = config('cors.max_age', 0);

        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            return $this->buildPreflightResponse(
                $allowedMethods,
                $allowedHeaders,
                $maxAge
            );
        }

        // Handle actual requests
        $response = $next($request);

        // Add CORS headers
        $this->addCorsHeaders(
            $response,
            $allowedOrigins,
            $allowedMethods,
            $allowedHeaders,
            $supportsCredentials
        );

        return $response;
    }

    protected function buildPreflightResponse(array $methods, array $headers, int $maxAge): Response
    {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', implode(', ', $methods))
            ->header('Access-Control-Allow-Headers', implode(', ', $headers))
            ->header('Access-Control-Max-Age', $maxAge)
            ->header('Vary', 'Origin');
    }

    protected function addCorsHeaders(
        Response $response,
        array $origins,
        array $methods,
        array $headers,
        bool $supportsCredentials
    ): void {
        $origin = request()->header('Origin');

        if (in_array('*', $origins) || in_array($origin, $origins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin ?? '*');
        }

        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $methods));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $headers));
        $response->headers->set('Access-Control-Expose-Headers', implode(', ', $headers));
        $response->headers->set('Vary', 'Origin');

        if ($supportsCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
    }
}