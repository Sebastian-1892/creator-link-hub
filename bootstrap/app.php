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
        $middleware->alias([
            'onboarding' => EnsureOnboardingCompleted::class,
        ]);
        // Livewire/Filament Datei-Uploads nutzen signierte URLs; hinter Nginx/TLS muss das Schema stimmen.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
