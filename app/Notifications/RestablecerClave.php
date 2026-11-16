<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// correo con el enlace para cambiar la contraseña
class RestablecerClave extends Notification
{
    public function __construct(public string $enlace)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('TAMPU - Cambiar tu contraseña')
            ->greeting('Hola, ' . $notifiable->nombre)
            ->line('Recibimos un pedido para cambiar la contraseña de tu cuenta en TAMPU.')
            ->action('Crear nueva contraseña', $this->enlace)
            ->line('El enlace vence en 60 minutos. Si no fuiste tú, puedes ignorar este correo.')
            ->salutation('Equipo TAMPU');
    }
}
