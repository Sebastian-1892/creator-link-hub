<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetHubLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = array_keys(config('creator.hub_locales', ['de' => [], 'en' => []]));

        $locale = (string) config('app.locale', 'de');

        if ($request->user() && is_string($request->user()->hub_locale) && in_array($request->user()->hub_locale, $allowed, true)) {
            $locale = $request->user()->hub_locale;
        } elseif (is_string($sessionLocale = $request->session()->get('hub_locale')) && in_array($sessionLocale, $allowed, true)) {
            $locale = $sessionLocale;
        } else {
            $preferred = $request->getPreferredLanguage($allowed);
            if (is_string($preferred) && $preferred !== '') {
                $locale = $preferred;
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
