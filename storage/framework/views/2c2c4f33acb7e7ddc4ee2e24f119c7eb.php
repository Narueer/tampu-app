
<?php ($cual = $cual ?? 'visitante'); ?>
<?php if(file_exists(public_path("videos/guia-$cual.mp4"))): ?>
    <button type="button" class="<?php echo e($clase ?? 'btn btn-borde'); ?>" data-bs-toggle="modal" data-bs-target="#videoGuia">
        <i class="bi bi-play-circle"></i> <?php echo e($texto ?? 'Ver la guía en video'); ?>

    </button>
    <?php if (! $__env->hasRenderedOnce('66a5b1f7-dbd8-4e7c-b4b8-e04847570c5a')): $__env->markAsRenderedOnce('66a5b1f7-dbd8-4e7c-b4b8-e04847570c5a'); ?>
        <?php $__env->startPush('modales'); ?>
            <div class="modal fade" id="videoGuia" tabindex="-1" aria-label="Video guía" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content video-guia">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        <video controls playsinline preload="none" poster="<?php echo e(asset("videos/guia-$cual.webp")); ?>">
                            <source src="<?php echo e(asset("videos/guia-$cual.mp4")); ?>" type="video/mp4">
                        </video>
                    </div>
                </div>
            </div>
            <script>
                // el video empieza al abrir la ventana y se detiene al cerrarla
                document.addEventListener('DOMContentLoaded', () => {
                    const m = document.getElementById('videoGuia'); const v = m.querySelector('video');
                    m.addEventListener('shown.bs.modal', () => v.play().catch(() => {}));
                    m.addEventListener('hide.bs.modal', () => v.pause());
                });
            </script>
        <?php $__env->stopPush(); ?>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/video-guia.blade.php ENDPATH**/ ?>