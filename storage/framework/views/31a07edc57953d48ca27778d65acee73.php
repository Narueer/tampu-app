<?php $__env->startSection('titulo', 'Trabajos'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <h1 class="titulo-pagina">Trabajos para estudiantes</h1>
        <p>Ofertas de medio tiempo, por horas y prácticas que puedes combinar con tus clases.</p>

        <form class="barra-filtros" action="<?php echo e(route('trabajos.index')); ?>" method="GET">
            <select name="modalidad" aria-label="Modalidad">
                <option value="">Todas las modalidades</option>
                <option value="medio tiempo" <?php if(request('modalidad') === 'medio tiempo'): echo 'selected'; endif; ?>>Medio tiempo</option>
                <option value="por horas" <?php if(request('modalidad') === 'por horas'): echo 'selected'; endif; ?>>Por horas</option>
                <option value="practicas" <?php if(request('modalidad') === 'practicas'): echo 'selected'; endif; ?>>Prácticas</option>
            </select>

            <?php if(auth()->check() && auth()->user()->rol === 'estudiante'): ?>
                <?php if($clases->isNotEmpty()): ?>
                    <label class="checkbox"><input type="checkbox" name="compatible" value="1" <?php if($compatible): echo 'checked'; endif; ?>> Solo compatibles con mi horario</label>
                <?php else: ?>
                    <span class="texto-suave">Registra tu <a href="<?php echo e(route('estudiante.panel')); ?>">horario de clases</a> para ver los compatibles.</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="texto-suave"><a href="<?php echo e(route('login')); ?>">Inicia sesión</a> como estudiante para filtrar por tu horario.</span>
            <?php endif; ?>

            <button type="submit" class="btn">Filtrar</button>
        </form>

        <p id="total"><?php echo e($trabajos->count()); ?> oferta(s)<?php echo e($compatible ? ' que no chocan con tus clases' : ''); ?></p>

        <?php $__empty_1 = true; $__currentLoopData = $trabajos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('partials.trabajo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="vacio">No hay ofertas con esos filtros.</p>
        <?php endif; ?>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/trabajos/index.blade.php ENDPATH**/ ?>