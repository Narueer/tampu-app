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
            ->subject($this->codigo . ' es tu código de TAMPU')
            ->view(['html' => 'emails.codigo', 'text' => 'emails.codigo-texto'], [
                'titulo' => 'Confirma tu correo',
                'saludo' => 'Hola, ' . explode(' ', trim($notifiable->nombre))[0],
                'etiqueta' => 'Bienvenida',
                'resumen' => 'Tu código es ' . $this->codigo . '. Vence en 15 minutos.',
                'codigo' => $this->codigo,
                'url' => route('correo.verificar'),
            ]);
    }
}
