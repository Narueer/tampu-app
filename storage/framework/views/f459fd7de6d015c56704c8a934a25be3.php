<?php $__env->startSection('titulo', 'Mi perfil'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <?php echo $__env->make('partials.subnav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <h1 class="titulo-pagina">Mi perfil</h1>
        <?php echo $__env->make('partials.errores', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="perfil">
            <form class="caja" action="<?php echo e(route('perfil')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <h2><i class="bi bi-person"></i> Mis datos</h2>

                <div class="perfil-foto">
                    <?php if($usuario->foto): ?>
                        <img src="<?php echo e($usuario->fotoUrl()); ?>" alt="Mi foto" id="vistaPrevia" class="avatar-grande">
                    <?php else: ?>
                        <span class="avatar-grande avatar-letras"><?php echo e($usuario->iniciales()); ?></span>
                        <img src="" alt="Vista previa" id="vistaPrevia" class="avatar-grande oculto">
                    <?php endif; ?>
                    <div>
                        <label for="foto">Foto de perfil</label>
                        <input type="file" id="foto" name="foto" accept="image/*">
                        <p class="texto-suave">JPG o PNG, máximo 2 MB.</p>
                    </div>
                </div>

                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo e(old('nombre', $usuario->nombre)); ?>" required>

                <div class="fila">
                    <div>
                        <label>Correo</label>
                        <input type="email" value="<?php echo e($usuario->email); ?>" disabled>
                    </div>
                    <div>
                        <label>DNI</label>
                        <input type="text" value="<?php echo e($usuario->dni); ?>" disabled>
                    </div>
                </div>

                <div class="fila">
                    <div>
                        <label for="celular">Celular</label>
                        <input type="text" id="celular" name="celular" value="<?php echo e(old('celular', $usuario->celular)); ?>" maxlength="9">
                    </div>
                    <?php if($usuario->rol === 'estudiante'): ?>
                        <div>
                            <label for="procedencia">¿De dónde eres?</label>
                            <input type="text" id="procedencia" name="procedencia" value="<?php echo e(old('procedencia', $usuario->procedencia)); ?>" maxlength="60">
                        </div>
                    <?php endif; ?>
                </div>

                <p class="texto-suave">Cuenta de <?php echo e($usuario->rol); ?> · <?php echo $__env->make('partials.estado', ['estado' => $usuario->verificacion], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></p>
                <button type="submit" class="btn">Guardar cambios</button>
            </form>

            <form class="caja" action="<?php echo e(route('perfil.clave')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <h2><i class="bi bi-key"></i> Cambiar contraseña</h2>
                <label for="actual">Contraseña actual</label>
                <input type="password" id="actual" name="actual" required>
                <label for="nueva">Nueva contraseña</label>
                <input type="password" id="nueva" name="password" minlength="6" required>
                <label for="nueva2">Repite la nueva contraseña</label>
                <input type="password" id="nueva2" name="password_confirmation" minlength="6" required>
                <button type="submit" class="btn">Cambiar contraseña</button>
            </form>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/perfil.blade.php ENDPATH**/ ?>