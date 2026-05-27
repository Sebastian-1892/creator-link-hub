<?php

use App\Http\Middleware\SetMarketingLocale;
use App\Services\BrandingService;

test('marketing locale route sets cookie and applies french branding', function () {
    app(BrandingService::class)->flushPayloadCache();
    app(\App\Services\TranslationService::class)->flushAll();

    $this->get(route('marketing.locale', ['locale' => 'fr']))
        ->assertRedirect();

    $this->withCookie(SetMarketingLocale::COOKIE_NAME, 'fr')
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Un lien. Tous les canaux', false);
});

test('marketing locale route sets cookie and applies english', function () {
    app(BrandingService::class)->flushPayloadCache();

    $this->get(route('marketing.locale', ['locale' => 'en']))
        ->assertRedirect();

    $this->withCookie(SetMarketingLocale::COOKIE_NAME, 'en')
        ->get(route('home'))
        ->assertOk()
        ->assertSee('One link. Every channel', false);
});

test('invalid marketing locale returns 404', function () {
    $this->get('/set-marketing-locale/xx')->assertNotFound();
});
