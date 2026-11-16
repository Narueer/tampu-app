<?php if($errors->any()): ?>
    <div class="alerta alerta-error">
        <b>Revisa lo siguiente:</b>
        <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/errores.blade.php ENDPATH**/ ?>