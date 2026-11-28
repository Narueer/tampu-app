<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
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

        // correos por la API de Brevo (MAIL_MAILER=brevo), porque Render gratis bloquea SMTP
        Mail::extend('brevo', fn (array $config) => new \App\Mail\BrevoTransport((string) ($config['key'] ?? '')));

        // limites propios para que no se mezclen con los de otras rutas
        RateLimiter::for('dos-pasos', fn (Request $r) => Limit::perMinute(5)->by('dos-pasos|' . $r->session()->get('dos_pasos.id') . '|' . $r->ip()));
        RateLimiter::for('codigo-correo', fn (Request $r) => Limit::perMinute(5)->by('codigo-correo|' . $r->user()?->id));
        RateLimiter::for('enviar-correo', fn (Request $r) => Limit::perMinute(3)->by('enviar-correo|' . $r->user()?->id));
        RateLimiter::for('externo', fn (Request $r) => Limit::perMinute(10)->by('externo|' . $r->ip()));
    }
}
