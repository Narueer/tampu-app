<?php $__env->startSection('titulo', 'Mis favoritos'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <?php echo $__env->make('partials.subnav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <h1 class="titulo-pagina">Mis favoritos</h1>

        <div class="tarjetas">
            <?php $__empty_1 = true; $__currentLoopData = $habitaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('partials.tarjeta-habitacion', ['favoritos' => [$h->id]], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="vacio">No guardaste habitaciones todavía. Toca el corazón en una habitación para guardarla.</p>
            <?php endif; ?>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/estudiante/favoritos.blade.php ENDPATH**/ ?>