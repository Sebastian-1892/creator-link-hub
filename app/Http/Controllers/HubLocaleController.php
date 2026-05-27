<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HubLocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $allowed = array_keys(config('creator.hub_locales', []));

        abort_unless(in_array($locale, $allowed, true), 404);

        $request->session()->put('hub_locale', $locale);

        if ($user = $request->user()) {
            $user->forceFill(['hub_locale' => $locale])->save();
        }

        return redirect()->back();
    }
}
