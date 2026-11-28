<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// correos para el equipo de TAMPU (reportes, alertas de seguridad). Va al correo de CONTACTO_CORREO.
class CorreoAdmin
{
    public static function enviar(string $titulo, string $etiqueta, array $filas, ?string $mensaje = null, ?string $url = null, string $boton = 'Ver en el panel'): void
    {
        try {
            Mail::send(['html' => 'emails.admin', 'text' => 'emails.admin-texto'], [
                'titulo' => $titulo,
                'etiqueta' => $etiqueta,
                'resumen' => $titulo . ': ' . implode(' · ', array_slice(array_values($filas), 0, 2)),
                'filas' => $filas,
                'mensaje' => $mensaje,
                'url' => $url,
                'boton' => $boton,
                'porque' => 'Te llega porque eres parte del equipo de TAMPU.',
                'sinFirma' => true,
            ], fn ($m) => $m->to(config('tampu.correo'))->subject('[TAMPU] ' . $titulo));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo al equipo: ' . $e->getMessage());
        }
    }
}
