<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Leitet Pfade mit doppelten Slashes um (z. B. //admin → /admin).
 * Tritt auf, wenn APP_URL oder Instanz-URLs mit trailing slash verlinkt werden.
 */
final class NormalizeRequestPath
{
    public function handle(Request $request, Closure $next): Response
    {
        $uri = $request->getRequestUri();
        if (! str_contains($uri, '//')) {
            return $next($request);
        }

        $parts = explode('?', $uri, 2);
        $path = preg_replace('#/{2,}#', '/', $parts[0]) ?? $parts[0];
        if ($path === $parts[0]) {
            return $next($request);
        }

        $target = $path.(isset($parts[1]) ? '?'.$parts[1] : '');

        return redirect()->to($target, 301);
    }
}
