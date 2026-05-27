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
        $defaultLocale = (string) config('app.locale', 'de');

        if (! $this->usesHubLocale($request)) {
            // Marketing/Legal: fest Deutsch bis eigene Marketing-i18n (Plan P2).
            App::setLocale('de');

            return $next($request);
        }

        $locale = $defaultLocale;

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

    protected function usesHubLocale(Request $request): bool
    {
        return $request->routeIs(
            'dashboard',
            'bio.edit',
            'bio.avatar.store',
            'design.edit',
            'design.wallpaper.store',
            'design.wallpaper.destroy',
            'design.banner.store',
            'design.banner.destroy',
            'links.manage',
            'links.redirect',
            'analytics',
            'billing',
            'profile',
            'onboarding',
            'hub.locale',
            'login',
            'register',
            'password.request',
            'password.reset',
            'password.confirm',
            'verification.notice',
            'verification.verify',
            'public.profile',
        );
    }
}
