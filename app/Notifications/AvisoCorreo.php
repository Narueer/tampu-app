<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// el mismo aviso de la campanita, pero por correo (solo para lo importante: visitas, postulaciones y verificación)
class AvisoCorreo extends Notification
{
    public function __construct(public string $asunto, public array $lineas, public string $url, public string $boton)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $correo = (new MailMessage)
            ->subject($this->asunto . ' · TAMPU')
            ->greeting('Hola, ' . explode(' ', $notifiable->nombre)[0]);
        foreach ($this->lineas as $linea) {
            $correo->line($linea);
        }

        return $correo->action($this->boton, $this->url)
            ->line('Si no quieres recibir estos correos, desactívalos en Mi perfil → Avisos por correo.')
            ->salutation('El equipo de TAMPU, Cusco');
    }
}
