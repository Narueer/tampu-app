<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// correo con el enlace para cambiar la contraseña
class RestablecerClave extends Notification
{
    public function __construct(public string $enlace, public ?string $dispositivo = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cambia tu contraseña de TAMPU')
            ->view(['html' => 'emails.clave', 'text' => 'emails.clave-texto'], [
                'titulo' => 'Crea una nueva contraseña',
                'saludo' => 'Hola, ' . explode(' ', trim($notifiable->nombre))[0],
                'etiqueta' => 'Seguridad',
                'resumen' => 'Usa el enlace para crear una nueva contraseña. Vence en 60 minutos.',
                'enlace' => $this->enlace,
                'cuando' => now('America/Lima')->translatedFormat('l j \d\e F, g:i a'),
                'dispositivo' => $this->dispositivo,
            ]);
    }

    // "Chrome en Windows", "Safari en iPhone"... para que la persona reconozca si fue ella
    public static function describir(?string $agente): ?string
    {
        if (! $agente) {
            return null;
        }
        $navegador = match (true) {
            str_contains($agente, 'Edg/') => 'Edge',
            str_contains($agente, 'OPR/') => 'Opera',
            str_contains($agente, 'Chrome/') => 'Chrome',
            str_contains($agente, 'Firefox/') => 'Firefox',
            str_contains($agente, 'Safari/') => 'Safari',
            default => 'un navegador',
        };
        $sistema = match (true) {
            str_contains($agente, 'iPhone') => 'iPhone',
            str_contains($agente, 'iPad') => 'iPad',
            str_contains($agente, 'Android') => 'Android',
            str_contains($agente, 'Windows') => 'Windows',
            str_contains($agente, 'Mac OS') => 'Mac',
            str_contains($agente, 'Linux') => 'Linux',
            default => null,
        };

        return $sistema ? "$navegador en $sistema" : $navegador;
    }
}
