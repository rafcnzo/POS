<?php
namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

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
        if (! Schema::hasTable('users')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('db:seed', ['--force' => true]);
            } catch (\Exception $e) {
                \Log::error('Migration/Seeding failed: ' . $e->getMessage());
            }
        }
        if (! Session::has('fresh_start')) {
            Session::flush();                  // Buang semua session
            Session::put('fresh_start', true); // Tandai sudah reset
        }

        Paginator::defaultView('layouts.pagination');
        Paginator::defaultSimpleView('layouts.pagination');

        RedirectIfAuthenticated::redirectUsing(function ($request) {
            
            // Cek jika user sudah login
            if (Auth::check()) {
                $user = Auth::user();
                $url  = '';

                if ($user->hasRole('Super Admin')) {
                    $url = route('admin.index');
                } elseif ($user->hasRole('Accounting')) {
                    $url = route('acc.suppliers.index');
                } elseif ($user->hasRole('HeadBar')) {
                    $url = route('kitchen.menu.index');
                } elseif ($user->hasRole('HeadKitchen')) {
                    $url = route('kitchen.menu.index');
                } elseif ($user->hasRole('Cashier')) {
                    $url = route('cashier.index');
                } else {
                    // Fallback jika role tidak terdaftar
                    $url = '/dashboard'; // Pastikan Anda punya route ini
                }
                
                return $url;
            }

            return ''; 
        });
    }
}
