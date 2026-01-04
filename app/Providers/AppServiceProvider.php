<?php

namespace App\Providers;

use App\Auth\EncryptedUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        // Backward-compatible Livewire aliases for renamed/moved components
        Livewire::component('admin.users', \App\Livewire\Admin\Users\Index::class);
        Livewire::component('admin.users-create', \App\Livewire\Admin\Users\Create::class);
        Livewire::component('admin.users-update', \App\Livewire\Admin\Users\Edit::class);

        Livewire::component('admin.divisions', \App\Livewire\Admin\Divisions\Index::class);
        Livewire::component('admin.divisions-create', \App\Livewire\Admin\Divisions\Create::class);
        Livewire::component('admin.division', \App\Livewire\Admin\Divisions\Edit::class);

        Livewire::component('admin.periods', \App\Livewire\Admin\Periods\Index::class);
        Livewire::component('admin.period-detail', \App\Livewire\Admin\Periods\Show::class);
    }
}
