<?php
namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
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
        if (! Schema::hasTable('users')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('db:seed', ['--force' => true]);
            } catch (\Exception $e) {
                \Log::error('Migration/Seeding failed: ' . $e->getMessage());
            }
        }

        if (config('app.env') === 'production' ||
            str_contains(config('app.url'), '127.0.0.1:8100')) {

            $storagePath = base_path('storage/app/public');

            if (! file_exists($storagePath . '/logos')) {
                @mkdir($storagePath . '/logos', 0755, true);
            }

            config(['filesystems.disks.public.root' => $storagePath]);
        }

        config(['database.connections.nativephp.database' => database_path('database.sqlite')]);

        if (getenv('NATIVEPHP') === 'true') {
            if (class_exists(\Native\Laravel\Facades\Window::class)) {
                try {
                    $storeName = Setting::where('key', 'store_name')->value('value') ?? config('app.name');
                    $storeLogo = Setting::where('key', 'store_logo')->value('value');

                    // Override app.name di runtime config (untuk konsistensi)
                    config(['app.name' => $storeName]);

                } catch (\Throwable $e) {
                    \Log::error('Failed to load settings on boot: ' . $e->getMessage());
                }
            }
        }

        if (! Session::has('fresh_start')) {
            Session::flush();                  // Buang semua session
            Session::put('fresh_start', true); // Tandai sudah reset
        }
    }
}
