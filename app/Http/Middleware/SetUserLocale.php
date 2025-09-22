<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetUserLocale
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('sanctum')->user();

        if ($user && $user->user_lang) {
            App::setLocale($this->sanitizeLocale($user->user_lang));
        } else {
            $header = $request->header('Accept-Language');
            $locale = $this->parsePreferredLocale($header) ?? config('app.locale');
            App::setLocale($this->sanitizeLocale($locale));
        }

        return $next($request);
    }

    protected function parsePreferredLocale($acceptLanguageHeader)
    {
        if (!$acceptLanguageHeader) {
            return null;
        }

        // Example: "en-IN,en;q=0.9,nl;q=0.8"
        $locales = explode(',', $acceptLanguageHeader);
        $primaryLocale = explode(';', $locales[0])[0] ?? null;

        return $primaryLocale ? str_replace('_', '-', trim($primaryLocale)) : null;
    }

    protected function sanitizeLocale($locale)
    {
        // Only allow whitelisted locales
        $allowed = ['en', 'nl'];

        $locale = strtolower(substr($locale, 0, 2));

        return in_array($locale, $allowed) ? $locale : config('app.fallback_locale');
    }
}

