<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CatchDatabaseExceptions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (PDOException | QueryException $e) {
            Log::error('DB Exception: ' . $e->getMessage());

            return response()->json([
                'error' => 'Database error',
                'message' => 'Database connection failed or query error.'
            ], 500);
        }
    }
}