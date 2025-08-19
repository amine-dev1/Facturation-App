<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SanctumAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifier si L'api contient un token d'authentification le guard dans ce cas est sanctum 
        if ($request->bearerToken() && Auth::guard('sanctum')->check()) {
            return $next($request);
        }

        // verifié si l'authentification est valide a travers les sessions
        if (Auth::check()) {
            return $next($request);
        }
    
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }
}