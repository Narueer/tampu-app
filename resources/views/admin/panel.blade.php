@extends('layouts.app')

@section('titulo', 'Administración')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Panel del administrador</h1>
        </div>

        <div class="numeros">
            <div><i class="bi bi-mortarboard"></i><b>{{ $datos['estudiantes'] }}</b><span>Estudiantes</span></div>
            <div><i class="bi bi-patch-check"></i><b>{{ $datos['verificados'] }}</b><span>Cuentas verificadas</span></div>
            <div><i class="bi bi-house"></i><b>{{ $datos['habitaciones'] }}</b><span>Habitaciones publicadas</span></div>
            <div><i class="bi bi-briefcase"></i><b>{{ $datos['trabajos'] }}</b><span>Ofertas activas</span></div>
            <div><i class="bi bi-envelope"></i><b>{{ $datos['solicitudes'] }}</b><span>Solicitudes de visita</span></div>
            <div><i class="bi bi-check2-circle"></i><b>{{ $datos['aceptadas'] }}</b><span>Visitas aceptadas</span></div>
            <div><i class="bi bi-person-workspace"></i><b>{{ $datos['postulaciones'] }}</b><span>Postulaciones</span></div>
            <div><i class="bi bi-flag"></i><b>{{ $datos['reportes'] }}</b><span>Reportes por revisar</span></div>
        </div>

        <div class="graficos">
            <div class="caja">
                <h2>Habitaciones por distrito</h2>
                <canvas id="grafDistritos"></canvas>
            </div>
            <div class="caja">
                <h2>Precio promedio por distrito (S/)</h2>
                <canvas id="grafPrecios"></canvas>
            </div>
            <div class="caja">
                <h2>Solicitudes de visita</h2>
                <canvas id="grafSolicitudes"></canvas>
            </div>
            <div class="caja">
                <h2>Usuarios por rol</h2>
                <canvas id="grafRoles"></canvas>
            </div>
        </div>

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
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        const datos = @json($graficos);
        const colores = ['#b4532a', '#1f3d36', '#c99a2e', '#6b8f71', '#8f3d1c'];
        const colorEstado = { aceptada: '#2e7d5b', pendiente: '#c99a2e', rechazada: '#c0392b' };
        Chart.defaults.font.family = 'Poppins';

        new Chart(document.getElementById('grafDistritos'), {
            type: 'bar',
            data: {
                labels: datos.distritos.map(d => d.distrito),
                datasets: [{ label: 'Habitaciones', data: datos.distritos.map(d => d.total), backgroundColor: '#b4532a' }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        new Chart(document.getElementById('grafPrecios'), {
            type: 'bar',
            data: {
                labels: datos.distritos.map(d => d.distrito),
                datasets: [{ label: 'Precio promedio', data: datos.distritos.map(d => d.promedio), backgroundColor: '#1f3d36' }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('grafSolicitudes'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(datos.solicitudes).map(e => e.charAt(0).toUpperCase() + e.slice(1)),
                datasets: [{ data: Object.values(datos.solicitudes), backgroundColor: Object.keys(datos.solicitudes).map(e => colorEstado[e]) }]
            }
        });

        new Chart(document.getElementById('grafRoles'), {
            type: 'pie',
            data: {
                labels: Object.keys(datos.roles).map(e => e.charAt(0).toUpperCase() + e.slice(1)),
                datasets: [{ data: Object.values(datos.roles), backgroundColor: colores }]
            }
        });
    </script>
@endpush
