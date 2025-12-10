<?php

namespace App\Providers;

use App\Auth\EncryptedUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom auth provider for encrypted passwords
        Auth::provider('encrypted', function ($app, array $config) {
            return new EncryptedUserProvider($app['hash'], $config['model']);
        });

        // Force HTTPS in Production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
