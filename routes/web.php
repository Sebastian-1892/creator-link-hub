<?php

use App\Http\Controllers\LinkRedirectController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\SitemapController;
use App\Livewire\AnalyticsDashboard;
use App\Livewire\BillingPortal;
use App\Livewire\BioPageEditor;
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
    Route::get('hub/analytics', AnalyticsDashboard::class)->name('analytics');
    Route::get('hub/billing', BillingPortal::class)->name('billing');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';

Route::get('p/{slug}', [PublicProfileController::class, 'show'])
    ->where('slug', '[a-z0-9](?:[a-z0-9\-]{0,62}[a-z0-9])?')
    ->name('public.profile');
