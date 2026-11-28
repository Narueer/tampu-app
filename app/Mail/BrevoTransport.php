<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

// Envía los correos por la API de Brevo (HTTPS, puerto 443).
// Render gratis bloquea los puertos SMTP (25, 465 y 587), así que Gmail por SMTP no sale desde la nube;
// por API sí. Brevo es gratis hasta 300 correos al día y basta con verificar un correo remitente.
class BrevoTransport extends AbstractTransport
{
    public function __construct(private string $clave)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $mensaje): void
    {
        $correo = MessageConverter::toEmail($mensaje->getOriginalMessage());
        $persona = fn (Address $a) => array_filter(['email' => $a->getAddress(), 'name' => $a->getName() ?: null]);
        $remitente = $correo->getFrom()[0] ?? null;

        $datos = array_filter([
            'sender' => $remitente ? $persona($remitente) : null,
            'to' => array_map($persona, $correo->getTo()),
            'cc' => array_map($persona, $correo->getCc()) ?: null,
            'bcc' => array_map($persona, $correo->getBcc()) ?: null,
            'replyTo' => ($r = $correo->getReplyTo()[0] ?? null) ? $persona($r) : null,
            'subject' => $correo->getSubject(),
            'htmlContent' => $correo->getHtmlBody(),
            'textContent' => $correo->getTextBody(),
        ]);

        $respuesta = Http::withHeaders(['api-key' => $this->clave, 'accept' => 'application/json'])
            ->timeout(15)
            ->post('https://api.brevo.com/v3/smtp/email', $datos);

        if ($respuesta->failed()) {
            throw new TransportException('Brevo no aceptó el correo: ' . $respuesta->status() . ' ' . $respuesta->body());
        }
    }

    public function __toString(): string
    {
        return 'brevo+api://default';
    }
}
