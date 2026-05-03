<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FilamentLocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $allowed = array_keys(config('creator.filament_locales', []));

        abort_unless(in_array($locale, $allowed, true), 404);

        $user = $request->user();
        abort_unless($user && $user->is_admin, 403);

        $user->forceFill(['filament_locale' => $locale])->save();

        $request->session()->put('filament_locale', $locale);

        return redirect()->back();
    }
}
