<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormatUnauthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (AuthenticationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            throw $e;
        }
    }
}