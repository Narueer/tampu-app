<?php $__env->startSection('titulo', 'Mensajes de contacto'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <?php echo $__env->make('partials.subnav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <h1 class="titulo-pagina">Mensajes de contacto</h1>

        <?php if($contactos->isEmpty()): ?>
            <p class="vacio">Todavía no llegaron mensajes.</p>
        <?php else: ?>
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>De</th>
                        <th>Asunto</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                    <?php $__currentLoopData = $contactos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($c->leido ? '' : 'fila-nueva'); ?>">
                            <td><b><?php echo e($c->nombre); ?></b><br><a href="mailto:<?php echo e($c->email); ?>?subject=Re: <?php echo e($c->asunto); ?>"><?php echo e($c->email); ?></a></td>
                            <td><?php echo e($c->asunto); ?></td>
                            <td><?php echo e($c->mensaje); ?></td>
                            <td><?php echo e($c->created_at->format('d/m/Y H:i')); ?></td>
                            <td class="acciones">
                                <form action="<?php echo e(route('admin.contactos.leer', $c)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button class="btn btn-borde btn-chico"><?php echo e($c->leido ? 'Marcar no leído' : 'Marcar leído'); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </table>
            </div>
        <?php endif; ?>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/admin/contactos.blade.php ENDPATH**/ ?>