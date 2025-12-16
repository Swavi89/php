<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = auth()->user();

        // Check if user is active
        if (!$user->isActive()) {
            $message = match($user->status) {
                'suspended' => 'Your account has been suspended. Please contact support.',
                'banned' => 'Your account has been banned. Please contact support.',
                default => 'Your account is not active.',
            };

            return response()->json([
                'success' => false,
                'message' => $message,
                'status' => $user->status,
            ], 403);
        }

        return $next($request);
    }
}
