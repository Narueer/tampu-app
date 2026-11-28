@extends('layouts.app')

@section('titulo', 'Verificar constancia ' . $codigo)

@section('contenido')
    <main class="contenedor verificar-doc">
        <div class="verificar-tarjeta {{ $datos ? 'es-valida' : 'no-valida' }}">
            <img src="{{ asset('img/logo-160.png') }}" alt="" class="verificar-agua">

            @if ($datos)
                <span class="verificar-estado"><i class="bi bi-patch-check-fill"></i> Constancia auténtica</span>
                <h1>{{ $datos['tipo'] }}</h1>
                <p class="texto-suave">Este documento fue emitido por TAMPU y sigue vigente.</p>

                <dl class="verificar-datos">
                    <div><dt>Código</dt><dd class="mono">{{ $codigo }}</dd></div>
                    <div><dt>Estudiante</dt><dd>{{ $datos['nombre'] }}</dd></div>
                    <div><dt>{{ str_starts_with($datos['tipo'], 'Visita') ? 'Habitación' : 'Puesto' }}</dt><dd>{{ $datos['que'] }}</dd></div>
                    <div><dt>{{ str_starts_with($datos['tipo'], 'Visita') ? 'Distrito' : 'Empresa' }}</dt><dd>{{ $datos['donde'] }}</dd></div>
                    <div><dt>Aceptada el</dt><dd>{{ $datos['fecha']->format('d/m/Y') }}</dd></div>
                    <div><dt>Detalle</dt><dd>{{ $datos['extra'] }}</dd></div>
                </dl>
                <p class="verificar-nota"><i class="bi bi-shield-lock"></i> Por privacidad no mostramos el DNI ni los celulares. Compáralos con el PDF que te mostraron.</p>
            @else
                <span class="verificar-estado"><i class="bi bi-x-octagon-fill"></i> No encontrada</span>
                <h1>No pudimos validar esta constancia</h1>
                <p class="texto-suave">No existe una constancia vigente con el código <b class="mono">{{ $codigo }}</b>. Puede estar mal escrito, o la visita o postulación ya no está aceptada.</p>
            @endif

            <form class="verificar-otra" onsubmit="event.preventDefault(); var c=this.codigo.value.trim(); if(c) location.href='{{ url('verificar') }}/'+encodeURIComponent(c);">
                <label for="codigoVerificar">Verificar otro código</label>
                <div>
                    <input id="codigoVerificar" name="codigo" placeholder="TMP-V-00012" autocomplete="off">
                    <button class="btn" type="submit">Verificar</button>
                </div>
            </form>
        </div>
    </main>
@endsection
