<div class="tabla-scroll">
    <table class="horario">
        <tr>
            <th></th>
            <?php $__currentLoopData = \App\Models\Horario::DIAS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $corto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th><?php echo e($corto); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        <?php $__currentLoopData = \App\Models\Horario::TURNOS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $turno => $nombreTurno): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <th><?php echo e(ucfirst($nombreTurno)); ?></th>
                <?php $__currentLoopData = \App\Models\Horario::DIAS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dia => $corto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($valor = $dia . '-' . $turno); ?>
                    <td>
                        <input type="checkbox" name="turnos[]" value="<?php echo e($valor); ?>"
                            aria-label="<?php echo e($corto); ?> <?php echo e($nombreTurno); ?>"
                            <?php if(in_array($valor, old('turnos', $marcados))): echo 'checked'; endif; ?>>
                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>
</div>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/horario.blade.php ENDPATH**/ ?>