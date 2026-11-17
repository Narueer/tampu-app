@extends('layouts.app')

@section('titulo', $habitacion->titulo . ' - S/ ' . number_format($habitacion->precio, 0) . ' en ' . $habitacion->distrito)
@section('descripcion', \Illuminate\Support\Str::limit($habitacion->descripcion, 155))
@section('imagen', $habitacion->fotoUrl())

@push('estilos')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('contenido')
    <main class="contenedor">
        @php
            $fotos = $habitacion->todasLasFotos();
            $distancias = $habitacion->distancias();
        @endphp

        <nav class="migas migas-claras" aria-label="Ubicación">
            <a href="{{ route('inicio') }}"><i class="bi bi-house"></i> Inicio</a>
            <i class="bi bi-chevron-right"></i>
            <a href="{{ route('habitaciones.index') }}">Habitaciones</a>
            <i class="bi bi-chevron-right"></i>
            <a href="{{ route('habitaciones.index', ['distrito' => $habitacion->distrito]) }}">{{ $habitacion->distrito }}</a>
        </nav>

        <div class="cabecera-detalle">
            <div>
                <h1>{{ $habitacion->titulo }}</h1>
                <p class="texto-suave">
                    <i class="bi bi-geo-alt"></i> {{ $habitacion->direccion }}, {{ $habitacion->distrito }}
                    @if ($habitacion->resenas->isNotEmpty())
                        · <span class="estrellas">@include('partials.estrellas', ['n' => $habitacion->promedio()])</span>
                        <a href="#resenas">{{ $habitacion->promedio() }} ({{ $habitacion->resenas->count() }} reseña{{ $habitacion->resenas->count() > 1 ? 's' : '' }})</a>
                    @endif
                </p>
            </div>
            <button type="button" class="btn btn-borde btn-chico" id="btnCompartir" data-url="{{ url()->current() }}" data-titulo="{{ $habitacion->titulo }}">
                <i class="bi bi-share"></i> Compartir
            </button>
        </div>

        <!-- galeria tipo revista: una foto grande y hasta cuatro chicas; todas se ven en el modal -->
        <div class="galeria galeria-{{ min(count($fotos), 5) }} {{ count($fotos) < 2 ? 'galeria-simple' : '' }}">
            @foreach (array_slice($fotos, 0, 5) as $i => $f)
                <button type="button" class="galeria-foto {{ $i === 0 ? 'galeria-principal' : '' }}" data-bs-toggle="modal" data-bs-target="#fotoGrande" data-foto="{{ $i }}" aria-label="Ver foto {{ $i + 1 }}">
                    <img src="{{ $f['url'] }}" alt="{{ $f['descripcion'] }}" @if ($i > 0) loading="lazy" @endif>
                </button>
            @endforeach
            <button type="button" class="btn btn-claro btn-chico galeria-todas" data-bs-toggle="modal" data-bs-target="#fotoGrande" data-foto="0">
                <i class="bi bi-images"></i> Ver las {{ count($fotos) }} fotos
            </button>
        </div>

        <div class="detalle">
            <div class="principal">
                <!-- datos rapidos -->
                <div class="datos-rapidos">
                    <div><i class="bi bi-door-open"></i><span>Tipo</span><b>{{ ucfirst($habitacion->tipo) }}</b></div>
                    @if ($habitacion->metros)
                        <div><i class="bi bi-arrows-angle-expand"></i><span>Tamaño</span><b>{{ $habitacion->metros }} m²</b></div>
                    @endif
                    @if ($habitacion->piso)
                        <div><i class="bi bi-building"></i><span>Piso</span><b>{{ $habitacion->piso }}°</b></div>
                    @endif
                    @if ($habitacion->bano)
                        <div><i class="bi bi-droplet"></i><span>Baño</span><b>{{ ucfirst($habitacion->bano) }}</b></div>
                    @endif
                    @if ($habitacion->garantia)
                        <div><i class="bi bi-shield-lock"></i><span>Garantía</span><b>S/ {{ number_format($habitacion->garantia, 0) }}</b></div>
                    @endif
                </div>

                <h2>Descripción</h2>
                <p>{{ $habitacion->descripcion }}</p>

                @if ($habitacion->listaServicios())
                    <h2>Lo que incluye</h2>
                    <div class="servicios">
                        @foreach ($habitacion->listaServicios() as $s)
                            <span><i class="bi bi-{{ \App\Models\Habitacion::iconoServicio($s) }}"></i> {{ $s }}</span>
                        @endforeach
                    </div>
                @endif

                @if ($habitacion->lat)
                    @php
                        $punto = [['lat' => (float) $habitacion->lat, 'lng' => (float) $habitacion->lng, 'titulo' => $habitacion->titulo, 'precio' => (int) $habitacion->precio]];
                        foreach (array_slice($distancias, 0, 2) as $u) {
                            $punto[] = ['lat' => $u['lat'], 'lng' => $u['lng'], 'titulo' => $u['nombre'], 'tipo' => 'uni'];
                        }
                    @endphp
                    <h2>Ubicación</h2>
                    <p class="texto-suave">{{ $habitacion->direccion }}, {{ $habitacion->distrito }}. En el mapa también están las universidades más cercanas.</p>
                    <div id="mapa" class="mapa mapa-chico" data-puntos='@json($punto)'></div>

                    <h2>¿Qué tan lejos queda de tu universidad?</h2>
                    <div class="distancias">
                        @foreach ($distancias as $u)
                            <div class="distancia {{ $loop->first ? 'mas-cerca' : '' }}">
                                <span class="distancia-icono"><i class="bi bi-{{ $u['modo'] === 'a pie' ? 'person-walking' : 'bus-front' }}"></i></span>
                                <div>
                                    <b>{{ $u['nombre'] }}</b>
                                    <small>{{ $u['km'] }} km · unos {{ $u['minutos'] }} min {{ $u['modo'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="nota-distancia">Distancia aproximada por calles a partir de la ubicación en OpenStreetMap.</p>
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

            <aside id="reservar" data-caja-reserva>
                <p class="precio">S/ {{ number_format($habitacion->precio, 0) }} <small>al mes</small></p>
                @if ($habitacion->garantia)
                    <p class="texto-suave garantia">Garantía de S/ {{ number_format($habitacion->garantia, 0) }}, se paga recién al mudarte.</p>
                @endif
                <div class="arrendador-mini">
                    <span class="avatar-mini">{{ $habitacion->arrendador->iniciales() }}</span>
                    <div>
                        <b>{{ $habitacion->arrendador->nombre }}</b>
                        <small><i class="bi bi-patch-check-fill"></i> Arrendador verificado · {{ $habitacion->arrendador->habitaciones()->count() }} publicaciones</small>
                    </div>
                </div>

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
        <!-- barra con el precio que aparece al bajar -->
        <div class="barra-reserva" id="barraReserva">
            <div class="contenedor">
                <img src="{{ $habitacion->fotoUrl() }}" alt="">
                <div class="barra-reserva-texto">
                    <b>{{ $habitacion->titulo }}</b>
                    <span>S/ {{ number_format($habitacion->precio, 0) }} al mes · {{ $habitacion->distrito }}</span>
                </div>
                @auth
                    <a href="#reservar" class="btn">{{ auth()->user()->rol === 'estudiante' ? 'Solicitar visita' : 'Ver detalle' }}</a>
                @else
                    <a href="{{ route('login') }}" class="btn">Solicitar visita</a>
                @endauth
            </div>
        </div>

        @if ($similares->isNotEmpty())
            <section class="similares">
                <div class="encabezado-seccion con-flechas">
                    <div>
                        <span class="sobretitulo">También te puede interesar</span>
                        <h2>Más habitaciones en {{ $habitacion->distrito }} y alrededores</h2>
                    </div>
                    <div class="flechas">
                        <button type="button" class="flecha" data-carril="#carrilSimilares" data-dir="-1" aria-label="Anteriores"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="flecha" data-carril="#carrilSimilares" data-dir="1" aria-label="Siguientes"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                <div class="carril" id="carrilSimilares">
                    @foreach ($similares as $h)
                        <div class="carril-item">@include('partials.tarjeta-habitacion', ['favoritos' => $idsFavoritos])</div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- todas las fotos en un carrusel dentro de un modal de Bootstrap -->
        <div class="modal fade" id="fotoGrande" tabindex="-1" aria-label="Fotos de {{ $habitacion->titulo }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content modal-foto">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    <div id="carruselFotos" class="carousel slide">
                        <div class="carousel-inner">
                            @foreach ($fotos as $f)
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <img src="{{ $f['url'] }}" alt="{{ $f['descripcion'] }}" loading="lazy">
                                    <p class="pie-foto">{{ $f['descripcion'] }} · {{ $loop->iteration }} de {{ count($fotos) }}</p>
                                </div>
                            @endforeach
                        </div>
                        @if (count($fotos) > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#carruselFotos" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span></button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carruselFotos" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Siguiente</span></button>
                        @endif
                    </div>
                    <div class="miniaturas">
                        @foreach ($fotos as $f)
                            <button type="button" data-bs-target="#carruselFotos" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-label="Foto {{ $loop->iteration }}">
                                <img src="{{ $f['url'] }}" alt="" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa.js') }}?v={{ filemtime(public_path('js/mapa.js')) }}"></script>
@endpush
