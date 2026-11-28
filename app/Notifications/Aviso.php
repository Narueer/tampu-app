<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class Aviso extends Notification
{
    public function __construct(public string $texto, public string $url, public string $icono = 'bell')
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['texto' => $this->texto, 'url' => $this->url, 'icono' => $this->icono];
    }

    /**
     * Aviso en la campanita y, si la persona lo tiene activado, también por correo.
     * Si el correo falla (servidor caído, sin configurar) no se rompe nada: queda en el log.
     */
    public static function enviar(object $usuario, string $texto, string $url, string $icono, string $asunto, array $lineas, string $boton = 'Ver en TAMPU'): void
    {
        $usuario->notify(new self($texto, $url, $icono));
        if (! ($usuario->avisos_correo ?? true)) {
            return;
        }
        try {
            $usuario->notify(new AvisoCorreo($asunto, $lineas, $url, $boton, $icono));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo enviar el aviso por correo a ' . $usuario->email . ': ' . $e->getMessage());
        }
    }
}
