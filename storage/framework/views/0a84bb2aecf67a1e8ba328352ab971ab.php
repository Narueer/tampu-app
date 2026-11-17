
<section class="banner" style="--fondo-img: url('<?php echo e(asset($imagen ?? 'img/cusco/catedral.jpg')); ?>')">
    <div class="contenedor">
        <nav class="migas" aria-label="Ubicación">
            <a href="<?php echo e(route('inicio')); ?>"><i class="bi bi-house"></i> Inicio</a>
            <i class="bi bi-chevron-right"></i>
            <span><?php echo e($titulo); ?></span>
        </nav>
        <h1><?php echo e($titulo); ?></h1>
        <?php if(isset($texto)): ?><p><?php echo e($texto); ?></p><?php endif; ?>
    </div>
</section>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/banner.blade.php ENDPATH**/ ?>