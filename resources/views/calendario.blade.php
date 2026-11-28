@extends('layouts.app')

@section('titulo', 'Calendario')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Mi calendario</h1>

        <div class="leyenda">
            @if (auth()->user()->rol === 'estudiante')
                <span><i class="punto" style="background:#34483e"></i> Clases</span>
                <span><i class="punto" style="background:#965c57"></i> Turnos de trabajo</span>
                <span><i class="punto" style="background:#2e7d5b"></i> Visita aceptada</span>
                <span><i class="punto" style="background:#c99a2e"></i> Visita pendiente</span>
            @elseif (auth()->user()->rol === 'arrendador')
                <span><i class="punto" style="background:#2e7d5b"></i> Visita aceptada</span>
                <span><i class="punto" style="background:#c99a2e"></i> Visita pendiente</span>
            @else
                <span><i class="punto" style="background:#965c57"></i> Turnos de tus ofertas (entre paréntesis, los estudiantes aceptados)</span>
            @endif
        </div>

        <div class="caja">
            <div id="calendario" data-eventos="{{ route('calendario.eventos') }}"></div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
    <script>
        const divCal = document.getElementById('calendario');
        const enCelular = window.innerWidth < 700;

        const calendario = new FullCalendar.Calendar(divCal, {
            locale: 'es',
            initialView: enCelular ? 'listWeek' : 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: enCelular ? 'listWeek,dayGridMonth' : 'timeGridWeek,dayGridMonth,listWeek'
            },
            slotMinTime: '07:00:00',
            slotMaxTime: '23:00:00',
            allDayText: 'Día',
            height: 'auto',
            events: divCal.dataset.eventos
        });

        calendario.render();
    </script>
@endpush
