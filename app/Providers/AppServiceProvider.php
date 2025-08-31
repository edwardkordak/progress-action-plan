<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;     // <-- tambahkan ini
// use Illuminate\Support\Facades\Schema; // opsional

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Contoh: paksa HTTPS hanya di production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Schema::defaultStringLength(191); // opsional
    }
}
