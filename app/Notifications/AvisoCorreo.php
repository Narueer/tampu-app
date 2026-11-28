<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// el mismo aviso de la campanita, pero por correo (solo para lo importante: visitas, postulaciones y verificación)
class AvisoCorreo extends Notification
{
    public function __construct(public string $asunto, public array $lineas, public string $url, public string $boton, public string $icono = 'bell')
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $etiqueta = match ($this->icono) {
            'house-door' => 'Visitas',
            'briefcase' => 'Trabajos',
            'patch-check' => 'Tu cuenta',
            'calendar-check' => 'Planificador',
            'flag' => 'Reporte',
            'shield-exclamation' => 'Seguridad',
            default => 'Aviso',
        };

        return (new MailMessage)
            ->subject($this->asunto . ' · TAMPU')
            ->view(['html' => 'emails.aviso', 'text' => 'emails.aviso-texto'], [
                'titulo' => $this->asunto,
                'saludo' => 'Hola, ' . explode(' ', trim($notifiable->nombre))[0],
                'etiqueta' => $etiqueta,
                'resumen' => $this->lineas[0] ?? $this->asunto,
                'lineas' => $this->lineas,
                'url' => $this->url,
                'boton' => $this->boton,
                'porque' => 'Te avisamos porque tienes activados los avisos por correo. Puedes apagarlos en Mi perfil.',
            ]);
    }
}
