@extends('layouts.app')

@section('titulo', 'Inicio')

@push('estilos')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('contenido')
    <!-- portada con carrusel de Bootstrap -->
    <section id="portada" class="carousel slide carousel-fade portada-carrusel" data-bs-ride="carousel" data-bs-interval="6000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#portada" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Diapositiva 1"></button>
            <button type="button" data-bs-target="#portada" data-bs-slide-to="1" aria-label="Diapositiva 2"></button>
            <button type="button" data-bs-target="#portada" data-bs-slide-to="2" aria-label="Diapositiva 3"></button>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active" style="--fondo-img: url('{{ asset('img/cusco/plaza.jpg') }}')">
                <div class="contenedor portada-texto">
                    <span class="portada-etiqueta"><i class="bi bi-geo-alt-fill"></i> Cusco, Perú</span>
                    <h1>Llegaste a Cusco.<br><span>No estás solo en el camino.</span></h1>
                    <p>Encuentra una habitación segura y un trabajo que encaje con tus clases. Arrendadores y empresas verificados.</p>
                    <div class="portada-botones">
                        <a href="{{ route('habitaciones.index') }}" class="btn btn-grande"><i class="bi bi-house-door"></i> Buscar habitación</a>
                        <a href="{{ route('trabajos.index') }}" class="btn btn-claro btn-grande"><i class="bi bi-briefcase"></i> Buscar trabajo</a>
                    </div>
                </div>
            </div>
            <div class="carousel-item" style="--fondo-img: url('{{ asset('img/habitaciones/h1.jpg') }}')">
                <div class="contenedor portada-texto">
                    <span class="portada-etiqueta"><i class="bi bi-patch-check-fill"></i> Cuartos verificados</span>
                    <h1>Visita antes de pagar.<br><span>Sin adelantos a ciegas.</span></h1>
                    <p>Pide una visita desde la página. El celular del arrendador recién aparece cuando acepta, y después puedes dejar tu reseña.</p>
                    <div class="portada-botones">
                        <a href="{{ route('habitaciones.index') }}" class="btn btn-grande"><i class="bi bi-map"></i> Ver el mapa de cuartos</a>
                    </div>
                </div>
            </div>
            <div class="carousel-item" style="--fondo-img: url('{{ asset('img/cusco/san-blas.jpg') }}')">
                <div class="contenedor portada-texto">
                    <span class="portada-etiqueta"><i class="bi bi-calendar-check-fill"></i> Trabajo + estudios</span>
                    <h1>Trabajos que respetan<br><span>tu horario de clases.</span></h1>
                    <p>Marca tus clases una vez y te mostramos solo las ofertas que no chocan con ellas.</p>
                    <div class="portada-botones">
                        <a href="{{ route('registro') }}" class="btn btn-grande"><i class="bi bi-person-plus"></i> Crear mi cuenta gratis</a>
                    </div>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#portada" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#portada" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </section>

    <!-- buscador flotante encima de la portada -->
    <div class="contenedor">
        <form class="buscador-flotante" action="{{ route('habitaciones.index') }}" method="GET">
            <div>
                <label for="bDistrito"><i class="bi bi-geo-alt"></i> Distrito</label>
                <select id="bDistrito" name="distrito">
                    <option value="">Todos los distritos</option>
                    @foreach ($distritos as $d)
                        <option>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="bPrecio"><i class="bi bi-cash-coin"></i> Precio máximo</label>
                <select id="bPrecio" name="precio_max">
                    <option value="">Cualquier precio</option>
                    <option value="250">Hasta S/ 250</option>
                    <option value="350">Hasta S/ 350</option>
                    <option value="450">Hasta S/ 450</option>
                </select>
            </div>
            <div>
                <label for="bTipo"><i class="bi bi-door-open"></i> Tipo</label>
                <select id="bTipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="individual">Individual</option>
                    <option value="compartida">Compartida</option>
                    <option value="minidepartamento">Minidepartamento</option>
                </select>
            </div>
            <button type="submit" class="btn btn-grande"><i class="bi bi-search"></i> Buscar</button>
        </form>
    </div>

    <main class="sin-espacio">
        <!-- numeros -->
        <section class="seccion-numeros">
            <div class="contenedor">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-3 revelar">
                        <b class="contador" data-hasta="{{ $numeros['habitaciones'] }}">{{ $numeros['habitaciones'] }}</b>
                        <span>habitaciones publicadas</span>
                    </div>
                    <div class="col-6 col-md-3 revelar">
                        <b class="contador" data-hasta="{{ $numeros['trabajos'] }}">{{ $numeros['trabajos'] }}</b>
                        <span>ofertas de trabajo</span>
                    </div>
                    <div class="col-6 col-md-3 revelar">
                        <b class="contador" data-hasta="{{ $numeros['verificados'] }}">{{ $numeros['verificados'] }}</b>
                        <span>cuentas verificadas</span>
                    </div>
                    <div class="col-6 col-md-3 revelar">
                        <b class="contador" data-hasta="{{ $numeros['distritos'] }}">{{ $numeros['distritos'] }}</b>
                        <span>distritos de Cusco</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- como funciona -->
        <section class="seccion">
            <div class="contenedor">
                <div class="encabezado-seccion revelar">
                    <span class="sobretitulo">En tres pasos</span>
                    <h2>¿Cómo funciona?</h2>
                </div>
                <div class="row g-4 pasos">
                    <div class="col-md-4 revelar">
                        <div class="paso">
                            <span class="paso-numero">1</span>
                            <i class="bi bi-person-plus"></i>
                            <h3>Regístrate</h3>
                            <p>Crea tu cuenta e indica de dónde vienes. Con tu correo de la universidad quedas como estudiante verificado.</p>
                        </div>
                    </div>
                    <div class="col-md-4 revelar">
                        <div class="paso">
                            <span class="paso-numero">2</span>
                            <i class="bi bi-calendar-week"></i>
                            <h3>Marca tu horario</h3>
                            <p>Te mostramos solo los trabajos que no chocan con tus clases.</p>
                        </div>
                    </div>
                    <div class="col-md-4 revelar">
                        <div class="paso">
                            <span class="paso-numero">3</span>
                            <i class="bi bi-house-check"></i>
                            <h3>Solicita una visita</h3>
                            <p>Visita la habitación antes de pagar. Nunca des adelantos sin ver el cuarto.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- por que tampu, con fotos flotantes -->
        <section class="seccion fondo-blanco">
            <div class="contenedor">
                <div class="row g-4 g-lg-5 align-items-center">
                    <div class="col-lg-6 revelar">
                        <div class="collage">
                            <img src="{{ asset('img/habitaciones/h5.jpg') }}" alt="Habitación iluminada con escritorio" class="collage-grande">
                            <img src="{{ asset('img/cusco/cuesta-san-blas.jpg') }}" alt="Cuesta de San Blas en Cusco" class="collage-chica flotar">
                            <div class="globo-flotante globo-1 flotar-lento">
                                <i class="bi bi-patch-check-fill"></i>
                                <div><b>Arrendador verificado</b><small>Revisamos su DNI</small></div>
                            </div>
                            @if ($numeros['promedio'])
                                <div class="globo-flotante globo-2 flotar">
                                    <i class="bi bi-star-fill"></i>
                                    <div><b>{{ number_format($numeros['promedio'], 1) }} de 5</b><small>en reseñas de visitas</small></div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-6 revelar">
                        <span class="sobretitulo">Por qué TAMPU</span>
                        <h2 class="titulo-grande">Un lugar seguro para empezar en Cusco</h2>
                        <p class="texto-suave">En la época inca los tampus eran casas de descanso en el Qhapaq Ñan. Nosotros queremos ser eso para quien llega de provincia.</p>
                        <ul class="lista-ventajas">
                            <li><i class="bi bi-shield-check"></i><div><b>Cuentas verificadas</b><span>El administrador revisa el DNI o RUC antes de que alguien publique.</span></div></li>
                            <li><i class="bi bi-geo"></i><div><b>Mapa de cuartos</b><span>Mira dónde queda cada habitación y qué tan cerca está de tu universidad.</span></div></li>
                            <li><i class="bi bi-chat-heart"></i><div><b>Chat y asistente</b><span>Escribe al arrendador o pregunta al asistente a cualquier hora.</span></div></li>
                            <li><i class="bi bi-flag"></i><div><b>Reportes</b><span>Si algo no está bien, repórtalo y el administrador lo revisa.</span></div></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- cerca de tu universidad -->
        <section class="seccion">
            <div class="contenedor">
                <div class="encabezado-seccion revelar">
                    <span class="sobretitulo">Elige por dónde estudias</span>
                    <h2>Cuartos cerca de tu universidad</h2>
                </div>
                <div class="row g-3">
                    @foreach ($universidades as $u)
                        <div class="col-6 col-lg-3 revelar">
                            <a href="{{ route('habitaciones.index', ['cerca_de' => $u['clave']]) }}" class="tarjeta-uni">
                                <i class="bi bi-mortarboard"></i>
                                <b>{{ $u['nombre'] }}</b>
                                <small>{{ $u['lugar'] }}</small>
                                <span>{{ $u['total'] }} {{ $u['total'] === 1 ? 'cuarto' : 'cuartos' }} <i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- mapa con todos los cuartos -->
        <section class="seccion fondo-blanco">
            <div class="contenedor">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-4 revelar">
                        <span class="sobretitulo">Mira dónde quedan</span>
                        <h2 class="titulo-grande">Todos los cuartos en un mapa</h2>
                        <p class="texto-suave">Cada marcador muestra el precio. Los íconos de birrete son las universidades, para que veas qué tan cerca estarías.</p>
                        <div class="distritos-lista">
                            @foreach ($distritos as $d)
                                @if (isset($porDistrito[$d]))
                                    <a href="{{ route('habitaciones.index', ['distrito' => $d]) }}">
                                        <span>{{ $d }}</span>
                                        <small>{{ $porDistrito[$d]->total }} · desde S/ {{ number_format($porDistrito[$d]->desde, 0) }}</small>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-8 revelar">
                        <div id="mapa" class="mapa mapa-portada" data-puntos='@json($puntos)'></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- habitaciones en carrusel deslizable -->
        <section class="seccion">
            <div class="contenedor">
                <div class="encabezado-seccion con-flechas revelar">
                    <div>
                        <span class="sobretitulo">Recién publicadas</span>
                        <h2>Habitaciones recientes</h2>
                    </div>
                    <div class="flechas">
                        <button type="button" class="flecha" data-carril="#carrilCuartos" data-dir="-1" aria-label="Anteriores"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="flecha" data-carril="#carrilCuartos" data-dir="1" aria-label="Siguientes"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                <div class="carril" id="carrilCuartos">
                    @foreach ($destacadas as $h)
                        <div class="carril-item">
                            @include('partials.tarjeta-habitacion')
                        </div>
                    @endforeach
                </div>
                <p class="centrado"><a href="{{ route('habitaciones.index') }}" class="btn btn-borde">Ver todas las habitaciones <i class="bi bi-arrow-right"></i></a></p>
            </div>
        </section>

        <!-- trabajos -->
        <section class="seccion fondo-blanco">
            <div class="contenedor">
                <div class="encabezado-seccion revelar">
                    <span class="sobretitulo">Para tus horas libres</span>
                    <h2>Trabajos recientes</h2>
                </div>
                <div class="row g-4">
                    @foreach ($trabajos as $t)
                        <div class="col-md-4 revelar">
                            @include('partials.tarjeta-trabajo')
                        </div>
                    @endforeach
                </div>
                <p class="centrado"><a href="{{ route('trabajos.index') }}" class="btn btn-borde">Ver todos los trabajos <i class="bi bi-arrow-right"></i></a></p>
            </div>
        </section>

        <!-- opiniones en carrusel -->
        @if ($resenas->isNotEmpty())
            <section class="seccion seccion-opiniones">
                <div class="contenedor">
                    <div class="encabezado-seccion revelar">
                        <span class="sobretitulo">Después de la visita</span>
                        <h2>Lo que dicen los estudiantes</h2>
                    </div>
                    <div id="opiniones" class="carousel slide revelar" data-bs-ride="carousel" data-bs-interval="7000">
                        <div class="carousel-inner">
                            @foreach ($resenas as $r)
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <figure class="opinion">
                                        <i class="bi bi-quote opinion-comillas"></i>
                                        <p class="estrellas">@include('partials.estrellas', ['n' => $r->estrellas])</p>
                                        <blockquote>{{ $r->comentario }}</blockquote>
                                        <figcaption>
                                            <span class="avatar-letras">{{ $r->estudiante->iniciales() }}</span>
                                            <span><b>{{ $r->estudiante->nombre }}</b><small>sobre "{{ $r->habitacion->titulo }}"</small></span>
                                        </figcaption>
                                    </figure>
                                </div>
                            @endforeach
                        </div>
                        <div class="carousel-indicators opiniones-puntos">
                            @foreach ($resenas as $r)
                                <button type="button" data-bs-target="#opiniones" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-label="Opinión {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- llamado para arrendadores y empresas -->
        <section class="banda-cta" style="--fondo-img: url('{{ asset('img/cusco/catedral.jpg') }}')">
            <div class="contenedor revelar">
                <h2>¿Tienes un cuarto o un trabajo para estudiantes?</h2>
                <p>Publica gratis. Verificamos tu cuenta y los estudiantes te escriben directamente.</p>
                <a href="{{ route('registro') }}" class="btn btn-grande btn-claro">Publicar gratis</a>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa.js') }}"></script>
@endpush
