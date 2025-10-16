<?php
namespace App\Providers;

use App\Models\Setting;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Window;

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
