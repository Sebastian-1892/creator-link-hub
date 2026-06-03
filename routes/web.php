<?php

use App\Http\Controllers\FilamentLocaleController;
use App\Http\Controllers\HubLocaleController;
use App\Http\Controllers\MarketingLocaleController;
use App\Http\Controllers\LinkRedirectController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ProfileAvatarController;
use App\Http\Controllers\ProfileDesignImageController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SpotifyAuthController;
use App\Livewire\AnalyticsDashboard;
use App\Livewire\BillingPortal;
use App\Livewire\BioPageEditor;
use App\Livewire\DesignEditor;
use App\Livewire\LinkManager;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/pricing', [MarketingController::class, 'pricing'])->name('pricing');
Route::get('/faq', [MarketingController::class, 'faq'])->name('faq');
Route::get('/help', [MarketingController::class, 'help'])->name('help');

Route::view('/legal/impressum', 'legal.impressum')->name('legal.impressum');
Route::view('/legal/datenschutz', 'legal.datenschutz')->name('legal.datenschutz');
Route::view('/legal/agb', 'legal.agb')->name('legal.agb');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\nSitemap: ".url('/sitemap.xml')."\n";

    return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

Route::get('/go/{link}', LinkRedirectController::class)
    ->middleware('throttle:link-go')
    ->name('links.redirect');

Volt::route('onboarding', 'pages.onboarding')
    ->middleware(['auth', 'verified'])
    ->name('onboarding');

Route::middleware(['auth', 'verified', 'onboarding'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('hub/links', LinkManager::class)->name('links.manage');
    Route::get('hub/bio', BioPageEditor::class)->name('bio.edit');
    Route::post('hub/bio/avatar', [ProfileAvatarController::class, 'store'])->name('bio.avatar.store');
    Route::get('hub/design', DesignEditor::class)->name('design.edit');
    Route::post('hub/design/wallpaper', [ProfileDesignImageController::class, 'storeWallpaper'])->name('design.wallpaper.store');
    Route::delete('hub/design/wallpaper', [ProfileDesignImageController::class, 'destroyWallpaper'])->name('design.wallpaper.destroy');
    Route::post('hub/design/banner', [ProfileDesignImageController::class, 'storeBanner'])->name('design.banner.store');
    Route::delete('hub/design/banner', [ProfileDesignImageController::class, 'destroyBanner'])->name('design.banner.destroy');
    Route::get('hub/analytics', AnalyticsDashboard::class)->name('analytics');
    Route::get('hub/billing', BillingPortal::class)->name('billing');
    Route::get('hub/spotify/connect', [SpotifyAuthController::class, 'connect'])->name('spotify.connect');
    Route::get('hub/spotify/callback', [SpotifyAuthController::class, 'callback'])->name('spotify.callback');
    Route::post('hub/spotify/disconnect', [SpotifyAuthController::class, 'disconnect'])->name('spotify.disconnect');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';

Route::get('set-hub-locale/{locale}', HubLocaleController::class)
    ->whereIn('locale', ['de', 'en', 'fr', 'it'])
    ->name('hub.locale');

Route::get('set-marketing-locale/{locale}', MarketingLocaleController::class)
    ->whereIn('locale', ['de', 'en', 'fr', 'it'])
    ->name('marketing.locale');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('set-filament-locale/{locale}', FilamentLocaleController::class)
        ->whereIn('locale', ['en', 'de', 'fr', 'it'])
        ->name('filament-admin.locale');
});

Route::get('p/{slug}', [PublicProfileController::class, 'show'])
    ->where('slug', '[a-z0-9](?:[a-z0-9\-]{0,62}[a-z0-9])?')
    ->name('public.profile');
