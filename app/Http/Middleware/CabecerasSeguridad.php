<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// cabeceras que le dicen al navegador como proteger la pagina
class CabecerasSeguridad
{
    public function handle(Request $request, Closure $next)
    {
        $respuesta = $next($request);
        $h = $respuesta->headers;

        // nadie puede meter TAMPU dentro de un iframe (clickjacking)
        $h->set('X-Frame-Options', 'SAMEORIGIN');
        // el navegador no adivina el tipo de archivo
        $h->set('X-Content-Type-Options', 'nosniff');
        // a otros sitios no se les pasa la ruta completa
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // camara y microfono apagados, ubicacion solo para nuestro mapa
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
        $h->remove('X-Powered-By');
        header_remove('X-Powered-By');

        // en la nube siempre por https
        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000');
        }

        // las paginas con datos personales no se guardan en cache compartida
        if ($request->user()) {
            $h->set('Cache-Control', 'no-store, private');
        }

        return $respuesta;
    }
}
