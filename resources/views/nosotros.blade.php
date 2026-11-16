@extends('layouts.app')

@section('titulo', 'Nosotros')

@section('contenido')
    <main class="contenedor">
        <h1 class="titulo-pagina">Sobre TAMPU</h1>
        <p>En la época inca, los <b>tampus</b> eran lugares de descanso en el Qhapaq Ñan donde los viajeros encontraban techo y alimento. TAMPU quiere ser eso para los estudiantes que llegan a Cusco desde provincias: un lugar seguro para encontrar vivienda y trabajo.</p>

        <section class="seccion">
            <div class="tarjetas">
                <div class="paso">
                    <i class="bi bi-bullseye"></i>
                    <h3>Misión</h3>
                    <p>Conectar a estudiantes con habitaciones y trabajos verificados.</p>
                </div>
                <div class="paso">
                    <i class="bi bi-eye"></i>
                    <h3>Visión</h3>
                    <p>Que ningún estudiante deje sus estudios por no encontrar dónde vivir.</p>
                </div>
                <div class="paso">
                    <i class="bi bi-people"></i>
                    <h3>Equipo</h3>
                    <p>Wernher Alvares Rodriguez, César Augusto Juro Huillca, David Achahuanco Bayona y Olenka Valeria Aedo Vargas.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
