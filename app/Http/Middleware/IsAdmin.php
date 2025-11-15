<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->role !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok.',
                'errors' => []
            ], 403);
        }

        return $next($request);
    }
}
