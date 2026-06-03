<?php

namespace App\Http\Middleware;

use App\Services\TranslationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetMarketingLocale
{
    public const COOKIE_NAME = 'clh_marketing_locale';

    public const SESSION_KEY = 'clh_marketing_locale';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isMarketingRoute($request)) {
            return $next($request);
        }

        $allowed = TranslationService::platformLocales();
        $locale = TranslationService::tenantDefaultLocale();

        $queryLocale = $request->query('locale');
        if (is_string($queryLocale) && in_array($queryLocale, $allowed, true)) {
            $locale = $queryLocale;
        } else {
            $sessionLocale = $request->session()->get(self::SESSION_KEY);
            if (is_string($sessionLocale) && in_array($sessionLocale, $allowed, true)) {
                $locale = $sessionLocale;
            } else {
                $cookie = $request->cookie(self::COOKIE_NAME);
                if (is_string($cookie) && in_array($cookie, $allowed, true)) {
                    $locale = $cookie;
                }
            }
        }

        App::setLocale($locale);

        return $next($request);
    }

    protected function isMarketingRoute(Request $request): bool
    {
        return $request->routeIs(
            'home',
            'pricing',
            'faq',
            'help',
            'legal.impressum',
            'legal.datenschutz',
            'legal.agb',
        );
    }
}
