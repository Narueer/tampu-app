<div class="trabajo" id="trabajo-<?php echo e($t->id); ?>">
    <div>
        <span class="etiqueta verificado">✔ Verificado</span> <span class="etiqueta"><?php echo e(ucfirst($t->modalidad)); ?></span>
        <h3><?php echo e($t->titulo); ?></h3>
        <p><?php echo e($t->empresa); ?> - <i class="bi bi-geo-alt"></i> <?php echo e($t->distrito); ?></p>
        <p class="texto-suave"><?php echo e($t->descripcion); ?></p>
        <p><i class="bi bi-clock"></i> <?php echo e($t->horarioTexto()); ?></p>
    </div>
    <div class="trabajo-pago">
        <p class="precio">S/ <?php echo e(number_format($t->pago, 0)); ?> <small>por <?php echo e($t->pago_por === 'dia' ? 'día' : $t->pago_por); ?></small></p>

        <?php if(isset($postulados)): ?>
            <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->rol === 'estudiante'): ?>
                    <?php if(isset($postulados[$t->id])): ?>
                        <p>Tu postulación: <?php echo $__env->make('partials.estado', ['estado' => $postulados[$t->id]], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></p>
                    <?php else: ?>
                        <details class="desplegable">
                            <summary class="btn btn-chico">Postular</summary>
                            <form action="<?php echo e(route('estudiante.postular', $t)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <textarea name="mensaje" rows="2" maxlength="300" placeholder="Cuéntale a la empresa por qué te interesa (opcional)"></textarea>
                                <button type="submit" class="btn btn-chico">Enviar postulación</button>
                            </form>
                        </details>
                    <?php endif; ?>
                    <form action="<?php echo e(route('mensajes.iniciar')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="trabajo_id" value="<?php echo e($t->id); ?>">
                        <button type="submit" class="enlace"><i class="bi bi-chat-dots"></i> Enviar mensaje</button>
                    </form>
                <?php endif; ?>
                <?php echo $__env->make('partials.reportar', ['campo' => 'trabajo_id', 'id' => $t->id, 'dueno' => $t->usuario_id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-borde btn-chico">Inicia sesión para postular</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/trabajo.blade.php ENDPATH**/ ?>