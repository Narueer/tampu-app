<?php $__env->startSection('titulo', 'Iniciar sesión'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <form class="formulario" id="formLogin" action="<?php echo e(route('login')); ?>" method="POST" novalidate>
            <?php echo csrf_field(); ?>
            <h2>Iniciar sesión</h2>

            <?php echo $__env->make('partials.errores', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <label for="correo">Correo</label>
            <input type="email" id="correo" name="email" value="<?php echo e(old('email')); ?>" placeholder="correo@ejemplo.com" required>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="password" required>

            <div class="fila-login">
                <label class="checkbox"><input type="checkbox" name="recordar"> Recordarme</label>
                <a href="<?php echo e(route('clave.olvide')); ?>">¿Olvidaste tu contraseña?</a>
            </div>

            <p class="error" id="mensajeLogin"></p>
            <button type="submit" class="btn">Ingresar</button>

            <p>¿No tienes cuenta? <a href="<?php echo e(route('registro')); ?>">Regístrate aquí</a></p>
        </form>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/auth/login.blade.php ENDPATH**/ ?>