<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ThrottleAuthenticatedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = auth()->user() ? auth()->user()->id : 'guest'; // Use user ID if authenticated, else 'guest'

        $key = "user:{$userId}";

        // Adjust the rate limiter to track based on the user ID
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json(['message' => 'Too many requests, please try again later.'], 429);
        }

        RateLimiter::hit($key, 60); // Adjust time period if necessary

        return $next($request);
    }
}

