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

        <div class="graficos">
            <div class="caja">
                <h2><i class="bi bi-bar-chart"></i> Habitaciones por distrito</h2>
                <canvas id="grafDistritos"></canvas>
            </div>
            <div class="caja">
                <h2><i class="bi bi-cash-stack"></i> Precio promedio por distrito (S/)</h2>
                <canvas id="grafPrecios"></canvas>
            </div>
            <div class="caja">
                <h2><i class="bi bi-pie-chart"></i> Solicitudes de visita</h2>
                <canvas id="grafSolicitudes"></canvas>
            </div>
            <div class="caja">
                <h2><i class="bi bi-people"></i> Usuarios por rol</h2>
                <canvas id="grafRoles"></canvas>
            </div>
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        const datos = @json($graficos);
        const colores = ['#b4532a', '#1f3d36', '#c99a2e', '#6b8f71', '#8f3d1c'];
        const colorEstado = { aceptada: '#2e7d5b', pendiente: '#c99a2e', rechazada: '#c0392b' };
        Chart.defaults.font.family = 'Poppins';
        Chart.defaults.color = '#666';
        Chart.defaults.plugins.legend.position = 'bottom';
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.tooltip.backgroundColor = '#1f3d36';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        new Chart(document.getElementById('grafDistritos'), {
            type: 'bar',
            data: {
                labels: datos.distritos.map(d => d.distrito),
                datasets: [{ label: 'Habitaciones', data: datos.distritos.map(d => d.total), backgroundColor: '#b4532a', borderRadius: 8, maxBarThickness: 46 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        new Chart(document.getElementById('grafPrecios'), {
            type: 'bar',
            data: {
                labels: datos.distritos.map(d => d.distrito),
                datasets: [{ label: 'Precio promedio', data: datos.distritos.map(d => d.promedio), backgroundColor: '#1f3d36', borderRadius: 8, maxBarThickness: 26 }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('grafSolicitudes'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(datos.solicitudes).map(e => e.charAt(0).toUpperCase() + e.slice(1)),
                datasets: [{ data: Object.values(datos.solicitudes), backgroundColor: Object.keys(datos.solicitudes).map(e => colorEstado[e]), borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }]
            },
            options: { cutout: '68%' }
        });

        new Chart(document.getElementById('grafRoles'), {
            type: 'pie',
            data: {
                labels: Object.keys(datos.roles).map(e => e.charAt(0).toUpperCase() + e.slice(1)),
                datasets: [{ data: Object.values(datos.roles), backgroundColor: colores, borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }]
            },
            options: { cutout: '55%' }
        });
    </script>
@endpush
