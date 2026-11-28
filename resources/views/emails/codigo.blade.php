@extends('emails.base')

@section('contenido')
    @include('emails.partes.parrafo', ['html' => 'Gracias por crear tu cuenta. Para terminar, escribe este código en TAMPU y confirma que este correo es tuyo:'])

    {{-- cada número en su casilla, como en la página --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:26px auto 10px;">
    <tr>
        @foreach (str_split($codigo) as $i => $numero)
            <td class="digito" width="48" align="center" style="width:48px; padding:12px 0; background:#F5F3ED; border:2px solid #D8CBB5; border-radius:10px; font-family:'Courier New', Courier, monospace; font-size:30px; font-weight:bold; color:#34483e;">{{ $numero }}</td>
            @if (! $loop->last)
                <td width="{{ $i === 2 ? 18 : 8 }}" style="width:{{ $i === 2 ? 18 : 8 }}px; font-size:0;">&nbsp;</td>
            @endif
        @endforeach
    </tr>
    </table>
    <p style="margin:0 0 8px; text-align:center; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:13px; color:#7a8079;">
        &#9201;&nbsp; Vence en <strong>15 minutos</strong>
    </p>

    @include('emails.partes.boton', ['url' => $url, 'texto' => 'Ir a confirmar mi correo'])

    @include('emails.partes.nota', [
        'color' => '#b58a4a',
        'titulo' => 'Cuida tu código',
        'html' => 'Nadie de TAMPU te lo pedirá por WhatsApp ni por llamada. Si no creaste una cuenta, ignora este correo.',
    ])
@endsection
