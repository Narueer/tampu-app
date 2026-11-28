@extends('layouts.app')

@section('titulo', 'Administración')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        @include('partials.bienvenida', [
            'titulo' => 'Panel del administrador',
            'subtitulo' => 'Resumen de TAMPU: publicaciones, cuentas por verificar y lo último que pasó en el sistema.',
            'acciones' => [[route('admin.usuarios'), 'people', 'Usuarios'], [route('admin.seguridad'), 'shield-lock', 'Bitácora']],
        ])

        @include('partials.kpis', ['kpis' => [
            ['mortarboard', $datos['estudiantes'], 'Estudiantes', 'terracota', route('admin.usuarios', ['rol' => 'estudiante'])],
            ['patch-check', $datos['verificados'], 'Cuentas verificadas', 'verde'],
            ['house', $datos['habitaciones'], 'Habitaciones publicadas', 'azul'],
            ['briefcase', $datos['trabajos'], 'Ofertas activas', 'amarillo'],
            ['envelope-paper', $datos['solicitudes'], 'Solicitudes de visita', 'azul'],
            ['check2-circle', $datos['aceptadas'], 'Visitas aceptadas', 'verde'],
            ['person-workspace', $datos['postulaciones'], 'Postulaciones', 'terracota'],
            ['flag', $datos['reportes'], 'Reportes por revisar', 'rosa', route('admin.reportes')],
        ]])

        {{-- gráficos hechos con HTML (barras y proporciones): se leen mejor que los "donuts" y no cargan librerías --}}
        @php
            $distritos = collect($graficos['distritos']);
            $maxTotal = max(1, (int) $distritos->max('total'));
            $maxPrecio = max(1, (int) $distritos->max('promedio'));
            $coloresEstado = ['aceptada' => '#465548', 'pendiente' => '#b58a4a', 'rechazada' => '#965c57'];
            $coloresRol = ['estudiante' => '#465548', 'arrendador' => '#b58a4a', 'empleador' => '#53677a'];
            $proporciones = [
                ['Solicitudes de visita', 'solicitudes', collect($graficos['solicitudes']), $coloresEstado],
                ['Postulaciones a trabajos', 'postulaciones', collect($graficos['postulaciones']), $coloresEstado],
                ['Usuarios por rol', 'usuarios', collect($graficos['roles']), $coloresRol],
            ];
        @endphp
        <div class="graficos">
            <div class="caja">
                <h2>Habitaciones por distrito</h2>
                <div class="barras-distrito">
                    @foreach ($distritos as $d)
                        <div class="barra-fila">
                            <b>{{ $d->distrito }}</b>
                            <span class="barra-pista"><span style="width: {{ round($d->total / $maxTotal * 100) }}%"></span></span>
                            <span class="barra-valor">{{ $d->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="caja">
                <h2>Precio promedio por distrito</h2>
                <div class="barras-distrito">
                    @foreach ($distritos->sortByDesc('promedio') as $d)
                        <div class="barra-fila barra-precio">
                            <b>{{ $d->distrito }}</b>
                            <span class="barra-pista"><span style="width: {{ round($d->promedio / $maxPrecio * 100) }}%"></span></span>
                            <span class="barra-valor">S/ {{ (int) $d->promedio }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @foreach ($proporciones as [$titulo, $unidad, $valores, $colores])
                @php($total = max(1, $valores->sum()))
                <div class="caja">
                    <h2>{{ $titulo }}</h2>
                    <p class="proporcion-total">{{ $valores->sum() }}<small>{{ $unidad }} en total</small></p>
                    <div class="proporcion" role="img" aria-label="{{ $titulo }}: {{ $valores->map(fn ($n, $k) => "$k $n")->implode(', ') }}">
                        @foreach ($valores as $clave => $n)
                            <span style="width: {{ $n / $total * 100 }}%; background: {{ $colores[$clave] ?? '#8c857a' }}" title="{{ ucfirst($clave) }}: {{ $n }}"></span>
                        @endforeach
                    </div>
                    <ul class="proporcion-leyenda">
                        @foreach ($valores as $clave => $n)
                            <li><i style="background: {{ $colores[$clave] ?? '#8c857a' }}"></i> {{ ucfirst($clave) }} <span class="texto-suave">{{ round($n / $total * 100) }}%</span> <b>{{ $n }}</b></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="row g-4 align-items-start">
        <div class="col-lg-8">
        <div class="caja">
            <h2><i class="bi bi-shield-check"></i> Verificaciones pendientes</h2>
            @if ($pendientes->isEmpty())
                <p class="vacio">No hay cuentas por revisar.</p>
            @else
                <div class="tabla-scroll">
                    <table class="tabla">
                        <tr>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>DNI</th>
                            <th>Registro</th>
                            <th>Documento</th>
                            <th>Decisión</th>
                        </tr>
                        @foreach ($pendientes as $u)
                            <tr>
                                <td><b>{{ $u->nombre }}</b><br><span class="texto-suave">{{ $u->email }}</span></td>
                                <td>{{ ucfirst($u->rol) }}</td>
                                <td>{{ $u->dni }}</td>
                                <td>{{ $u->created_at->format('d/m/Y') }}</td>
                                <td><a href="{{ route('admin.documento', $u) }}" target="_blank"><i class="bi bi-file-earmark-text"></i> Ver</a></td>
                                <td class="acciones">
                                    <form action="{{ route('admin.verificar', $u) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button name="accion" value="verificado" class="btn btn-chico">Verificar</button>
                                        <button name="accion" value="rechazado" class="btn btn-borde btn-chico">Rechazar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif
        </div>
        </div>

        <div class="col-lg-4">
            <div class="caja actividad-reciente">
                <h2><i class="bi bi-activity"></i> Actividad reciente</h2>
                @forelse ($actividad as $e)
                    <div class="linea-tiempo {{ $e->esAlerta() ? 'alerta' : '' }}">
                        <span class="punto"></span>
                        <div>
                            <b>{{ $e->nombreTipo() }}</b>
                            <small>{{ $e->usuario->nombre ?? $e->email }} · {{ $e->created_at->locale('es')->diffForHumans() }}</small>
                        </div>
                    </div>
                @empty
                    <p class="vacio">Todavía no hay movimientos.</p>
                @endforelse
                <a href="{{ route('admin.seguridad') }}" class="ver-todo">Ver la bitácora completa <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="caja">
                <h2><i class="bi bi-person-plus"></i> Últimas cuentas</h2>
                @foreach ($nuevos as $u)
                    <div class="usuario-mini">
                        <span class="avatar-mini">{{ $u->iniciales() }}</span>
                        <div>
                            <b>{{ $u->nombre }}</b> @include('partials.insignia-uni', ['usuario' => $u])
                            <small>{{ ucfirst($u->rol) }} · {{ $u->created_at->format('d/m/Y') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        </div>
    </main>
@endsection


