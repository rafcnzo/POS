<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use App\Models\Setting; 
use Illuminate\Support\Facades\Cache;

class GlobalViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('app', function ($view) {
            $globalAuthPassword = Cache::remember('global_auth_password', 3600, function () {
                return Setting::where('key', 'authorization_password')->value('value');
            });

            $view->with('globalAuthPassword', $globalAuthPassword);
        });
    }
}
