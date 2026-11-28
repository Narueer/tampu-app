{{--
  Plantilla de todos los correos de TAMPU.
  Los correos no aceptan CSS moderno: todo va en tablas y con estilos en línea,
  para que se vea igual en Gmail, Outlook y el celular.
--}}
@php
    $molle = '#34483e';
    $carbon = '#343936';
    $texto = '#4f5650';
    $suave = '#7a8079';
    $marfil = '#F5F3ED';
    $arena = '#D8CBB5';
    $serif = "Georgia, 'Times New Roman', serif";
    $sans = "'Helvetica Neue', Helvetica, Arial, sans-serif";
    $inicio = rtrim(config('app.url'), '/');
@endphp
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light">
<title>{{ $titulo ?? 'TAMPU' }}</title>
<style>
    a { color: {{ $molle }}; }
    @media only screen and (max-width: 620px) {
        .caja { width: 100% !important; }
        .relleno { padding-left: 24px !important; padding-right: 24px !important; }
        .titulo { font-size: 24px !important; line-height: 30px !important; }
        .boton a { display: block !important; }
        .digito { width: 38px !important; font-size: 26px !important; }
    }
</style>
</head>
<body style="margin:0; padding:0; background:{{ $marfil }}; -webkit-text-size-adjust:100%;">

{{-- texto que Gmail muestra junto al asunto, en la lista de correos --}}
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:{{ $marfil }};">
    {{ $resumen ?? '' }}&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:{{ $marfil }};">
<tr><td align="center" style="padding:28px 12px 36px;">

    <table role="presentation" class="caja" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px;">

        {{-- cabecera --}}
        <tr><td style="background:{{ $molle }}; border-radius:16px 16px 0 0; padding:22px 32px;" class="relleno">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td valign="middle" width="52" style="width:52px;">
                    <a href="{{ $inicio }}" style="text-decoration:none;">
                        <img src="{{ asset('img/logo-160.png') }}" width="46" height="46" alt="TAMPU" style="display:block; border:0; border-radius:50%; background:#ffffff;">
                    </a>
                </td>
                <td valign="middle" style="padding-left:12px;">
                    <a href="{{ $inicio }}" style="text-decoration:none;">
                        <span style="font-family:{{ $serif }}; font-size:24px; font-weight:bold; color:#ffffff; letter-spacing:0.5px;">Tampu</span><br>
                        <span style="font-family:{{ $sans }}; font-size:10px; font-weight:bold; color:{{ $arena }}; letter-spacing:2.5px;">CUSCO · ESTUDIANTES</span>
                    </a>
                </td>
                @isset($etiqueta)
                <td valign="middle" align="right">
                    <span style="font-family:{{ $sans }}; font-size:11px; font-weight:bold; color:{{ $molle }}; background:{{ $arena }}; border-radius:20px; padding:6px 12px; letter-spacing:1px; white-space:nowrap;">{{ mb_strtoupper($etiqueta) }}</span>
                </td>
                @endisset
            </tr>
            </table>
        </td></tr>

        {{-- franja de tejido andino: bloques de color, funciona en todos los correos --}}
        <tr><td style="line-height:0; font-size:0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                @foreach (array_merge(...array_fill(0, 6, ['#b58a4a', '#965c57', '#D8CBB5', '#53677a', '#965c57'])) as $color)
                    <td height="6" style="height:6px; background:{{ $color }}; font-size:0; line-height:0;">&nbsp;</td>
                @endforeach
            </tr></table>
        </td></tr>

        {{-- contenido --}}
        <tr><td style="background:#ffffff; padding:36px 40px 32px;" class="relleno">
            @isset($saludo)
                <p style="margin:0 0 6px; font-family:{{ $sans }}; font-size:15px; color:{{ $suave }};">{{ $saludo }}</p>
            @endisset
            <h1 class="titulo" style="margin:0 0 18px; font-family:{{ $serif }}; font-size:28px; line-height:34px; font-weight:bold; color:{{ $carbon }};">{{ $titulo }}</h1>

            @yield('contenido')

            @unless ($sinFirma ?? false)
            <p style="margin:28px 0 0; font-family:{{ $sans }}; font-size:15px; line-height:22px; color:{{ $texto }};">
                Un abrazo,<br>
                <strong style="color:{{ $carbon }};">El equipo de TAMPU</strong>
            </p>
            @endunless
        </td></tr>

        {{-- pie --}}
        <tr><td style="background:#efeadf; border-radius:0 0 16px 16px; padding:24px 40px 26px; text-align:center;" class="relleno">
            <p style="margin:0 0 10px; font-family:{{ $sans }}; font-size:13px; line-height:20px;">
                <a href="{{ $inicio }}/habitaciones" style="color:{{ $molle }}; font-weight:bold; text-decoration:none;">Cuartos</a>
                <span style="color:{{ $arena }};">&nbsp;·&nbsp;</span>
                <a href="{{ $inicio }}/trabajos" style="color:{{ $molle }}; font-weight:bold; text-decoration:none;">Trabajos</a>
                <span style="color:{{ $arena }};">&nbsp;·&nbsp;</span>
                <a href="{{ $inicio }}/guias/guia-tampu.pdf" style="color:{{ $molle }}; font-weight:bold; text-decoration:none;">Guía de uso</a>
                <span style="color:{{ $arena }};">&nbsp;·&nbsp;</span>
                <a href="{{ $inicio }}/contacto" style="color:{{ $molle }}; font-weight:bold; text-decoration:none;">Ayuda</a>
            </p>
            <p style="margin:0; font-family:{{ $sans }}; font-size:12px; line-height:18px; color:{{ $suave }};">
                {{ $porque ?? 'Recibes este correo porque tienes una cuenta en TAMPU.' }}<br>
                ¿Dudas? Escríbenos a <a href="mailto:{{ config('tampu.correo') }}" style="color:{{ $suave }};">{{ config('tampu.correo') }}</a><br>
                TAMPU · Cusco, Perú · {{ date('Y') }}
            </p>
        </td></tr>

    </table>

</td></tr>
</table>
</body>
</html>
