<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        // en la nube todos los enlaces van con https
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
