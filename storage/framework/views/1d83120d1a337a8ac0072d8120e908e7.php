<?php $__env->startSection('titulo', 'Nosotros'); ?>

<?php $__env->startSection('contenido'); ?>
    <?php echo $__env->make('partials.banner', ['titulo' => 'Sobre TAMPU', 'texto' => 'Un lugar de descanso en el camino, como los tampus del Qhapaq Ñan.', 'imagen' => 'img/cusco/catedral.jpg'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="contenedor">
        <!-- historia con fotos flotantes -->
        <section class="seccion">
            <div class="row g-4 g-lg-5 align-items-center">
                <div class="col-lg-6 revelar">
                    <span class="sobretitulo">Nuestra historia</span>
                    <h2 class="titulo-grande">¿Por qué se llama TAMPU?</h2>
                    <p>En la época inca, los <b>tampus</b> eran lugares de descanso en el Qhapaq Ñan donde los viajeros encontraban techo y alimento. Cada año llegan a Cusco muchos jóvenes de provincias para estudiar y lo primero que tienen que resolver es dónde vivir y cómo mantenerse.</p>
                    <p class="mt-3">Sin conocer la ciudad, buscan cuartos por Facebook o carteles y quedan expuestos a adelantos que se pierden y a trabajos que chocan con sus clases. TAMPU quiere ser para ellos lo que eran los tampus: un lugar seguro para empezar.</p>
                </div>
                <div class="col-lg-6 revelar">
                    <div class="collage">
                        <img src="<?php echo e(asset('img/cusco/plaza.jpg')); ?>" alt="Plaza de Armas del Cusco" class="collage-grande">
                        <img src="<?php echo e(asset('img/habitaciones/h3.jpg')); ?>" alt="Habitación para estudiantes" class="collage-chica flotar">
                        <div class="globo-flotante globo-1 flotar-lento">
                            <i class="bi bi-mortarboard-fill"></i>
                            <div><b>Hecho por estudiantes</b><small>para estudiantes</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- mision, vision y valores -->
        <section class="seccion pt-0">
            <div class="row g-4">
                <div class="col-md-4 revelar">
                    <div class="tarjeta-valor">
                        <i class="bi bi-bullseye"></i>
                        <h3>Misión</h3>
                        <p>Conectar a estudiantes con habitaciones y trabajos verificados.</p>
                    </div>
                </div>
                <div class="col-md-4 revelar">
                    <div class="tarjeta-valor destacada">
                        <i class="bi bi-eye"></i>
                        <h3>Visión</h3>
                        <p>Que ningún estudiante deje sus estudios por no encontrar dónde vivir.</p>
                    </div>
                </div>
                <div class="col-md-4 revelar">
                    <div class="tarjeta-valor">
                        <i class="bi bi-heart"></i>
                        <h3>Valores</h3>
                        <p>Confianza, respeto por el tiempo de estudio y cero adelantos a ciegas.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- equipo -->
        <section class="seccion pt-0">
            <div class="encabezado-seccion revelar">
                <span class="sobretitulo">Curso Programación Web · Universidad Continental</span>
                <h2>Nuestro equipo</h2>
            </div>
            <div class="row g-4">
                <?php $__currentLoopData = ['Wernher Alvares Rodriguez', 'César Augusto Juro Huillca', 'David Achahuanco Bayona', 'Olenka Valeria Aedo Vargas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nombre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($partes = explode(' ', $nombre)); ?>
                    <div class="col-6 col-lg-3 revelar">
                        <div class="tarjeta-equipo">
                            <span class="avatar-equipo"><?php echo e(mb_substr($partes[0], 0, 1)); ?><?php echo e(mb_substr($partes[count($partes) - 2], 0, 1)); ?></span>
                            <h3><?php echo e($nombre); ?></h3>
                            <p>Integrante del equipo</p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <p class="creditos-fotos">Fotos de Cusco: Pavel Špindler (CC BY 3.0), Bernard Brönn y Alexey Komarov (CC BY-SA 4.0) y Pedro Szekely (CC BY-SA 2.0), vía Wikimedia Commons. Fotos de habitaciones: Unsplash y Openverse (CC0, CC BY y CC BY-SA). Ubicaciones: © colaboradores de OpenStreetMap.</p>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/nosotros.blade.php ENDPATH**/ ?>