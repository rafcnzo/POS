<?php
namespace App\Providers;

use App\Models\Setting;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Window;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Process;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $storeName = Setting::where('key', 'store_name')->value('value') ?? config('app.name') ?? 'Point of Sale Restoran';

        Window::open()
            ->title($storeName)
            ->width(1200)
            ->height(800);

        if (app()->runningInConsole() === false) {
            $this->startQueueWorker();
        }
    }

    protected function startQueueWorker(): void
    {
        // Jalankan queue worker sebagai background process
        if (PHP_OS_FAMILY === 'Windows') {
            Process::run('start /B php artisan queue:work --tries=3 --daemon');
        } else {
            Process::run('php artisan queue:work --tries=3 --daemon > /dev/null 2>&1 &');
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
