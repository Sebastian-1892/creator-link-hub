<?php

namespace App\Providers;

use App\Listeners\CreateWorkspaceForNewUser;
use App\Models\Link;
use App\Models\Profile;
use App\Observers\LinkObserver;
use App\Observers\ProfileObserver;
use App\Services\BrandingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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
        Link::observe(LinkObserver::class);

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

        View::composer(['layouts.marketing', 'layouts.public', 'marketing.*', 'public.*'], function ($view): void {
            $view->with('branding', app(BrandingService::class)->payload());
        });

        Blade::directive('brandText', function (?string $expression): string {
            $tail = is_string($expression) ? $expression : '';

            return "<?php echo e(brand{$tail}); ?>";
        });
    }
}
