<?php $__env->startSection('titulo', 'Inicio'); ?>

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
                    <?php $__currentLoopData = $universidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-6 col-lg-3 revelar">
                            <a href="<?php echo e(route('habitaciones.index', ['cerca_de' => $u['clave']])); ?>" class="tarjeta-uni">
                                <i class="bi bi-mortarboard"></i>
                                <b><?php echo e($u['nombre']); ?></b>
                                <small><?php echo e($u['lugar']); ?></small>
                                <span><?php echo e($u['total']); ?> <?php echo e($u['total'] === 1 ? 'cuarto' : 'cuartos'); ?> <i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <h2>Habitaciones recientes</h2>
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
                    <h2>Trabajos recientes</h2>
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

        <!-- opiniones en carrusel -->
        <?php if($resenas->isNotEmpty()): ?>
            <section class="seccion seccion-opiniones">
                <div class="contenedor">
                    <div class="encabezado-seccion revelar">
                        <span class="sobretitulo">Después de la visita</span>
                        <h2>Lo que dicen los estudiantes</h2>
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

        <!-- llamado para arrendadores y empresas -->
        <section class="banda-cta" style="--fondo-img: url('<?php echo e(asset('img/cusco/catedral.jpg')); ?>')">
            <div class="contenedor revelar">
                <h2>¿Tienes un cuarto o un trabajo para estudiantes?</h2>
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