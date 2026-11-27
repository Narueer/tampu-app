@extends('layouts.app')

@section('titulo', 'Nosotros')
@section('descripcion', 'Conoce TAMPU, el proyecto de estudiantes de la Universidad Continental que ayuda a quienes llegan a Cusco a encontrar cuarto seguro y trabajo.')

@section('contenido')
    @include('partials.banner', ['titulo' => 'Sobre TAMPU', 'texto' => 'Un lugar de descanso en el camino, como los tampus del Qhapaq Ñan.', 'imagen' => 'img/cusco/catedral.jpg'])

    <main class="contenedor">
        <!-- historia con fotos flotantes -->
        <section class="seccion">
            <div class="row g-4 g-lg-5 align-items-center">
                <div class="col-lg-6 revelar">
                    <span class="sobretitulo">Nuestra historia</span>
                    <h2 class="titulo-grande">¿Por qué se llama TAMPU?</h2>
                    <p>En la época inca, los <b>tampus</b> eran lugares de descanso en el Qhapaq Ñan donde los viajeros encontraban techo y alimento. Cada año llegan a Cusco muchos jóvenes de provincias para estudiar y lo primero que tienen que resolver es dónde vivir y cómo mantenerse.</p>
                    <p class="mt-3">Sin conocer la ciudad, buscan cuartos por Facebook o carteles y quedan expuestos a adelantos que se pierden y a trabajos que chocan con sus clases. TAMPU quiere ser para ellos lo que eran los tampus: un lugar seguro para empezar.</p>
                </div>
                <div class="col-lg-6 revelar">
                    <div class="collage">
                        <img src="{{ asset('img/cusco/plaza.jpg') }}" alt="Plaza de Armas del Cusco" class="collage-grande">
                        <img src="{{ asset('img/habitaciones/h3.jpg') }}" alt="Habitación para estudiantes" class="collage-chica flotar">
                        <div class="globo-flotante globo-1 flotar-lento">
                            <i class="bi bi-mortarboard-fill"></i>
                            <div><b>Hecho por estudiantes</b><small>para estudiantes</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- mision, vision y valores -->
        <section class="seccion pt-0">
            <div class="row g-4">
                <div class="col-md-4 revelar">
                    <div class="tarjeta-valor">
                        <i class="bi bi-bullseye"></i>
                        <h3>Misión</h3>
                        <p>Conectar a estudiantes con habitaciones y trabajos verificados.</p>
                    </div>
                </div>
                <div class="col-md-4 revelar">
                    <div class="tarjeta-valor destacada">
                        <i class="bi bi-eye"></i>
                        <h3>Visión</h3>
                        <p>Que ningún estudiante deje sus estudios por no encontrar dónde vivir.</p>
                    </div>
                </div>
                <div class="col-md-4 revelar">
                    <div class="tarjeta-valor">
                        <i class="bi bi-heart"></i>
                        <h3>Valores</h3>
                        <p>Confianza, respeto por el tiempo de estudio y cero adelantos a ciegas.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- equipo -->
        <section class="seccion pt-0">
            <div class="encabezado-seccion revelar">
                <span class="sobretitulo">Curso Programación Web · Universidad Continental</span>
                <h2>Nuestro equipo</h2>
            </div>
            <div class="row g-4">
                @foreach (['wernher' => 'Wernher Alvares Rodriguez', 'cesar' => 'César Augusto Juro Huillca', 'david' => 'David Achahuanco Bayona', 'olenka' => 'Olenka Valeria Aedo Vargas'] as $foto => $nombre)
                    @php($partes = explode(' ', $nombre))
                    <div class="col-6 col-lg-3 revelar">
                        <div class="tarjeta-equipo">
                            @if (file_exists(public_path("img/equipo/{$foto}.webp")))
                                <img class="avatar-equipo foto" src="{{ asset("img/equipo/{$foto}.webp") }}" alt="Foto de {{ $nombre }}" loading="lazy" width="96" height="96">
                            @else
                                <span class="avatar-equipo">{{ mb_substr($partes[0], 0, 1) }}{{ mb_substr($partes[count($partes) - 2], 0, 1) }}</span>
                            @endif
                            <h3>{{ $nombre }}</h3>
                            <p>Integrante del equipo</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- cómo está hecho: diagramas en public/img/diagramas (se generan con recursos/diagramas.py) --}}
        <section class="seccion pt-0" id="como-esta-hecho">
            <span class="sobretitulo">Para los curiosos</span>
            <h2>Cómo está <em>hecho</em> TAMPU</h2>
            <p class="texto-suave">Laravel 12 con PHP 8.2 en Render, base de datos PostgreSQL en Supabase, mapas y transporte de OpenStreetMap,
                y un asistente que combina reglas propias con IA. Toca un diagrama para verlo en grande.</p>
            <div class="diagramas">
                <figure>
                    <a href="{{ asset('img/diagramas/arquitectura.svg') }}" target="_blank" rel="noopener"><img src="{{ asset('img/diagramas/arquitectura.svg') }}" alt="Diagrama de arquitectura: usuarios, servidor Laravel en Render con rutas, controladores, servicios, modelos y vistas; y los servicios externos Supabase, IA, OpenStreetMap y correo" loading="lazy"></a>
                    <figcaption>Arquitectura</figcaption>
                </figure>
                <figure>
                    <a href="{{ asset('img/diagramas/base-de-datos.svg') }}" target="_blank" rel="noopener"><img src="{{ asset('img/diagramas/base-de-datos.svg') }}" alt="Diagrama de la base de datos: usuarios se relaciona con habitaciones, trabajos, conversaciones, horarios, reportes y eventos de seguridad; habitaciones con fotos, favoritos, solicitudes y reseñas; trabajos con horarios y postulaciones" loading="lazy"></a>
                    <figcaption>Base de datos</figcaption>
                </figure>
            </div>
        </section>
    </main>
@endsection
