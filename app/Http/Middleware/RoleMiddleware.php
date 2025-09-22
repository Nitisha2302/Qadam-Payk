<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @param  array  ...$roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['message' => 'Please log in to access this area.']);
        }

        $user = Auth::user();
        if (!in_array($user->role, $roles)) {
            return redirect()->route('login')->withErrors(['message' => 'You do not have access to this area.']);
        }

        return $next($request);
    }
}
