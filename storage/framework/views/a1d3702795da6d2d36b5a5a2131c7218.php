
<?php
    $pasos = [
        ['Llegas a Cusco', 'Empiezas con <em>una maleta</em>', 'Y muchas dudas: ¿dónde vivir?, ¿cuánto cuesta?, ¿es seguro? Baja y armamos tu cuarto juntos.'],
        ['Paso 1 · Lo básico', 'Paredes y piso que <em>sí existen</em>', 'Cada cuarto en TAMPU tiene arrendador con DNI revisado, y los estudiantes que lo visitaron confirman que es como en las fotos.'],
        ['Paso 2 · Descanso', 'Una cama <em>abrigada</em>', 'Las noches en Cusco llegan a 2 °C. Filtra por agua caliente, frazadas o baño propio antes de ir a verlo.'],
        ['Paso 3 · Estudio', 'Tu rincón para <em>estudiar</em>', 'Mira a cuántos minutos queda tu universidad y qué líneas de bus pasan cerca. Wifi incluido, si lo necesitas.'],
        ['Listo', 'Tu cuarto, <em>armado</em>', '¿Te alcanza? Arma tu presupuesto en un minuto y te mostramos solo los cuartos que puedes pagar.'],
    ];
?>
<section class="armar" id="armarCuarto" aria-label="Arma tu cuarto en Cusco">
    <div class="armar-escena">
        <span class="armar-numero" aria-hidden="true">01</span>
        <div class="armar-textos">
            <?php $__currentLoopData = $pasos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => [$sobre, $titulo, $texto]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="armar-paso <?php echo e($i === 0 ? 'activo' : ''); ?>" data-paso="<?php echo e($i); ?>">
                    <span class="sobretitulo"><?php echo e($sobre); ?></span>
                    <h2><?php echo $titulo; ?></h2>
                    <p><?php echo e($texto); ?></p>
                    <?php if($loop->last): ?>
                        <a href="<?php echo e(route('calculadora')); ?>" class="btn"><i class="bi bi-wallet2"></i> Armar mi presupuesto</a>
                        <a href="<?php echo e(route('habitaciones.index')); ?>" class="btn btn-borde">Ver cuartos</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="armar-modelo">
            <span class="armar-sombra" aria-hidden="true"></span>
            <model-viewer
                src="<?php echo e(asset('modelos/cuarto-animado.glb')); ?>"
                alt="Maqueta 3D de un cuarto de estudiante que se arma pieza por pieza: piso, paredes, cama, escritorio y mochila"
                loading="lazy"
                animation-name="armar"
                camera-orbit="-10deg 74deg 128%"
                camera-target="0m 1m 0m"
                field-of-view="28deg"
                interpolation-decay="60"
                interaction-prompt="none"
                disable-zoom
                disable-pan
                shadow-intensity="1.2"
                shadow-softness="0.7"
                exposure="1.1"
                tone-mapping="aces"></model-viewer>
        </div>
        <div class="armar-progreso" role="group" aria-label="Etapas">
            <?php $__currentLoopData = $pasos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" data-ir="<?php echo e($i); ?>" aria-label="Ir a la etapa <?php echo e($i + 1); ?>: <?php echo e($p[0]); ?>"><i></i></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <span class="armar-scroll" aria-hidden="true"><i class="bi bi-mouse"></i> Baja para armarlo</span>
    </div>
</section>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/cuarto-scroll.blade.php ENDPATH**/ ?>