@extends('layouts.app')

@section('titulo', $trabajo->titulo . ' en ' . $trabajo->empresa . ' - Cusco')
@section('descripcion', \Illuminate\Support\Str::limit($trabajo->descripcion, 155))

@push('estilos')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('contenido')
    <main class="contenedor">
        <nav class="migas migas-claras" aria-label="Ubicación">
            <a href="{{ route('inicio') }}"><i class="bi bi-house"></i> Inicio</a>
            <i class="bi bi-chevron-right"></i>
            <a href="{{ route('trabajos.index') }}">Trabajos</a>
            <i class="bi bi-chevron-right"></i>
            <span>{{ $trabajo->empresa }}</span>
        </nav>

        <!-- cabecera de la oferta -->
        <section class="cabecera-trabajo">
            <span class="logo-empresa logo-grande">{{ $trabajo->inicialesEmpresa() }}</span>
            <div class="cabecera-trabajo-texto">
                <p class="sobretitulo">{{ $trabajo->empresa }}</p>
                <h1>{{ $trabajo->titulo }}</h1>
                <div class="chips">
                    <span><i class="bi bi-geo-alt"></i> {{ $trabajo->distrito }}</span>
                    <span><i class="bi bi-hourglass-split"></i> {{ ucfirst($trabajo->modalidad) }}</span>
                    <span><i class="bi bi-people"></i> {{ $trabajo->vacantes }} {{ $trabajo->vacantes > 1 ? 'vacantes' : 'vacante' }}</span>
                    <span><i class="bi bi-calendar3"></i> Publicado {{ $trabajo->created_at->locale('es')->diffForHumans() }}</span>
                    <span class="chip-verificado"><i class="bi bi-patch-check-fill"></i> Empresa verificada</span>
                </div>
            </div>
            <div class="cabecera-trabajo-pago">
                <b>S/ {{ number_format($trabajo->pago, 0) }}</b>
                <span>por {{ $trabajo->pago_por === 'dia' ? 'día' : $trabajo->pago_por }}</span>
            </div>
        </section>

        <div class="detalle">
            <div class="principal">
                <h2>Sobre el trabajo</h2>
                <p>{{ $trabajo->descripcion }}</p>

                @if ($trabajo->listaRequisitos())
                    <h2>Requisitos</h2>
                    <ul class="lista-check">
                        @foreach ($trabajo->listaRequisitos() as $r)
                            <li><i class="bi bi-check2-circle"></i> {{ $r }}</li>
                        @endforeach
                    </ul>
                @endif

                <h2>Horario</h2>
                <p class="texto-suave">
                    En color están los turnos de trabajo.
                    @if ($clases->isNotEmpty())
                        Con rayas, los turnos en los que tienes clases.
                    @endif
                </p>
                <div class="tabla-scroll">
                    <table class="horario horario-ver">
                        <tr>
                            <th></th>
                            @foreach (\App\Models\Horario::DIAS as $corto)
                                <th>{{ $corto }}</th>
                            @endforeach
                        </tr>
                        @foreach (\App\Models\Horario::TURNOS as $turno => $nombreTurno)
                            <tr>
                                <th>{{ ucfirst($nombreTurno) }}</th>
                                @foreach (\App\Models\Horario::DIAS as $dia => $corto)
                                    @php
                                        $clave = $dia . '-' . $turno;
                                        $trabaja = in_array($clave, $deTrabajo);
                                        $estudia = in_array($clave, $deClase);
                                    @endphp
                                    <td class="{{ $trabaja ? 'turno-trabajo' : '' }} {{ $estudia ? 'turno-clase' : '' }} {{ $trabaja && $estudia ? 'turno-choque' : '' }}"
                                        title="{{ $corto }} {{ $nombreTurno }}">
                                        @if ($trabaja && $estudia)
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                        @elseif ($trabaja)
                                            <i class="bi bi-briefcase-fill"></i>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </table>
                </div>

                @if ($clases->isNotEmpty())
                    @if ($choques)
                        <p class="alerta alerta-error"><i class="bi bi-exclamation-triangle"></i> Este trabajo choca con tus clases en {{ count($choques) }} turno(s). Habla con la empresa antes de postular.</p>
                    @else
                        <p class="alerta alerta-ok"><i class="bi bi-check-circle"></i> Este trabajo no choca con tu horario de clases.</p>
                    @endif
                @endif

                @if ($trabajo->lat)
                    @php
                        $punto = [['lat' => (float) $trabajo->lat, 'lng' => (float) $trabajo->lng, 'titulo' => $trabajo->empresa, 'empresa' => $trabajo->direccion, 'pago' => $trabajo->pagoTexto(), 'tipo' => 'trabajo']];
                        foreach (array_slice($distancias, 0, 2) as $u) {
                            $punto[] = ['lat' => $u['lat'], 'lng' => $u['lng'], 'titulo' => $u['nombre'], 'tipo' => 'uni'];
                        }
                    @endphp
                    <h2>¿Dónde queda?</h2>
                    <p class="texto-suave"><i class="bi bi-geo-alt"></i> {{ $trabajo->direccion }}, {{ $trabajo->distrito }}</p>
                    <div id="mapa" class="mapa mapa-chico" data-puntos='@json($punto)'></div>

                    <div class="distancias mt-3">
                        @foreach (array_slice($distancias, 0, 4) as $u)
                            <div class="distancia {{ $loop->first ? 'mas-cerca' : '' }}">
                                <span class="distancia-icono"><i class="bi bi-{{ $u['modo'] === 'a pie' ? 'person-walking' : 'bus-front' }}"></i></span>
                                <div>
                                    <b>Desde {{ $u['nombre'] }}</b>
                                    <small>{{ $u['km'] }} km · unos {{ $u['minutos'] }} min {{ $u['modo'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside>
                <p class="precio">S/ {{ number_format($trabajo->pago, 0) }} <small>por {{ $trabajo->pago_por === 'dia' ? 'día' : $trabajo->pago_por }}</small></p>
                <p class="texto-suave">{{ $trabajo->horarioTexto() }}</p>

                <div class="arrendador-mini">
                    <span class="avatar-mini">{{ $trabajo->inicialesEmpresa() }}</span>
                    <div>
                        <b>{{ $trabajo->empresa }}</b>
                        <small><i class="bi bi-patch-check-fill"></i> Verificada · {{ $trabajo->empleador->trabajos()->count() }} ofertas</small>
                    </div>
                </div>

                @auth
                    @if (auth()->user()->rol === 'estudiante')
                        @include('partials.errores')
                        @if ($postulacion)
                            <p>Tu postulación: @include('partials.estado', ['estado' => $postulacion->estado])</p>
                        @else
                            <form action="{{ route('estudiante.postular', $trabajo) }}" method="POST">
                                @csrf
                                <label for="mensaje">Mensaje para la empresa (opcional)</label>
                                <textarea id="mensaje" name="mensaje" rows="3" maxlength="300" placeholder="Cuéntale por qué te interesa"></textarea>
                                <button type="submit" class="btn btn-ancho"><i class="bi bi-send"></i> Postular</button>
                            </form>
                        @endif
                        <form action="{{ route('mensajes.iniciar') }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="trabajo_id" value="{{ $trabajo->id }}">
                            <button type="submit" class="btn btn-borde btn-ancho"><i class="bi bi-chat-dots"></i> Escribir a la empresa</button>
                        </form>
                    @else
                        <p class="texto-suave">Solo los estudiantes pueden postular.</p>
                    @endif
                    @include('partials.reportar', ['campo' => 'trabajo_id', 'id' => $trabajo->id, 'dueno' => $trabajo->usuario_id])
                @else
                    <p><a href="{{ route('login') }}" class="btn btn-ancho">Inicia sesión para postular</a></p>
                @endauth

                <p class="aviso">Ninguna empresa debe cobrarte por postular o por "capacitación". Si pasa, repórtala.</p>
            </aside>
        </div>

        @if ($similares->isNotEmpty())
            <section class="similares">
                <div class="encabezado-seccion con-flechas">
                    <div>
                        <span class="sobretitulo">Sigue buscando</span>
                        <h2>Ofertas parecidas</h2>
                    </div>
                    <div class="flechas">
                        <button type="button" class="flecha" data-carril="#carrilTrabajos" data-dir="-1" aria-label="Anteriores"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="flecha" data-carril="#carrilTrabajos" data-dir="1" aria-label="Siguientes"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                <div class="carril" id="carrilTrabajos">
                    @foreach ($similares as $t)
                        <div class="carril-item">@include('partials.tarjeta-trabajo')</div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa.js') }}?v={{ filemtime(public_path('js/mapa.js')) }}"></script>
@endpush
