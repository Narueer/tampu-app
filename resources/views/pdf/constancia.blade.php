@php
    // dompdf no entiende flex ni grid: todo va con tablas y posiciones fijas
    $esVisita = $tipo === 'visita';
    $logo = public_path('img/logo-160.png');
    $molle = '#34483e';
    $aceptada = $esVisita ? $solicitud->updated_at : $postulacion->updated_at;
    $verificar = route('constancia.verificar', $codigo);
    $tejido = ['#b58a4a', '#965c57', '#D8CBB5', '#53677a', '#965c57', '#465548'];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Constancia {{ $codigo }}</title>
<style>
    @page { margin: 0; }
    body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #343936; line-height: 1.5; }
    .serif { font-family: 'DejaVu Serif', serif; }

    /* marca de agua: el logo grande y casi transparente en el centro de la hoja */
    .agua { position: fixed; top: 300px; left: 178px; width: 440px; height: 440px; opacity: 0.06; z-index: -1; }
    .agua-texto { position: fixed; top: 760px; left: 0; right: 0; text-align: center; font-family: 'DejaVu Serif', serif; font-size: 60px; font-weight: bold; color: #465548; opacity: 0.05; letter-spacing: 18px; z-index: -1; }

    /* cabecera */
    .cabecera { background: {{ $molle }}; color: #fff; padding: 26px 48px 22px; }
    .cabecera td { vertical-align: middle; }
    .cab-logo { width: 54px; height: 54px; background: #fff; border-radius: 27px; }
    .marca { font-family: 'DejaVu Serif', serif; font-size: 24px; font-weight: bold; color: #fff; line-height: 1; }
    .lema { font-size: 7.5px; letter-spacing: 2.5px; color: #D8CBB5; font-weight: bold; }
    .codigo-caja { text-align: right; }
    .codigo-caja .etq { font-size: 7.5px; letter-spacing: 2px; color: #D8CBB5; font-weight: bold; }
    .codigo-caja .num { font-family: 'DejaVu Sans Mono', monospace; font-size: 15px; font-weight: bold; color: #fff; }
    .codigo-caja .fecha { font-size: 8.5px; color: #c9d0c6; }
    .tejido td { height: 7px; font-size: 0; line-height: 0; }

    .hoja { padding: 30px 48px 0; }
    .tipo { font-size: 8px; letter-spacing: 3px; font-weight: bold; color: #965c57; }
    h1 { font-family: 'DejaVu Serif', serif; font-size: 25px; line-height: 1.15; margin: 4px 0 8px; color: #343936; }
    h1 em { color: #965c57; font-style: italic; font-weight: normal; }
    .intro { font-size: 11px; color: #4f5650; margin: 0 0 20px; width: 470px; }

    /* sello */
    .sello { position: absolute; top: 158px; right: 52px; width: 104px; height: 104px; border: 3px solid #965c57; border-radius: 55px; text-align: center; color: #965c57; }
    .sello-interno { margin: 5px; height: 88px; border: 1px dashed #965c57; border-radius: 47px; }
    .sello .s1 { font-size: 7px; letter-spacing: 1.5px; font-weight: bold; padding-top: 20px; }
    .sello .s2 { font-family: 'DejaVu Serif', serif; font-size: 12px; font-weight: bold; letter-spacing: 1px; }
    .sello .s3 { font-size: 7px; }

    /* las dos partes */
    .partes { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 18px; }
    .parte { width: 50%; vertical-align: top; background: #F5F3ED; border: 1px solid #e3dccd; border-radius: 10px; padding: 12px 14px; }
    .parte-sep { width: 12px; }
    .parte .rol { font-size: 7.5px; letter-spacing: 2px; font-weight: bold; color: #7a8079; }
    .parte .nombre { font-family: 'DejaVu Serif', serif; font-size: 14px; font-weight: bold; color: #343936; margin: 2px 0 4px; }
    .parte .dato { font-size: 9.5px; color: #4f5650; }
    .barra-color { height: 3px; border-radius: 2px; margin-bottom: 8px; }

    /* detalle */
    .seccion { font-size: 8px; letter-spacing: 2.5px; font-weight: bold; color: #465548; margin: 0 0 6px; }
    .detalle { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .detalle td { padding: 8px 12px; border-bottom: 1px solid #ebe5d9; font-size: 10.5px; }
    .detalle tr.par td { background: #faf8f3; }
    .detalle td.campo { width: 34%; color: #7a8079; font-size: 9.5px; }
    .detalle td.valor { color: #343936; font-weight: bold; }
    .precio { font-family: 'DejaVu Serif', serif; font-size: 14px; color: #465548; }

    /* consejo */
    .consejo { background: #f3ede0; border-left: 4px solid #b58a4a; border-radius: 0 10px 10px 0; padding: 10px 14px; margin-bottom: 16px; }
    .consejo b { color: #86652f; }

    /* qué sigue */
    .sigue { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 20px; }
    .sigue td { width: 33%; vertical-align: top; padding: 0 10px 0 0; }
    .sigue .n { width: 22px; height: 18px; padding-top: 4px; line-height: 14px; border-radius: 11px; background: #465548; color: #fff; text-align: center; font-weight: bold; font-size: 10px; margin-bottom: 6px; }
    .sigue b { display: block; font-size: 10.5px; color: #343936; }
    .sigue span.t { font-size: 9.5px; color: #4f5650; }

    /* verificación */
    .verifica { width: 100%; border: 1px solid #d8cbb5; border-radius: 10px; }
    .verifica td { padding: 10px 14px; vertical-align: middle; font-size: 9px; color: #4f5650; }
    .verifica .icono { width: 30px; font-family: 'DejaVu Sans', sans-serif; font-size: 20px; color: #465548; }
    .verifica .url { font-family: 'DejaVu Sans Mono', monospace; font-size: 9px; color: #53677a; }
    .firma { text-align: right; width: 190px; }
    .firma .linea { border-top: 1px solid #343936; padding-top: 4px; font-size: 8.5px; color: #4f5650; }
    .firma .nombre-firma { font-family: 'DejaVu Serif', serif; font-style: italic; font-size: 15px; color: #465548; }

    /* pie */
    .pie { position: fixed; bottom: 0; left: 0; right: 0; }
    .pie-texto { background: #efeadf; padding: 9px 48px; font-size: 8px; color: #7a8079; }
    .pie-texto td { font-size: 8px; color: #7a8079; }
</style>
</head>
<body>
    <img src="{{ $logo }}" class="agua" alt="">
    <div class="agua-texto">TAMPU</div>

    {{-- pie fijo (va antes para que dompdf lo repita) --}}
    <div class="pie">
        <table width="100%" cellpadding="0" cellspacing="0" class="tejido"><tr>
            @foreach (array_merge(...array_fill(0, 8, $tejido)) as $color)<td style="background: {{ $color }};"></td>@endforeach
        </tr></table>
        <div class="pie-texto">
            <table width="100%" cellpadding="0" cellspacing="0"><tr>
                <td>{{ config('tampu.pie_documentos') }}</td>
                <td style="text-align: right;">{{ parse_url(config('app.url'), PHP_URL_HOST) }} · {{ config('tampu.correo') }}</td>
            </tr></table>
        </div>
    </div>

    {{-- cabecera --}}
    <div class="cabecera">
        <table width="100%" cellpadding="0" cellspacing="0"><tr>
            <td width="62"><img src="{{ $logo }}" class="cab-logo" alt=""></td>
            <td>
                <div class="marca">Tampu</div>
                <div class="lema">CUSCO · ESTUDIANTES</div>
            </td>
            <td class="codigo-caja">
                <div class="etq">CONSTANCIA N.º</div>
                <div class="num">{{ $codigo }}</div>
                <div class="fecha">Emitida el {{ now('America/Lima')->translatedFormat('j \d\e F \d\e Y') }}</div>
            </td>
        </tr></table>
    </div>
    <table width="100%" cellpadding="0" cellspacing="0" class="tejido"><tr>
        @foreach (array_merge(...array_fill(0, 8, $tejido)) as $color)<td style="background: {{ $color }};"></td>@endforeach
    </tr></table>

    {{-- sello --}}
    <div class="sello">
        <div class="sello-interno">
            <div class="s1">{{ $esVisita ? 'VISITA' : 'POSTULACIÓN' }}</div>
            <div class="s2">ACEPTADA</div>
            <div class="s3">{{ $aceptada->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="hoja">
        <div class="tipo">{{ $esVisita ? 'CONSTANCIA DE VISITA' : 'CONSTANCIA DE POSTULACIÓN' }}</div>
        @if ($esVisita)
            <h1>Visita <em>aceptada</em></h1>
            <p class="intro">Se deja constancia de que el arrendador <b>aceptó</b> la siguiente solicitud de visita, registrada en la plataforma TAMPU.</p>
        @else
            <h1>Postulación <em>aceptada</em></h1>
            <p class="intro">Se deja constancia de que la empresa <b>aceptó</b> la postulación del estudiante a la siguiente oferta publicada en TAMPU.</p>
        @endif

        {{-- quiénes --}}
        <table class="partes" cellpadding="0" cellspacing="0"><tr>
            <td class="parte">
                <div class="barra-color" style="background: #465548;"></div>
                <div class="rol">ESTUDIANTE</div>
                <div class="nombre">{{ $estudiante->nombre }}</div>
                <div class="dato">DNI {{ $estudiante->dni }}</div>
                <div class="dato">Procedencia: {{ $estudiante->procedencia ?: '—' }}</div>
            </td>
            <td class="parte-sep"></td>
            <td class="parte">
                <div class="barra-color" style="background: #965c57;"></div>
                <div class="rol">{{ $esVisita ? 'ARRENDADOR' : 'EMPRESA' }}</div>
                <div class="nombre">{{ $esVisita ? $contraparte->nombre : $trabajo->empresa }}</div>
                @unless ($esVisita)
                    <div class="dato">Contacto: {{ $contraparte->nombre }}</div>
                @endunless
                <div class="dato">Celular: {{ $contraparte->celular ?: '—' }}</div>
            </td>
        </tr></table>

        {{-- detalle --}}
        <div class="seccion">{{ $esVisita ? 'DATOS DE LA HABITACIÓN' : 'DATOS DE LA OFERTA' }}</div>
        <table class="detalle" cellpadding="0" cellspacing="0">
            @if ($esVisita)
                <tr><td class="campo">Habitación</td><td class="valor">{{ $habitacion->titulo }}</td></tr>
                <tr class="par"><td class="campo">Dirección</td><td class="valor">{{ $habitacion->direccion }}, {{ $habitacion->distrito }}</td></tr>
                <tr><td class="campo">Precio mensual</td><td class="valor"><span class="precio">S/ {{ number_format($habitacion->precio, 2) }}</span></td></tr>
                <tr class="par"><td class="campo">Fecha de la visita</td><td class="valor">{{ ucfirst($solicitud->fecha_visita->translatedFormat('l j \d\e F \d\e Y')) }}</td></tr>
                <tr><td class="campo">Aceptada el</td><td class="valor">{{ $aceptada->format('d/m/Y') }} a las {{ $aceptada->format('H:i') }}</td></tr>
            @else
                <tr><td class="campo">Puesto</td><td class="valor">{{ $trabajo->titulo }}</td></tr>
                <tr class="par"><td class="campo">Modalidad</td><td class="valor">{{ ucfirst($trabajo->modalidad) }}</td></tr>
                <tr><td class="campo">Horario</td><td class="valor">{{ $trabajo->horarioTexto() }}</td></tr>
                <tr class="par"><td class="campo">Pago</td><td class="valor"><span class="precio">S/ {{ number_format($trabajo->pago, 2) }}</span> por {{ $trabajo->pago_por === 'dia' ? 'día' : $trabajo->pago_por }}</td></tr>
                <tr><td class="campo">Aceptada el</td><td class="valor">{{ $aceptada->format('d/m/Y') }} a las {{ $aceptada->format('H:i') }}</td></tr>
            @endif
        </table>

        <div class="consejo">
            @if ($esVisita)
                <b>Importante:</b> no pagues ningún adelanto antes de visitar la habitación. Si te piden dinero por adelantado, repórtalo en TAMPU.
            @else
                <b>Importante:</b> este documento respalda la aceptación dentro de TAMPU. Las condiciones finales del trabajo se acuerdan directamente con la empresa.
            @endif
        </div>

        <div class="seccion">QUÉ SIGUE</div>
        <table class="sigue" cellpadding="0" cellspacing="0"><tr>
            @if ($esVisita)
                <td><div class="n">1</div><b>Confirma la hora</b><span class="t">Escribe al arrendador por celular y acuerden la hora exacta.</span></td><td><div class="n">2</div><b>Lleva tu DNI</b><span class="t">Y si puedes, ve acompañado. Revisa luz, agua, baño y seguridad.</span></td><td><div class="n">3</div><b>Paga solo después</b><span class="t">Si todo está bien, firma un contrato y pide recibo por cada pago.</span></td>
            @else
                <td><div class="n">1</div><b>Revisa tus mensajes</b><span class="t">La empresa te escribirá por el chat de TAMPU para coordinar.</span></td><td><div class="n">2</div><b>Acuerda las condiciones</b><span class="t">Horario, pago y tareas, de preferencia por escrito.</span></td><td><div class="n">3</div><b>Cuida tus clases</b><span class="t">Tu horario de estudio va primero. TAMPU solo te muestra turnos compatibles.</span></td>
            @endif
        </tr></table>

        {{-- verificación y firma --}}
        <table width="100%" cellpadding="0" cellspacing="0"><tr>
            <td style="vertical-align: bottom; padding-right: 20px;">
                <table class="verifica" cellpadding="0" cellspacing="0"><tr>
                    <td class="icono">&#10003;</td>
                    <td>
                        <b style="color: #343936;">¿Es auténtica?</b> Cualquiera puede comprobarla en:<br>
                        <span class="url">{{ $verificar }}</span>
                    </td>
                </tr></table>
            </td>
            <td class="firma" style="vertical-align: bottom;">
                <div class="nombre-firma">Equipo TAMPU</div>
                <div class="linea">Plataforma TAMPU · Cusco</div>
            </td>
        </tr></table>
    </div>
</body>
</html>
