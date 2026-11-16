<?php $__env->startSection('titulo', 'Notificaciones'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Notificaciones</h1>
            <?php if(auth()->user()->unreadNotifications()->exists()): ?>
                <form action="<?php echo e(route('notificaciones.leer')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-borde btn-chico"><i class="bi bi-check2-all"></i> Marcar todas como leídas</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="caja lista-avisos">
            <?php $__empty_1 = true; $__currentLoopData = $notificaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('notificaciones.abrir', $n->id)); ?>" class="aviso-item <?php echo e($n->read_at ? '' : 'nuevo'); ?>">
                    <i class="bi bi-<?php echo e($n->data['icono'] ?? 'bell'); ?>"></i>
                    <span><?php echo e($n->data['texto']); ?></span>
                    <small><?php echo e($n->created_at->diffForHumans()); ?></small>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="vacio">No tienes notificaciones.</p>
            <?php endif; ?>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/notificaciones.blade.php ENDPATH**/ ?>