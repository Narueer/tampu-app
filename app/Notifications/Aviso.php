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
}
