<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; margin: 30px 40px; }
        .cabecera { border-bottom: 2px solid #465548; padding-bottom: 10px; margin-bottom: 25px; }
        .cabecera b { font-size: 22px; color: #465548; }
        .cabecera span { float: right; font-size: 11px; color: #555; margin-top: 8px; }
        h1 { font-size: 17px; text-align: center; margin: 0 0 25px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        td { border: 1px solid #ccc; padding: 7px 10px; }
        td.campo { width: 35%; background: #f3e6d9; font-weight: bold; }
        .nota { font-size: 11px; color: #444; border: 1px dashed #465548; padding: 10px; }
        .pie { position: fixed; bottom: 10px; left: 40px; right: 40px; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="cabecera">
        <b>TAMPU</b> <small>Cuartos y trabajos para estudiantes en Cusco</small>
        <span>Código: {{ $codigo }}</span>
    </div>

    @if ($tipo === 'visita')
        <h1>CONSTANCIA DE VISITA ACEPTADA</h1>
        <p>Se deja constancia de que el arrendador aceptó la siguiente solicitud de visita registrada en TAMPU:</p>
        <table>
            <tr><td class="campo">Estudiante</td><td>{{ $estudiante->nombre }} (DNI {{ $estudiante->dni }})</td></tr>
            <tr><td class="campo">Procedencia</td><td>{{ $estudiante->procedencia ?: '—' }}</td></tr>
            <tr><td class="campo">Habitación</td><td>{{ $habitacion->titulo }}</td></tr>
            <tr><td class="campo">Dirección</td><td>{{ $habitacion->direccion }}, {{ $habitacion->distrito }}</td></tr>
            <tr><td class="campo">Precio mensual</td><td>S/ {{ number_format($habitacion->precio, 2) }}</td></tr>
            <tr><td class="campo">Arrendador</td><td>{{ $contraparte->nombre }} - Cel. {{ $contraparte->celular }}</td></tr>
            <tr><td class="campo">Fecha de visita</td><td>{{ $solicitud->fecha_visita->format('d/m/Y') }}</td></tr>
            <tr><td class="campo">Fecha de aceptación</td><td>{{ $solicitud->updated_at->format('d/m/Y H:i') }}</td></tr>
        </table>
        <p class="nota">Recuerda: no pagues ningún adelanto antes de visitar la habitación. Si el arrendador te pide dinero por adelantado, repórtalo en TAMPU.</p>
    @else
        <h1>CONSTANCIA DE POSTULACIÓN ACEPTADA</h1>
        <p>Se deja constancia de que la empresa aceptó la postulación del estudiante a la siguiente oferta publicada en TAMPU:</p>
        <table>
            <tr><td class="campo">Estudiante</td><td>{{ $estudiante->nombre }} (DNI {{ $estudiante->dni }})</td></tr>
            <tr><td class="campo">Oferta</td><td>{{ $trabajo->titulo }}</td></tr>
            <tr><td class="campo">Empresa</td><td>{{ $trabajo->empresa }}</td></tr>
            <tr><td class="campo">Contacto</td><td>{{ $contraparte->nombre }} - Cel. {{ $contraparte->celular }}</td></tr>
            <tr><td class="campo">Modalidad</td><td>{{ ucfirst($trabajo->modalidad) }}</td></tr>
            <tr><td class="campo">Horario</td><td>{{ $trabajo->horarioTexto() }}</td></tr>
            <tr><td class="campo">Pago</td><td>S/ {{ number_format($trabajo->pago, 2) }} por {{ $trabajo->pago_por === 'dia' ? 'día' : $trabajo->pago_por }}</td></tr>
            <tr><td class="campo">Fecha de aceptación</td><td>{{ $postulacion->updated_at->format('d/m/Y H:i') }}</td></tr>
        </table>
        <p class="nota">Este documento sirve como respaldo de la aceptación dentro de TAMPU. Las condiciones finales del trabajo se acuerdan directamente con la empresa.</p>
    @endif

    <p>Documento generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}.</p>

    <div class="pie">TAMPU - Proyecto del curso Programación Web - Universidad Continental</div>
</body>
</html>
