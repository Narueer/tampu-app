@extends('emails.base')

@section('contenido')
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:15px; line-height:22px; color:#4f5650;">
        @foreach ($filas as $campo => $valor)
            <tr>
                <td style="padding:7px 0; width:120px; color:#7a8079; vertical-align:top; border-bottom:1px solid #f0ebe1;">{{ $campo }}</td>
                <td style="padding:7px 0; color:#343936; font-weight:bold; border-bottom:1px solid #f0ebe1;">{{ $valor }}</td>
            </tr>
        @endforeach
    </table>

    @if ($mensaje)
        @include('emails.partes.nota', ['color' => '#965c57', 'html' => nl2br(e($mensaje))])
    @endif

    @if ($url)
        @include('emails.partes.boton', ['url' => $url, 'texto' => $boton])
    @endif
@endsection
