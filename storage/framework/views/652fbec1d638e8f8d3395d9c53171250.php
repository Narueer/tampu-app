<?php $__env->startSection('titulo', 'Contacto'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <h1 class="titulo-pagina">Contacto</h1>

        <div class="contacto">
            <form class="caja" action="<?php echo e(route('contacto')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <h2>Escríbenos</h2>
                <p class="texto-suave">¿Tienes una duda, un problema con tu cuenta o quieres avisarnos de una estafa? Déjanos tu mensaje y te respondemos a tu correo.</p>

                <?php echo $__env->make('partials.errores', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="fila">
                    <div>
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo e(old('nombre', auth()->user()->nombre ?? '')); ?>" maxlength="100" required>
                    </div>
                    <div>
                        <label for="email">Correo</label>
                        <input type="email" id="email" name="email" value="<?php echo e(old('email', auth()->user()->email ?? '')); ?>" maxlength="100" required>
                    </div>
                </div>

                <label for="asunto">Asunto</label>
                <select id="asunto" name="asunto" required>
                    <?php $__currentLoopData = \App\Models\Contacto::ASUNTOS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option <?php if(old('asunto') === $a): echo 'selected'; endif; ?>><?php echo e($a); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <label for="mensaje">Mensaje</label>
                <textarea id="mensaje" name="mensaje" rows="5" minlength="10" maxlength="1000" required><?php echo e(old('mensaje')); ?></textarea>

                <button type="submit" class="btn">Enviar mensaje</button>
            </form>

            <aside class="caja datos-contacto">
                <h2>Otros medios</h2>
                <p><i class="bi bi-envelope"></i> <a href="mailto:<?php echo e(config('tampu.correo')); ?>"><?php echo e(config('tampu.correo')); ?></a></p>
                <?php if(config('tampu.whatsapp')): ?>
                    <p><i class="bi bi-whatsapp"></i> <a href="https://wa.me/51<?php echo e(config('tampu.whatsapp')); ?>" target="_blank" rel="noopener"><?php echo e(config('tampu.whatsapp')); ?></a></p>
                <?php endif; ?>
                <p><i class="bi bi-geo-alt"></i> Cusco, Perú</p>
                <p class="aviso">Si alguien te pide un adelanto sin dejarte ver el cuarto, no pagues. Usa el botón "Reportar" de la publicación o escríbenos.</p>
                <p class="texto-suave">También puedes resolver dudas rápidas con el asistente (botón de abajo a la derecha).</p>
            </aside>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/contacto.blade.php ENDPATH**/ ?>