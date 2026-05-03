<?php

namespace App\Providers;

use App\Listeners\CreateWorkspaceForNewUser;
use App\Models\Profile;
use App\Observers\ProfileObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Profile::observe(ProfileObserver::class);

        Event::listen(Registered::class, CreateWorkspaceForNewUser::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', $request->ip());

            return Limit::perMinute(5)->by(strtolower($email));
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('link-go', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
