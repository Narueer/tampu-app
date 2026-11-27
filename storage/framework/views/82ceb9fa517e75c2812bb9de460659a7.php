<?php $__env->startSection('titulo', 'Cuartos y trabajos para estudiantes en Cusco'); ?>
<?php $__env->startSection('descripcion', 'Encuentra habitaciones verificadas cerca de la UNSAAC, la U. Andina y la U. Continental, y trabajos de medio tiempo que respetan tu horario de clases en Cusco.'); ?>

<?php $__env->startPush('meta'); ?>
    <script type="application/ld+json">
        <?php echo json_encode([
            "\x40context" => 'https://schema.org',
            "\x40type" => 'WebSite',
            'name' => 'TAMPU',
            'url' => route('inicio'),
            'description' => 'Habitaciones verificadas y trabajos de medio tiempo para estudiantes en Cusco.',
            'inLanguage' => 'es-PE',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>

    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('estilos'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('contenido'); ?>
    <!-- portada con carrusel de Bootstrap -->
    <section id="portada" class="carousel slide carousel-fade portada-carrusel" data-bs-ride="carousel" data-bs-interval="6000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#portada" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Diapositiva 1"></button>
            <button type="button" data-bs-target="#portada" data-bs-slide-to="1" aria-label="Diapositiva 2"></button>
            <button type="button" data-bs-target="#portada" data-bs-slide-to="2" aria-label="Diapositiva 3"></button>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active" style="--fondo-img: url('<?php echo e(asset('img/cusco/plaza.jpg')); ?>')">
                <div class="contenedor portada-texto">
                    <span class="portada-etiqueta"><i class="bi bi-geo-alt-fill"></i> Cusco, Perú</span>
                    <h1>Llegaste a Cusco.<br><span>No estás solo en el camino.</span></h1>
                    <p>Encuentra una habitación segura y un trabajo que encaje con tus clases. Arrendadores y empresas verificados.</p>
                    <div class="portada-botones">
                        <a href="<?php echo e(route('habitaciones.index')); ?>" class="btn btn-grande"><i class="bi bi-house-door"></i> Buscar habitación</a>
                        <a href="<?php echo e(route('trabajos.index')); ?>" class="btn btn-claro btn-grande"><i class="bi bi-briefcase"></i> Buscar trabajo</a>
                    </div>
                </div>
            </div>
            <div class="carousel-item" style="--fondo-img: url('<?php echo e(asset('img/habitaciones/h1.jpg')); ?>')">
                <div class="contenedor portada-texto">
                    <span class="portada-etiqueta"><i class="bi bi-patch-check-fill"></i> Cuartos verificados</span>
                    <h1>Visita antes de pagar.<br><span>Sin adelantos a ciegas.</span></h1>
                    <p>Pide una visita desde la página. El celular del arrendador recién aparece cuando acepta, y después puedes dejar tu reseña.</p>
                    <div class="portada-botones">
                        <a href="<?php echo e(route('habitaciones.index')); ?>" class="btn btn-grande"><i class="bi bi-map"></i> Ver el mapa de cuartos</a>
                    </div>
                </div>
            </div>
            <div class="carousel-item" style="--fondo-img: url('<?php echo e(asset('img/cusco/san-blas.jpg')); ?>')">
                <div class="contenedor portada-texto">
                    <span class="portada-etiqueta"><i class="bi bi-calendar-check-fill"></i> Trabajo + estudios</span>
                    <h1>Trabajos que respetan<br><span>tu horario de clases.</span></h1>
                    <p>Marca tus clases una vez y te mostramos solo las ofertas que no chocan con ellas.</p>
                    <div class="portada-botones">
                        <a href="<?php echo e(route('registro')); ?>" class="btn btn-grande"><i class="bi bi-person-plus"></i> Crear mi cuenta gratis</a>
                    </div>
                </div>
            </div>
        </div>

        <a href="#contenido-inicio" class="bajar" aria-label="Bajar al contenido"><span></span></a>

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
        <form class="buscador-flotante" action="<?php echo e(route('habitaciones.index')); ?>" method="GET">
            <div>
                <label for="bDistrito"><i class="bi bi-geo-alt"></i> Distrito</label>
                <select id="bDistrito" name="distrito">
                    <option value="">Todos los distritos</option>
                    <?php $__currentLoopData = $distritos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option><?php echo e($d); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <?php $__currentLoopData = \App\Models\Habitacion::TIPOS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $texto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($valor); ?>"><?php echo e($texto); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="btn btn-grande"><i class="bi bi-search"></i> Buscar</button>
        </form>
    </div>

    <!-- cinta que se desliza sola -->
    <div class="cinta" aria-label="Universidades y ventajas">
        <div class="cinta-pista">
            <?php $__currentLoopData = [1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vuelta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="cinta-grupo" <?php if($vuelta === 2): ?> aria-hidden="true" <?php endif; ?>>
                    <?php $__currentLoopData = $universidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span><i class="bi bi-mortarboard"></i> <?php echo e($u['nombre']); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <span><i class="bi bi-patch-check"></i> Cuartos verificados</span>
                    <span><i class="bi bi-house-heart"></i> Visita antes de pagar</span>
                    <span><i class="bi bi-calendar2-week"></i> Trabajos según tu horario</span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <main class="sin-espacio" id="contenido-inicio">
        <!-- numeros -->
        <section class="seccion-numeros">
            <div class="contenedor">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-3 revelar">
                        <b class="contador" data-hasta="<?php echo e($numeros['habitaciones']); ?>"><?php echo e($numeros['habitaciones']); ?></b>
                        <span>habitaciones publicadas</span>
                    </div>
                    <div class="col-6 col-md-3 revelar">
                        <b class="contador" data-hasta="<?php echo e($numeros['trabajos']); ?>"><?php echo e($numeros['trabajos']); ?></b>
                        <span>ofertas de trabajo</span>
                    </div>
                    <div class="col-6 col-md-3 revelar">
                        <b class="contador" data-hasta="<?php echo e($numeros['verificados']); ?>"><?php echo e($numeros['verificados']); ?></b>
                        <span>cuentas verificadas</span>
                    </div>
                    <div class="col-6 col-md-3 revelar">
                        <b class="contador" data-hasta="<?php echo e($numeros['distritos']); ?>"><?php echo e($numeros['distritos']); ?></b>
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
                    <h2>¿Cómo <em>funciona</em>?</h2>
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
                            <img src="<?php echo e(asset('img/habitaciones/h5.jpg')); ?>" alt="Habitación iluminada con escritorio" class="collage-grande">
                            <img src="<?php echo e(asset('img/cusco/cuesta-san-blas.jpg')); ?>" alt="Cuesta de San Blas en Cusco" class="collage-chica flotar">
                            <div class="globo-flotante globo-1 flotar-lento">
                                <i class="bi bi-patch-check-fill"></i>
                                <div><b>Arrendador verificado</b><small>Revisamos su DNI</small></div>
                            </div>
                            <?php if($numeros['promedio']): ?>
                                <div class="globo-flotante globo-2 flotar">
                                    <i class="bi bi-star-fill"></i>
                                    <div><b><?php echo e(number_format($numeros['promedio'], 1)); ?> de 5</b><small>en reseñas de visitas</small></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6 revelar">
                        <span class="sobretitulo">Por qué TAMPU</span>
                        <h2 class="titulo-grande">Un lugar seguro para <em>empezar</em> en Cusco</h2>
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
                    <h2>Cuartos cerca de <em>donde estudias</em></h2>
                </div>
                <div class="row g-3">
                    <?php $__currentLoopData = $universidades->where('tipo', 'universidad'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-6 col-lg revelar">
                            
                            <a href="<?php echo e($u['total'] > 0 ? route('habitaciones.index', ['cerca_de' => $u['clave']]) : route('habitaciones.index')); ?>" class="tarjeta-uni">
                                <i class="bi bi-mortarboard"></i>
                                <b><?php echo e($u['nombre']); ?></b>
                                <small><?php echo e($u['lugar']); ?></small>
                                <span><?php if($u['total'] > 0): ?><?php echo e($u['total']); ?> <?php echo e($u['total'] === 1 ? 'cuarto' : 'cuartos'); ?><?php else: ?> Ver cuartos <?php endif; ?> <i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- institutos: agrupados por tipo, como chips -->
                <div class="institutos revelar">
                    <p class="institutos-titulo"><i class="bi bi-buildings"></i> ¿Estudias en un instituto? También te buscamos cuarto cerca</p>
                    <?php $__currentLoopData = \App\Support\Lugares::TIPOS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo => $titulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($tipo === 'universidad') continue; ?>
                        <div class="institutos-grupo">
                            <span class="institutos-tipo"><?php echo e($titulo); ?></span>
                            <div class="institutos-lista">
                                <?php $__currentLoopData = $universidades->where('tipo', $tipo); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e($u['total'] > 0 ? route('habitaciones.index', ['cerca_de' => $u['clave']]) : route('habitaciones.index')); ?>" class="chip-instituto" title="<?php echo e($u['completo']); ?>">
                                        <?php echo e($u['nombre']); ?>

                                        <?php if($u['total'] > 0): ?><em><?php echo e($u['total']); ?></em><?php endif; ?>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- barrios de cusco -->
        <section class="seccion fondo-blanco">
            <div class="contenedor">
                <div class="encabezado-seccion con-flechas revelar">
                    <div>
                        <span class="sobretitulo">Barrios de Cusco</span>
                        <h2>¿Dónde te <em>gustaría vivir</em>?</h2>
                    </div>
                    <a href="<?php echo e(route('habitaciones.index')); ?>" class="btn btn-borde">Ver todos <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="barrios">
                    <?php $__currentLoopData = $barrios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('habitaciones.index', ['distrito' => $b['nombre']])); ?>" class="barrio revelar" style="--fondo-img: url('<?php echo e($b['foto']); ?>')">
                            <span class="barrio-numero"><?php echo e(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)); ?></span>
                            <div class="barrio-texto">
                                <h3><?php echo e($b['nombre']); ?></h3>
                                <p><?php echo e($b['texto']); ?></p>
                                <span class="barrio-datos"><?php echo e($b['total']); ?> <?php echo e($b['total'] == 1 ? 'cuarto' : 'cuartos'); ?> · desde S/ <?php echo e(number_format($b['desde'], 0)); ?> <i class="bi bi-arrow-up-right"></i></span>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- mapa con todos los cuartos -->
        <section class="seccion">
            <div class="contenedor">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-4 revelar">
                        <span class="sobretitulo">Mira dónde quedan</span>
                        <h2 class="titulo-grande">Todos los cuartos <em>en un mapa</em></h2>
                        <p class="texto-suave">Cada marcador muestra el precio. Los íconos de birrete son las universidades, para que veas qué tan cerca estarías.</p>
                        <div class="distritos-lista">
                            <?php $__currentLoopData = $distritos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(isset($porDistrito[$d])): ?>
                                    <a href="<?php echo e(route('habitaciones.index', ['distrito' => $d])); ?>">
                                        <span><?php echo e($d); ?></span>
                                        <small><?php echo e($porDistrito[$d]->total); ?> · desde S/ <?php echo e(number_format($porDistrito[$d]->desde, 0)); ?></small>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <div class="col-lg-8 revelar">
                        <div id="mapa" class="mapa mapa-portada" data-puntos='<?php echo json_encode($puntos, 15, 512) ?>'></div>
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
                        <h2>Habitaciones <em>recientes</em></h2>
                    </div>
                    <div class="flechas">
                        <button type="button" class="flecha" data-carril="#carrilCuartos" data-dir="-1" aria-label="Anteriores"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="flecha" data-carril="#carrilCuartos" data-dir="1" aria-label="Siguientes"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                <div class="carril" id="carrilCuartos">
                    <?php $__currentLoopData = $destacadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="carril-item">
                            <?php echo $__env->make('partials.tarjeta-habitacion', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <p class="centrado"><a href="<?php echo e(route('habitaciones.index')); ?>" class="btn btn-borde">Ver todas las habitaciones <i class="bi bi-arrow-right"></i></a></p>
            </div>
        </section>

        <!-- trabajos -->
        <section class="seccion fondo-blanco">
            <div class="contenedor">
                <div class="encabezado-seccion revelar">
                    <span class="sobretitulo">Para tus horas libres</span>
                    <h2>Trabajos <em>recientes</em></h2>
                </div>
                <div class="row g-4">
                    <?php $__currentLoopData = $trabajos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4 revelar">
                            <?php echo $__env->make('partials.tarjeta-trabajo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <p class="centrado"><a href="<?php echo e(route('trabajos.index')); ?>" class="btn btn-borde">Ver todos los trabajos <i class="bi bi-arrow-right"></i></a></p>
            </div>
        </section>

        <!-- linea de tiempo: de los tampus incas a hoy -->
        <section class="seccion seccion-historia">
            <div class="contenedor">
                <div class="encabezado-seccion revelar">
                    <span class="sobretitulo">De dónde viene el nombre</span>
                    <h2>Del Qhapaq Ñan <em>a tu primer cuarto</em></h2>
                </div>
                <ol class="linea-historia">
                    <li class="revelar">
                        <span class="hito-fecha">Siglo XV</span>
                        <i class="bi bi-signpost-split"></i>
                        <h3>El camino inca</h3>
                        <p>El Qhapaq Ñan unía el Tawantinsuyu con miles de kilómetros de caminos que salían desde Cusco.</p>
                    </li>
                    <li class="revelar">
                        <span class="hito-fecha">En el camino</span>
                        <i class="bi bi-house-door"></i>
                        <h3>Los tampus</h3>
                        <p>A lo largo de la ruta había tampus: casas de descanso con techo, comida y depósitos para el viajero.</p>
                    </li>
                    <li class="revelar">
                        <span class="hito-fecha">2014</span>
                        <i class="bi bi-globe-americas"></i>
                        <h3>Patrimonio mundial</h3>
                        <p>La UNESCO declaró al Qhapaq Ñan Patrimonio de la Humanidad, compartido por seis países.</p>
                    </li>
                    <li class="revelar">
                        <span class="hito-fecha">Hoy</span>
                        <i class="bi bi-phone"></i>
                        <h3>TAMPU</h3>
                        <p>Una casa de paso digital para el estudiante que llega a Cusco: cuarto seguro y trabajo que respeta sus clases.</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- opiniones en carrusel -->
        <?php if($resenas->isNotEmpty()): ?>
            <section class="seccion seccion-opiniones">
                <div class="contenedor">
                    <div class="encabezado-seccion revelar">
                        <span class="sobretitulo">Después de la visita</span>
                        <h2>Lo que dicen <em>los estudiantes</em></h2>
                    </div>
                    <div id="opiniones" class="carousel slide revelar" data-bs-ride="carousel" data-bs-interval="7000">
                        <div class="carousel-inner">
                            <?php $__currentLoopData = $resenas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="carousel-item <?php echo e($loop->first ? 'active' : ''); ?>">
                                    <figure class="opinion">
                                        <i class="bi bi-quote opinion-comillas"></i>
                                        <p class="estrellas"><?php echo $__env->make('partials.estrellas', ['n' => $r->estrellas], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></p>
                                        <blockquote><?php echo e($r->comentario); ?></blockquote>
                                        <figcaption>
                                            <span class="avatar-letras"><?php echo e($r->estudiante->iniciales()); ?></span>
                                            <span><b><?php echo e($r->estudiante->nombre); ?></b><small>sobre "<?php echo e($r->habitacion->titulo); ?>"</small></span>
                                        </figcaption>
                                    </figure>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="carousel-indicators opiniones-puntos">
                            <?php $__currentLoopData = $resenas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" data-bs-target="#opiniones" data-bs-slide-to="<?php echo e($loop->index); ?>" class="<?php echo e($loop->first ? 'active' : ''); ?>" aria-label="Opinión <?php echo e($loop->iteration); ?>"></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- vivir en cusco: fotos en mosaico y consejos para quien llega -->
        <section class="seccion fondo-blanco">
            <div class="contenedor">
                <div class="row g-4 g-lg-5 align-items-center">
                    <div class="col-lg-5 revelar">
                        <span class="sobretitulo">Tu nueva ciudad</span>
                        <h2 class="titulo-grande">Cusco te recibe, <em>nosotros te orientamos</em></h2>
                        <p class="texto-suave">Llegar de provincia a una ciudad nueva es más fácil cuando alguien te cuenta lo básico. Esto es lo que les decimos a todos los que recién llegan.</p>
                        <ol class="consejos">
                            <li><b>La altura</b><span>Cusco está a unos 3 400 m. Los primeros días camina despacio, come ligero y toma bastante agua.</span></li>
                            <li><b>El frío de la noche</b><span>De día hace sol, pero las noches son frías casi todo el año. Pregunta si el cuarto tiene agua caliente.</span></li>
                            <li><b>Visita antes de pagar</b><span>Nunca mandes un adelanto por un cuarto que no viste. En TAMPU la visita se pide desde la página.</span></li>
                        </ol>
                    </div>
                    <div class="col-lg-7 revelar">
                        <div class="mosaico">
                            <figure class="mosaico-alto">
                                <img src="<?php echo e(asset('img/cusco/plaza.jpg')); ?>" alt="Plaza de Armas del Cusco" loading="lazy">
                                <figcaption>Plaza de Armas</figcaption>
                            </figure>
                            <figure>
                                <img src="<?php echo e(asset('img/cusco/san-blas.jpg')); ?>" alt="Barrio de San Blas" loading="lazy">
                                <figcaption>San Blas</figcaption>
                            </figure>
                            <figure>
                                <img src="<?php echo e(asset('img/cusco/cuesta-san-blas.jpg')); ?>" alt="Cuesta de San Blas" loading="lazy">
                                <figcaption>Cuesta de San Blas</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- preguntas frecuentes -->
        <section class="seccion">
            <div class="contenedor">
                <div class="row g-4 g-lg-5">
                    <div class="col-lg-4 revelar">
                        <span class="sobretitulo">Preguntas frecuentes</span>
                        <h2 class="titulo-grande">Lo que todos <em>preguntan</em></h2>
                        <p class="texto-suave">Si tu duda no está aquí, escríbenos o pregúntale al asistente del botón de abajo.</p>
                        <a href="<?php echo e(route('contacto')); ?>" class="btn btn-borde mt-3">Escríbenos <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="col-lg-8 revelar">
                        <div class="accordion preguntas" id="preguntas">
                            <?php $__currentLoopData = [
                                ['¿Usar TAMPU cuesta algo?', 'No. Buscar cuartos, postular a trabajos y publicar es gratis para estudiantes, arrendadores y empresas.'],
                                ['¿Cómo sé que el arrendador es real?', 'Antes de publicar, el administrador revisa el DNI o RUC de cada cuenta. Solo las cuentas verificadas aparecen en la página.'],
                                ['¿Qué hago si me piden un adelanto sin ver el cuarto?', 'No lo pagues. Pide la visita desde la página y usa el botón "Reportar" en la publicación. El administrador lo revisa.'],
                                ['¿Cómo funciona el filtro por horario?', 'Marcas en tu panel los turnos en los que tienes clases. En Trabajos puedes ver solo las ofertas que no se cruzan con ellos.'],
                                ['¿Cuándo veo el celular del arrendador?', 'Cuando el arrendador acepta tu solicitud de visita. Así tus datos y los suyos quedan protegidos.'],
                                ['¿Puedo instalar TAMPU en mi celular?', 'Sí. Desde el navegador del celular elige "Instalar app" o "Agregar a pantalla de inicio" y se abre como una aplicación.'],
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$pregunta, $respuesta]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <button class="accordion-button <?php echo e($loop->first ? '' : 'collapsed'); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#pregunta<?php echo e($loop->index); ?>" aria-expanded="<?php echo e($loop->first ? 'true' : 'false'); ?>">
                                            <span class="pregunta-numero"><?php echo e(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)); ?></span> <?php echo e($pregunta); ?>

                                        </button>
                                    </h3>
                                    <div id="pregunta<?php echo e($loop->index); ?>" class="accordion-collapse collapse <?php echo e($loop->first ? 'show' : ''); ?>" data-bs-parent="#preguntas">
                                        <div class="accordion-body"><?php echo e($respuesta); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- boletin y app en el celular -->
        <section class="seccion seccion-app" id="boletin">
            <div class="contenedor">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-5 revelar">
                        <div class="celular-escena">
                            <div class="celular">
                                <img src="<?php echo e(asset('img/app-celular.jpg')); ?>" alt="TAMPU abierto en un celular">
                            </div>
                            <div class="globo-flotante globo-app flotar-lento">
                                <i class="bi bi-bell-fill"></i>
                                <div><b>Nuevo cuarto en Wanchaq</b><small>S/ 280 al mes · verificado</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 revelar">
                        <span class="sobretitulo">En tu bolsillo</span>
                        <h2 class="titulo-grande">Lleva TAMPU <em>en tu celular</em></h2>
                        <p class="texto-suave">Instálala como app desde el navegador y recibe los avisos de tus visitas y postulaciones. Si quieres, también te avisamos por correo cuando haya cuartos nuevos.</p>
                        <form action="<?php echo e(route('boletin')); ?>" method="POST" class="form-boletin">
                            <?php echo csrf_field(); ?>
                            <label for="correoBoletin" class="visually-hidden">Tu correo</label>
                            <input type="email" id="correoBoletin" name="email" placeholder="tu correo electrónico" required maxlength="100">
                            <button type="submit" class="btn">Avísame <i class="bi bi-arrow-right"></i></button>
                        </form>
                        <?php echo $__env->make('partials.errores', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <div class="app-ventajas">
                            <span><i class="bi bi-lightning-charge"></i> Abre al instante</span>
                            <span><i class="bi bi-bell"></i> Avisos de visitas</span>
                            <span><i class="bi bi-wifi-off"></i> Funciona con mala señal</span>
                        </div>
                        <button type="button" class="btn btn-borde mt-4 oculto" data-instalar><i class="bi bi-phone"></i> Instalar la app</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- llamado para arrendadores y empresas -->
        <section class="banda-cta" style="--fondo-img: url('<?php echo e(asset('img/cusco/catedral.jpg')); ?>')">
            <div class="contenedor revelar">
                <h2>¿Tienes un cuarto o un trabajo <em>para estudiantes?</em></h2>
                <p>Publica gratis. Verificamos tu cuenta y los estudiantes te escriben directamente.</p>
                <a href="<?php echo e(route('registro')); ?>" class="btn btn-grande btn-claro">Publicar gratis</a>
            </div>
        </section>
    </main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?php echo e(asset('js/mapa.js')); ?>?v=<?php echo e(filemtime(public_path('js/mapa.js'))); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/inicio.blade.php ENDPATH**/ ?>