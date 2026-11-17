<?php $__env->startSection('titulo', 'Habitaciones'); ?>

<?php $__env->startPush('estilos'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('contenido'); ?>
    <?php echo $__env->make('partials.banner', ['titulo' => 'Habitaciones para estudiantes', 'texto' => 'Cuartos verificados cerca de tu universidad. Filtra por distrito y precio, y mira dónde quedan en el mapa.', 'imagen' => 'img/cusco/plaza.jpg'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="contenedor">

        <div class="con-filtros">
            <!-- filtros -->
            <form class="filtros" action="<?php echo e(route('habitaciones.index')); ?>" method="GET">
                <h3>Filtros</h3>
                <label for="distrito">Distrito</label>
                <select id="distrito" name="distrito">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $distritos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option <?php if(request('distrito') === $d): echo 'selected'; endif; ?>><?php echo e($d); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <label for="precioMax">Precio máximo (S/)</label>
                <input type="number" id="precioMax" name="precio_max" value="<?php echo e(request('precio_max')); ?>" placeholder="Ej: 400" min="0">

                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="individual" <?php if(request('tipo') === 'individual'): echo 'selected'; endif; ?>>Individual</option>
                    <option value="compartida" <?php if(request('tipo') === 'compartida'): echo 'selected'; endif; ?>>Compartida</option>
                    <option value="minidepartamento" <?php if(request('tipo') === 'minidepartamento'): echo 'selected'; endif; ?>>Minidepartamento</option>
                </select>

                <label for="cerca">Cerca de</label>
                <input type="text" id="cerca" name="cerca_de" value="<?php echo e(request('cerca_de')); ?>" placeholder="Ej: UNSAAC" list="listaUniversidades">
                <datalist id="listaUniversidades">
                    <?php $__currentLoopData = array_keys(\App\Support\Lugares::UNIVERSIDADES); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($u); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </datalist>

                <label for="orden">Ordenar por precio</label>
                <select id="orden" name="orden">
                    <option value="menor">Menor a mayor</option>
                    <option value="mayor" <?php if(request('orden') === 'mayor'): echo 'selected'; endif; ?>>Mayor a menor</option>
                </select>

                <button type="submit" class="btn">Filtrar</button>
                <a href="<?php echo e(route('habitaciones.index')); ?>" class="limpiar">Limpiar filtros</a>
            </form>

            <!-- lista -->
            <div class="resultados">
                <p id="total"><?php echo e($habitaciones->count()); ?> habitación(es) encontrada(s)</p>
                <?php if($puntos->isNotEmpty()): ?>
                    <div id="mapa" class="mapa" data-puntos='<?php echo json_encode($puntos, 15, 512) ?>'></div>
                <?php endif; ?>
                <div class="tarjetas">
                    <?php $__empty_1 = true; $__currentLoopData = $habitaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('partials.tarjeta-habitacion', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="vacio">No hay habitaciones con esos filtros. Prueba con otro distrito o un precio mayor.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?php echo e(asset('js/mapa.js')); ?>?v=<?php echo e(filemtime(public_path('js/mapa.js'))); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/habitaciones/index.blade.php ENDPATH**/ ?>