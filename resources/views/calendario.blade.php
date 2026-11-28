@extends('layouts.app')

@php($esEstudiante = auth()->user()->rol === 'estudiante')

@section('titulo', $esEstudiante ? 'Mi planificador' : 'Calendario')

@section('contenido')
    <main class="contenedor {{ $esEstudiante ? 'planificador' : '' }}">
        @include('partials.subnav')

        @if ($esEstudiante)
            <div class="plan-cabecera">
                <div>
                    <span class="sobretitulo">Tu semana en orden</span>
                    <h1 class="titulo-pagina">Mi <em>planificador</em></h1>
                    <p class="texto-suave">Tus clases, turnos y visitas ya están aquí. Anota tus exámenes, entregas y pagos para que nada te tome por sorpresa.</p>
                </div>
                <a href="{{ route('planificador.exportar') }}" class="btn btn-borde plan-exportar"><i class="bi bi-phone"></i> Llevar a mi celular</a>
            </div>

            <div class="plan-grid">
                <aside class="plan-lado">
                    {{-- agregar rápido --}}
                    <div class="caja plan-bloque">
                        <h2 class="plan-titulo">Agregar</h2>
                        <div class="plan-rapidos">
                            @foreach ($tipos as $clave => [$nombre, $icono, $color])
                                <button type="button" class="plan-rapido" data-nuevo="{{ $clave }}" style="--c: {{ $color }}">
                                    <i class="bi {{ $icono }}"></i><span>{{ $clave === 'entrega' ? 'Entrega' : $nombre }}</span>
                                </button>
                            @endforeach
                        </div>
                        <p class="plan-pista"><i class="bi bi-hand-index"></i> También puedes tocar o arrastrar sobre el calendario.</p>
                    </div>

                    {{-- resumen de la semana --}}
                    <div class="plan-resumen" id="planResumen" hidden>
                        <div><b data-r="examenes">0</b><span>exámenes</span></div>
                        <div><b data-r="entregas">0</b><span>entregas</span></div>
                        <div><b data-r="pagos">S/ 0</b><span>en pagos</span></div>
                        <small>en los próximos 7 días</small>
                    </div>

                    {{-- pago del alquiler sacado de "Mi presupuesto" --}}
                    @if ($cuarto)
                        <div class="caja plan-bloque plan-alquiler" id="planAlquiler">
                            <i class="bi bi-house-heart"></i>
                            <div>
                                <b>¿Anotamos el pago de tu cuarto?</b>
                                <p>En tu presupuesto pusiste <b>S/ {{ number_format($cuarto, 0) }}</b> al mes. Te lo recordamos cada mes.</p>
                                <div class="plan-alquiler-form">
                                    <label>El día <input type="number" id="diaAlquiler" min="1" max="31" value="5"> de cada mes</label>
                                    <button type="button" class="btn btn-chico" id="btnAlquiler" data-monto="{{ $cuarto }}">Agregar</button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- lo que viene --}}
                    <div class="caja plan-bloque">
                        <h2 class="plan-titulo">Lo que viene <small>14 días</small></h2>
                        <ul class="plan-lista" id="planLista" aria-live="polite">
                            <li class="plan-cargando">Cargando…</li>
                        </ul>
                    </div>

                    {{-- qué se ve --}}
                    <div class="caja plan-bloque">
                        <h2 class="plan-titulo">Mostrar</h2>
                        <div class="plan-filtros" id="planFiltros">
                            <label><input type="checkbox" value="clases" checked><i style="background:#34483e"></i> Clases</label>
                            <label><input type="checkbox" value="trabajo" checked><i style="background:#a66d53"></i> Turnos de trabajo</label>
                            <label><input type="checkbox" value="visita" checked><i style="background:#465548"></i> Visitas</label>
                            <label><input type="checkbox" value="actividad" checked><i class="arcoiris"></i> Mis actividades</label>
                            <label><input type="checkbox" value="libre" checked><i class="rayado"></i> Turnos libres para estudiar</label>
                        </div>
                    </div>
                </aside>

                <div class="caja plan-calendario">
                    <div id="calendario" data-eventos="{{ route('calendario.eventos') }}"></div>
                </div>
            </div>

            {{-- formulario para agregar o editar --}}
            <div class="modal fade" id="modalActividad" tabindex="-1" aria-labelledby="modalTitulo" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content plan-modal" id="formActividad" novalidate>
                        <div class="modal-header">
                            <h2 class="modal-title" id="modalTitulo">Nueva actividad</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="plan-tipos" role="radiogroup" aria-label="Tipo">
                                @foreach ($tipos as $clave => [$nombre, $icono, $color])
                                    <label style="--c: {{ $color }}">
                                        <input type="radio" name="tipo" value="{{ $clave }}" @checked($loop->first)>
                                        <span><i class="bi {{ $icono }}"></i> {{ $clave === 'entrega' ? 'Entrega' : $nombre }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <label for="aTitulo">¿Qué es?</label>
                            <input type="text" id="aTitulo" name="titulo" maxlength="80" required placeholder="Ej: Parcial de Cálculo I">

                            <div class="plan-fila">
                                <div>
                                    <label for="aFecha">Día</label>
                                    <input type="date" id="aFecha" name="fecha" required>
                                </div>
                                <div class="plan-dia-entero">
                                    <label class="checkbox"><input type="checkbox" id="aTodoDia"> Todo el día</label>
                                </div>
                            </div>
                            <div class="plan-fila" id="filaHoras">
                                <div><label for="aInicio">Desde</label><input type="time" id="aInicio" name="hora_inicio" step="900"></div>
                                <div><label for="aFin">Hasta</label><input type="time" id="aFin" name="hora_fin" step="900"></div>
                            </div>

                            <div class="plan-fila" id="filaMonto">
                                <div><label for="aMonto">Monto (S/)</label><input type="number" id="aMonto" name="monto" min="0" step="0.5" placeholder="330"></div>
                            </div>

                            <div class="plan-fila">
                                <div>
                                    <label for="aRepetir">Se repite</label>
                                    <select id="aRepetir" name="repetir">
                                        <option value="no">No</option>
                                        <option value="semanal">Cada semana</option>
                                        <option value="mensual">Cada mes</option>
                                    </select>
                                </div>
                                <div id="filaHasta" hidden><label for="aHasta">Hasta</label><input type="date" id="aHasta" name="hasta"></div>
                            </div>

                            <label for="aNota">Nota <small class="texto-suave">(opcional)</small></label>
                            <textarea id="aNota" name="nota" rows="2" maxlength="300" placeholder="Temas, aula, a quién pagar…"></textarea>

                            <p class="error" id="aError" role="alert"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-borde btn-peligro me-auto" id="aBorrar" hidden><i class="bi bi-trash"></i> Borrar</button>
                            <button type="button" class="btn btn-borde" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn" id="aGuardar">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <h1 class="titulo-pagina">Mi calendario</h1>

            <div class="leyenda">
                @if (auth()->user()->rol === 'arrendador')
                    <span><i class="punto" style="background:#465548"></i> Visita aceptada</span>
                    <span><i class="punto" style="background:#b58a4a"></i> Visita pendiente</span>
                @else
                    <span><i class="punto" style="background:#965c57"></i> Turnos de tus ofertas (entre paréntesis, los estudiantes aceptados)</span>
                @endif
            </div>

            <div class="caja">
                <div id="calendario" data-eventos="{{ route('calendario.eventos') }}"></div>
            </div>
        @endif
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
    @if ($esEstudiante)
        <script>
            window.TAMPU_PLAN = {
                rutas: {
                    guardar: @json(route('planificador.guardar')),
                    proximas: @json(route('planificador.proximas')),
                    base: @json(url('planificador')),
                },
                tipos: @json($tipos),
            };
        </script>
        <script src="{{ asset('js/planificador.js') }}?v={{ filemtime(public_path('js/planificador.js')) }}"></script>
    @else
        <script>
            const divCal = document.getElementById('calendario');
            const enCelular = window.innerWidth < 700;
            new FullCalendar.Calendar(divCal, {
                locale: 'es',
                initialView: enCelular ? 'listWeek' : 'timeGridWeek',
                headerToolbar: { left: 'prev,next today', center: 'title', right: enCelular ? 'listWeek,dayGridMonth' : 'timeGridWeek,dayGridMonth,listWeek' },
                slotMinTime: '07:00:00', slotMaxTime: '23:00:00', allDayText: 'Día', height: 'auto',
                events: divCal.dataset.eventos
            }).render();
        </script>
    @endif
@endpush
