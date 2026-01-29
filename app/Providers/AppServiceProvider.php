<?php

namespace App\Providers;

use App\Models\Owner;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\SystemSettings;
use App\Observers\ReservationObserver;
use App\Policies\OwnerPolicy;
use App\Policies\RoomPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SystemSettings::class, function () {
            // Don't query database if not yet configured
            if (! filter_var(env('DB_CONFIGURED', false), FILTER_VALIDATE_BOOLEAN)) {
                return null;
            }

            return SystemSettings::first();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Owner::class, OwnerPolicy::class);

        // Register observers
        Reservation::observe(ReservationObserver::class);

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            // Use our custom OIDC provider instead of the outdated package
            $event->extendSocialite('nextcloud', \App\Services\Auth\OidcProvider::class);
        });

        // Force HTTPS URLs when:
        // 1. APP_URL explicitly uses https, OR
        // 2. Current request is secure (user accessed via HTTPS, even before APP_URL is configured)
        if (str_starts_with(config('app.url') ?? '', 'https://') || request()->secure() || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }
    }
}
