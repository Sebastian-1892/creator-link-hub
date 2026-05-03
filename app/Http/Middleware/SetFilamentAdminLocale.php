<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetFilamentAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = array_keys(config('creator.filament_locales', []));

        $locale = config('app.locale');

        if ($request->user() && is_string($request->user()->filament_locale) && in_array($request->user()->filament_locale, $allowed, true)) {
            $locale = $request->user()->filament_locale;
        } elseif (is_string($sessionLocale = $request->session()->get('filament_locale')) && in_array($sessionLocale, $allowed, true)) {
            $locale = $sessionLocale;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
