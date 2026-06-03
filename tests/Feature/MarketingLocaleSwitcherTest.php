<?php

use App\Http\Middleware\SetMarketingLocale;
use App\Services\BrandingService;

test('marketing locale route sets cookie and applies french branding', function () {
    app(BrandingService::class)->flushPayloadCache();
    app(\App\Services\TranslationService::class)->flushAll();

    $this->get(route('marketing.locale', ['locale' => 'fr']))
        ->assertRedirect();

    $this->withCookie(SetMarketingLocale::COOKIE_NAME, 'fr')
        ->get(route('pricing'))
        ->assertOk()
        ->assertSee('Tarifs simples', false)
        ->assertDontSee('Simple pricing', false);
});

test('marketing locale route sets cookie and applies english', function () {
    app(BrandingService::class)->flushPayloadCache();

    $this->get(route('marketing.locale', ['locale' => 'en']))
        ->assertRedirect();

    $this->withCookie(SetMarketingLocale::COOKIE_NAME, 'en')
        ->get(route('pricing'))
        ->assertOk()
        ->assertSee('Simple pricing', false);
});

test('invalid marketing locale returns 404', function () {
    $this->get('/set-marketing-locale/xx')->assertNotFound();
});

test('marketing pages accept locale query parameter for admin preview', function () {
    app(BrandingService::class)->flushPayloadCache();

    $this->get(route('pricing', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('Simple pricing', false);
});
