<?php

use App\Http\Middleware\EnsureOnboardingCompleted;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\NormalizeRequestPath::class);

        $middleware->alias([
            'onboarding' => EnsureOnboardingCompleted::class,
        ]);
        // Livewire/Filament Datei-Uploads nutzen signierte URLs; hinter Nginx/TLS muss das Schema stimmen.
        $middleware->trustProxies(at: '*');
        $trustedHosts = [];
        $appUrl = (string) env('APP_URL', '');
        if ($appUrl !== '') {
            $host = parse_url($appUrl, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                $trustedHosts[] = $host;
            }
        }
        $extra = (string) env('APP_EXTRA_HOSTS', '');
        foreach (explode(',', $extra) as $part) {
            $part = strtolower(trim($part));
            if ($part !== '') {
                $trustedHosts[] = $part;
            }
        }
        if ($trustedHosts !== []) {
            $middleware->trustHosts(at: array_values(array_unique($trustedHosts)));
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
