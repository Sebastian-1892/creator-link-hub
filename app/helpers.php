<?php

use App\Services\BrandingService;

if (! function_exists('brand')) {
    /**
     * Übersetzter / gespeicherter Branding-Text (Schlüssel wie „marketing.headline“).
     */
    function brand(string $key, ?string $default = null): string
    {
        return app(BrandingService::class)->text($key, $default);
    }
}

if (! function_exists('brand_color')) {
    /**
     * Gespeicherte Branding-Farbe (Kurzschlüssel wie „primary“, „accent“).
     */
    function brand_color(string $shortKey, ?string $default = null): string
    {
        return app(BrandingService::class)->color($shortKey, $default);
    }
}

if (! function_exists('branding_payload')) {
    /**
     * Vollständiges Branding-Payload (Farben, Marketing, Bio, Logo).
     *
     * @return array<string, mixed>
     */
    function branding_payload(): array
    {
        return app(BrandingService::class)->payload();
    }
}
