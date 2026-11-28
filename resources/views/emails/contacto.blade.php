@extends('emails.base')

@section('contenido')
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:15px; line-height:22px; color:#4f5650;">
        <tr><td style="padding:6px 0; width:90px; color:#7a8079;">Nombre</td><td style="padding:6px 0; color:#343936; font-weight:bold;">{{ $contacto->nombre }}</td></tr>
        <tr><td style="padding:6px 0; color:#7a8079;">Correo</td><td style="padding:6px 0;"><a href="mailto:{{ $contacto->email }}" style="color:#53677a;">{{ $contacto->email }}</a></td></tr>
        <tr><td style="padding:6px 0; color:#7a8079;">Asunto</td><td style="padding:6px 0; color:#343936;">{{ $contacto->asunto }}</td></tr>
    </table>

    @include('emails.partes.nota', ['color' => '#34483e', 'titulo' => 'Mensaje', 'html' => nl2br(e($contacto->mensaje))])

    @include('emails.partes.parrafo', ['html' => '<span style="display:block; margin-top:18px; font-size:14px;">Para contestar, solo responde este correo: la respuesta le llega directo a ' . e($contacto->nombre) . '.</span>'])

    @include('emails.partes.boton', ['url' => route('admin.contactos'), 'texto' => 'Ver en el panel'])
@endsection
