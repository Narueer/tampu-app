
<figure class="modelo-3d <?php echo e($clase ?? ''); ?>" style="--poster: url('<?php echo e(asset($poster)); ?>')">
    <model-viewer
        src="<?php echo e(asset($modelo)); ?>"
        poster="<?php echo e(asset($poster)); ?>"
        alt="<?php echo e($alt); ?>"
        loading="lazy"
        reveal="auto"
        camera-controls
        disable-zoom
        touch-action="pan-y"
        auto-rotate
        auto-rotate-delay="2500"
        rotation-per-second="14deg"
        interaction-prompt="none"
        camera-orbit="<?php echo e($orbita ?? '35deg 62deg auto'); ?>"
        min-camera-orbit="auto 25deg auto"
        max-camera-orbit="auto 85deg auto"
        shadow-intensity="1.1"
        shadow-softness="0.8"
        exposure="1.05"
        tone-mapping="aces"
        ar
        ar-modes="webxr scene-viewer quick-look">
        <?php $__currentLoopData = $puntos ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => [$pos, $titulo, $texto]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button class="punto-3d" slot="hotspot-<?php echo e($i); ?>" data-position="<?php echo e($pos); ?>" data-visibility-attribute="visible"
                    aria-label="<?php echo e($titulo); ?>: <?php echo e($texto); ?>">
                <span class="punto-3d-num"><?php echo e($i + 1); ?></span>
                <span class="punto-3d-globo"><b><?php echo e($titulo); ?></b><?php echo e($texto); ?></span>
            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <button slot="ar-button" class="boton-ar"><i class="bi bi-phone"></i> Ver en tu espacio</button>
        <div class="modelo-cargando" slot="progress-bar"><span></span></div>
    </model-viewer>
    
    <div class="modelo-info" aria-live="polite" hidden></div>
    <span class="modelo-pista"><i class="bi bi-arrow-repeat"></i> Arrastra para girar · toca los números</span>
</figure>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/modelo-3d.blade.php ENDPATH**/ ?>