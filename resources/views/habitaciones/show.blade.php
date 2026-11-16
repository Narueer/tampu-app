@extends('layouts.app')

@section('titulo', $habitacion->titulo)

@push('estilos')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('contenido')
    <main class="contenedor">
        <p class="titulo-pagina"><a href="{{ route('habitaciones.index') }}">Habitaciones</a> / {{ $habitacion->distrito }}</p>

        <div class="detalle">
            <div class="principal">
                <img src="{{ $habitacion->fotoUrl() }}" alt="{{ $habitacion->titulo }}">
                <h1>{{ $habitacion->titulo }}</h1>
                <p><i class="bi bi-geo-alt"></i> {{ $habitacion->direccion }}, {{ $habitacion->distrito }}@if ($habitacion->cerca_de) - cerca de {{ $habitacion->cerca_de }}@endif</p>
                <p>
                    <span class="etiqueta">{{ ucfirst($habitacion->tipo) }}</span>
                    @if ($habitacion->resenas->isNotEmpty())
                        <span class="estrellas">@include('partials.estrellas', ['n' => $habitacion->promedio()])</span>
                        {{ $habitacion->promedio() }} ({{ $habitacion->resenas->count() }} reseña{{ $habitacion->resenas->count() > 1 ? 's' : '' }})
                    @endif
                </p>

                <h2>Descripción</h2>
                <p>{{ $habitacion->descripcion }}</p>

                @if ($habitacion->listaServicios())
                    <h2>Servicios</h2>
                    <p>
                        @foreach ($habitacion->listaServicios() as $s)
                            <span class="etiqueta">{{ $s }}</span>
                        @endforeach
                    </p>
                @endif

                @if ($habitacion->lat)
                    @php
                        $punto = [['lat' => (float) $habitacion->lat, 'lng' => (float) $habitacion->lng, 'titulo' => $habitacion->titulo, 'precio' => (int) $habitacion->precio]];
                    @endphp
                    <h2>Ubicación</h2>
                    <div id="mapa" class="mapa mapa-chico" data-puntos='@json($punto)'></div>
                @endif

                <h2 id="resenas">Reseñas</h2>
                @forelse ($habitacion->resenas as $r)
                    <div class="resena">
                        <p><b>{{ $r->estudiante->nombre }}</b> <span class="estrellas">@include('partials.estrellas', ['n' => $r->estrellas])</span>
                            <span class="texto-suave">{{ $r->created_at->format('d/m/Y') }}</span></p>
                        <p>{{ $r->comentario }}</p>
                    </div>
                @empty
                    <p class="texto-suave">Todavía no tiene reseñas.</p>
                @endforelse

                @if ($puedeResenar)
                    <form action="{{ route('estudiante.resenar', $habitacion) }}" method="POST" class="caja form-resena">
                        @csrf
                        <h3>{{ $miResena ? 'Editar mi reseña' : 'Deja tu reseña' }}</h3>
                        <div class="elegir-estrellas">
                            @for ($i = 5; $i >= 1; $i--)
                                <input type="radio" id="e{{ $i }}" name="estrellas" value="{{ $i }}" @checked(old('estrellas', $miResena->estrellas ?? 0) == $i) required>
                                <label for="e{{ $i }}" title="{{ $i }} estrella(s)"><i class="bi bi-star-fill"></i></label>
                            @endfor
                        </div>
                        <textarea name="comentario" rows="3" maxlength="500" minlength="10" required
                            placeholder="¿Cómo te fue en la visita? ¿Coincide con las fotos?">{{ old('comentario', $miResena->comentario ?? '') }}</textarea>
                        <button type="submit" class="btn btn-chico">Publicar reseña</button>
                    </form>
                @endif
            </div>

            <aside>
                <p class="precio">S/ {{ number_format($habitacion->precio, 0) }} <small>al mes</small></p>
                <p><b>{{ $habitacion->arrendador->nombre }}</b> <span class="etiqueta verificado">✔ Verificado</span></p>

                @auth
                    @if (auth()->user()->rol === 'estudiante')
                        @include('partials.errores')
                        @if ($yaSolicito)
                            <p class="alerta alerta-ok">Ya enviaste una solicitud. Revisa su estado en <a href="{{ route('estudiante.panel') }}">Mi panel</a>.</p>
                        @else
                            <form action="{{ route('estudiante.solicitar', $habitacion) }}" method="POST">
                                @csrf
                                <label for="fecha">Fecha de visita</label>
                                <input type="date" id="fecha" name="fecha_visita" value="{{ old('fecha_visita') }}"
                                    min="{{ now()->toDateString() }}" required>
                                <label for="mensaje">Mensaje</label>
                                <textarea id="mensaje" name="mensaje" rows="3" maxlength="300"
                                    placeholder="Hola, me interesa la habitación...">{{ old('mensaje') }}</textarea>
                                <button type="submit" class="btn btn-ancho">Solicitar visita</button>
                            </form>
                        @endif

                        <div class="botones-detalle">
                            <form action="{{ route('estudiante.favorito', $habitacion) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-borde btn-chico">
                                    <i class="bi {{ $esFavorito ? 'bi-heart-fill' : 'bi-heart' }}"></i> {{ $esFavorito ? 'En favoritos' : 'Guardar' }}
                                </button>
                            </form>
                            <form action="{{ route('mensajes.iniciar') }}" method="POST">
                                @csrf
                                <input type="hidden" name="habitacion_id" value="{{ $habitacion->id }}">
                                <button type="submit" class="btn btn-borde btn-chico"><i class="bi bi-chat-dots"></i> Enviar mensaje</button>
                            </form>
                        </div>
                    @else
                        <p class="texto-suave">Solo los estudiantes pueden solicitar visitas.</p>
                    @endif
                    @include('partials.reportar', ['campo' => 'habitacion_id', 'id' => $habitacion->id, 'dueno' => $habitacion->usuario_id])
                @else
                    <p><a href="{{ route('login') }}" class="btn btn-ancho">Inicia sesión para solicitar una visita</a></p>
                @endauth

                <p class="aviso">Nunca pagues adelantos sin haber visitado la habitación. El celular del arrendador se muestra cuando acepta tu visita.</p>
            </aside>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa.js') }}"></script>
@endpush
