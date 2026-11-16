@extends('layouts.app')

@section('titulo', 'Inicio')

@section('contenido')
    <!-- portada -->
    <section class="portada">
        <div class="contenedor">
            <div>
                <h1>Llegaste a Cusco.<br><span>No estás solo en el camino.</span></h1>
                <p>Encuentra una habitación segura y un trabajo que encaje con tus clases. Todos los arrendadores y empleadores son verificados.</p>
                <a href="{{ route('habitaciones.index') }}" class="btn">Buscar habitación</a>
                <a href="{{ route('trabajos.index') }}" class="btn btn-borde">Buscar trabajo</a>
            </div>
            <img src="{{ asset('img/logo-160.png') }}" alt="Logo de Tampu">
        </div>
    </section>

    <main>
        <!-- como funciona -->
        <section class="seccion">
            <div class="contenedor">
                <h2>¿Cómo funciona?</h2>
                <div class="tarjetas">
                    <div class="paso">
                        <i class="bi bi-person-plus"></i>
                        <h3>1. Regístrate</h3>
                        <p>Crea tu cuenta e indica de dónde vienes y dónde estudias.</p>
                    </div>
                    <div class="paso">
                        <i class="bi bi-calendar-week"></i>
                        <h3>2. Marca tu horario</h3>
                        <p>Te mostramos solo los trabajos que no chocan con tus clases.</p>
                    </div>
                    <div class="paso">
                        <i class="bi bi-house-check"></i>
                        <h3>3. Solicita una visita</h3>
                        <p>Visita la habitación antes de pagar. Nunca des adelantos sin ver el cuarto.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- destacadas -->
        <section class="seccion fondo-blanco">
            <div class="contenedor">
                <h2>Habitaciones recientes</h2>
                <div class="tarjetas">
                    @foreach ($destacadas as $h)
                        @include('partials.tarjeta-habitacion')
                    @endforeach
                </div>
                <p class="centrado"><a href="{{ route('habitaciones.index') }}" class="btn">Ver todas las habitaciones</a></p>
            </div>
        </section>

        <section class="seccion">
            <div class="contenedor">
                <h2>Trabajos recientes</h2>
                @foreach ($trabajos as $t)
                    @include('partials.trabajo')
                @endforeach
                <p class="centrado"><a href="{{ route('trabajos.index') }}" class="btn">Ver todos los trabajos</a></p>
            </div>
        </section>
    </main>
@endsection
