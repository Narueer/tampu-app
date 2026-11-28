@extends('emails.base')

@section('contenido')
    @include('emails.partes.parrafo', ['html' => 'Recibimos un pedido para <strong style="color:#343936;">cambiar la contraseña</strong> de tu cuenta en TAMPU. Para crear una nueva, presiona el botón:'])

    @include('emails.partes.boton', ['url' => $enlace, 'texto' => 'Crear nueva contraseña'])

    <p style="margin:0 0 4px; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:13px; color:#7a8079;">
        &#9201;&nbsp; El enlace vence en <strong>60 minutos</strong> y solo sirve una vez.
    </p>

    @include('emails.partes.nota', [
        'color' => '#53677a',
        'titulo' => 'Datos del pedido',
        'html' => 'Fecha: ' . e($cuando) . '<br>' . ($dispositivo ? 'Desde: ' . e($dispositivo) : ''),
    ])

    @include('emails.partes.nota', [
        'titulo' => '¿No fuiste tú?',
        'html' => 'Ignora este correo: tu contraseña no cambia si no usas el enlace. Si recibes varios sin haberlos pedido, avísanos respondiendo a <a href="mailto:' . e(config('tampu.correo')) . '" style="color:#965c57;">' . e(config('tampu.correo')) . '</a>.',
    ])

    @include('emails.partes.enlace', ['url' => $enlace])
@endsection
