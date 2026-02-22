<?php

// app/Http/Middleware/SetUserLanguage.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserLang;

class SetUserLanguage
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $lang = 'ru'; // default

        if ($user) {
            $deviceId = $request->header('device_id');
            $deviceType = $request->header('device_type');

            $userLang = UserLang::where('user_id', $user->id)
                ->where('device_id', $deviceId)
                ->where('device_type', $deviceType)
                ->first();

            if ($userLang) {
                $lang = $userLang->language;
            }
        }

        app()->setLocale($lang);

        return $next($request);
    }
}
