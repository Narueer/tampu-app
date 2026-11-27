@extends('layouts.app')

@section('titulo', 'Mi panel')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        @include('partials.bienvenida', [
            'subtitulo' => 'Aquí ves tus visitas, tus postulaciones y tu horario de clases.',
            'acciones' => [[route('habitaciones.index'), 'search', 'Buscar cuarto'], [route('trabajos.index', ['compatible' => 1]), 'briefcase', 'Trabajos compatibles']],
        ])

        @include('partials.kpis', ['kpis' => [
            ['house-door', $solicitudes->count(), 'Visitas solicitadas', 'terracota'],
            ['check2-circle', $solicitudes->where('estado', 'aceptada')->count(), 'Visitas aceptadas', 'verde'],
            ['person-workspace', $postulaciones->count(), 'Postulaciones', 'azul'],
            ['heart', auth()->user()->favoritos()->count(), 'Favoritos', 'rosa', route('estudiante.favoritos')],
        ]])

        <div class="caja">
            <h2><i class="bi bi-calendar-week"></i> Mi horario de clases</h2>
            <p class="texto-suave">Marca los turnos en los que tienes clases. En Trabajos podrás ver solo las ofertas que no se cruzan con ellos.</p>
            <form action="{{ route('estudiante.horario') }}" method="POST">
                @csrf
                @include('partials.horario', ['marcados' => $marcados])
                <button type="submit" class="btn">Guardar horario</button>
                <a href="{{ route('trabajos.index', ['compatible' => 1]) }}" class="btn btn-borde">Ver trabajos compatibles</a>
                <a href="{{ route('calendario') }}" class="btn btn-borde"><i class="bi bi-calendar3"></i> Ver en el calendario</a>
            </form>
        </div>

        <div class="caja" id="misVisitas">
            <h2><i class="bi bi-house-door"></i> Mis solicitudes de visita</h2>
            @if ($solicitudes->isEmpty())
                <p class="vacio">Aún no solicitaste visitas. <a href="{{ route('habitaciones.index') }}">Busca una habitación</a>.</p>
            @else
                <div class="tabla-scroll">
                    <table class="tabla">
                        <tr>
                            <th>Habitación</th>
                            <th>Fecha de visita</th>
                            <th>Estado</th>
                            <th>Contacto</th>
                            <th>Opciones</th>
                        </tr>
                        @foreach ($solicitudes as $s)
                            <tr>
                                <td><a href="{{ route('habitaciones.show', $s->habitacion) }}">{{ $s->habitacion->titulo }}</a></td>
                                <td>{{ $s->fecha_visita->format('d/m/Y') }}</td>
                                <td>@include('partials.estado', ['estado' => $s->estado])</td>
                                <td>
                                    @if ($s->estado === 'aceptada')
                                        {{ $s->habitacion->arrendador->nombre }}: <b>{{ $s->habitacion->arrendador->celular }}</b>
                                    @else
                                        <span class="texto-suave">—</span>
                                    @endif
                                </td>
                                <td class="acciones">
                                    @if ($s->estado === 'aceptada')
                                        <a href="{{ route('constancia.visita', $s) }}" class="btn btn-borde btn-chico"><i class="bi bi-file-earmark-pdf"></i> Constancia</a>
                                        @if ($s->fecha_visita->isPast() || $s->fecha_visita->isToday())
                                            <a href="{{ route('habitaciones.show', $s->habitacion) }}#resenas" class="btn btn-borde btn-chico"><i class="bi bi-star"></i> Reseña</a>
                                        @endif
                                        @if ($s->puedeConfirmar())
                                            {{-- ¿el cuarto era como en las fotos? --}}
                                            <form method="POST" action="{{ route('estudiante.confirmar', $s) }}" class="confirmar-visita">
                                                @csrf
                                                <span>¿Era como en las fotos?</span>
                                                <button name="coincide" value="si" class="btn btn-chico"><i class="bi bi-hand-thumbs-up"></i> Sí</button>
                                                <button name="coincide" value="no" class="btn btn-borde btn-chico" onclick="return confirm('Vamos a avisar al administrador para que revise esta publicación. ¿Continuar?')"><i class="bi bi-hand-thumbs-down"></i> No</button>
                                            </form>
                                        @elseif ($s->confirmacion)
                                            <span class="confirmado-{{ $s->confirmacion }}"><i class="bi bi-{{ $s->confirmacion === 'coincide' ? 'patch-check-fill' : 'flag-fill' }}"></i> {{ $s->confirmacion === 'coincide' ? 'Confirmaste la visita' : 'Reportado' }}</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif
        </div>

        <div class="caja">
            <h2><i class="bi bi-briefcase"></i> Mis postulaciones</h2>
            @if ($postulaciones->isEmpty())
                <p class="vacio">Aún no postulaste a ningún trabajo. <a href="{{ route('trabajos.index') }}">Mira las ofertas</a>.</p>
            @else
                <div class="tabla-scroll">
                    <table class="tabla">
                        <tr>
                            <th>Oferta</th>
                            <th>Horario</th>
                            <th>Estado</th>
                            <th>Contacto</th>
                            <th>Opciones</th>
                        </tr>
                        @foreach ($postulaciones as $p)
                            <tr>
                                <td><b>{{ $p->trabajo->titulo }}</b><br><span class="texto-suave">{{ $p->trabajo->empresa }}</span></td>
                                <td>{{ $p->trabajo->horarioTexto() }}</td>
                                <td>@include('partials.estado', ['estado' => $p->estado])</td>
                                <td>
                                    @if ($p->estado === 'aceptada')
                                        {{ $p->trabajo->empleador->nombre }}: <b>{{ $p->trabajo->empleador->celular }}</b>
                                    @else
                                        <span class="texto-suave">—</span>
                                    @endif
                                </td>
                                <td class="acciones">
                                    @if ($p->estado === 'aceptada')
                                        <a href="{{ route('constancia.postulacion', $p) }}" class="btn btn-borde btn-chico"><i class="bi bi-file-earmark-pdf"></i> Constancia</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif
        </div>
    </main>
@endsection
