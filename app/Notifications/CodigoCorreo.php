<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CodigoCorreo extends Notification
{
    public function __construct(public string $codigo)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu código de TAMPU: ' . $this->codigo)
            ->greeting('Hola, ' . $notifiable->nombre)
            ->line('Este es tu código para confirmar tu correo en TAMPU:')
            ->line('**' . $this->codigo . '**')
            ->line('Vence en 15 minutos. Si no creaste una cuenta en TAMPU, ignora este mensaje.');
    }
}
