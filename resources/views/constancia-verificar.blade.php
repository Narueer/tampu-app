@extends('layouts.app')

@section('titulo', 'Verificar constancia')

@section('contenido')
    <main class="contenedor verificar-doc">
        @php($valida = $datos && $datos['vigente'])
        <div class="verificar-tarjeta {{ $valida ? 'es-valida' : 'no-valida' }}">
            <img src="{{ asset('img/logo-160.png') }}" alt="" class="verificar-agua">

            @if ($datos && $datos['vigente'])
                <span class="verificar-estado"><i class="bi bi-patch-check-fill"></i> Constancia auténtica y vigente</span>
                <h1>{{ $datos['tipo'] }}</h1>
                <p class="texto-suave">TAMPU emitió este documento y sigue vigente. Compara estos datos con el PDF que te mostraron.</p>
            @elseif ($datos)
                <span class="verificar-estado"><i class="bi bi-exclamation-octagon-fill"></i> Constancia anulada</span>
                <h1>Esta constancia ya no es válida</h1>
                <p class="texto-suave">TAMPU la emitió, pero ya no está vigente{{ $datos['motivo'] ? ': ' . $datos['motivo'] : ' (la visita o la postulación fue cancelada).' }}</p>
            @else
                <span class="verificar-estado"><i class="bi bi-x-octagon-fill"></i> No encontrada</span>
                <h1>Este código no existe</h1>
                <p class="texto-suave">No hay ninguna constancia de TAMPU con el código <b class="mono">{{ $codigo }}</b>. Si alguien te mostró un PDF con este código, <b>puede ser falso</b>.</p>
            @endif

            @if ($datos)
                <dl class="verificar-datos">
                    <div><dt>Código único</dt><dd class="mono">{{ $codigo }}</dd></div>
                    <div><dt>Estudiante</dt><dd>{{ $datos['nombre'] }} <small class="texto-suave">· DNI {{ $datos['dni'] }}</small></dd></div>
                    <div><dt>{{ str_starts_with($datos['tipo'], 'Visita') ? 'Habitación' : 'Puesto' }}</dt><dd>{{ $datos['que'] }}</dd></div>
                    <div><dt>{{ str_starts_with($datos['tipo'], 'Visita') ? 'Distrito' : 'Empresa' }}</dt><dd>{{ $datos['donde'] }}</dd></div>
                    <div><dt>Aceptada el</dt><dd>{{ $datos['fecha']->timezone('America/Lima')->format('d/m/Y') }}</dd></div>
                    <div><dt>Emitida el</dt><dd>{{ $datos['emitida']->timezone('America/Lima')->format('d/m/Y H:i') }}</dd></div>
                    @if ($datos['extra'])
                        <div class="ancho"><dt>Detalle</dt><dd>{{ $datos['extra'] }}</dd></div>
                    @endif
                </dl>
                <p class="verificar-nota"><i class="bi bi-shield-lock"></i> Por privacidad no mostramos el DNI completo ni los celulares.</p>
            @endif

            <form class="verificar-otra" action="{{ route('constancia.verificar') }}" method="GET">
                <label for="codigoVerificar">Verificar otro código</label>
                <div>
                    <input id="codigoVerificar" name="codigo" placeholder="TMP-V-K7Q2-9XMF" autocomplete="off" required>
                    <button class="btn" type="submit">Verificar</button>
                </div>
            </form>
        </div>
    </main>
@endsection
