<?php for($i = 1; $i <= 5; $i++): ?>
    <i class="bi <?php echo e($n >= $i ? 'bi-star-fill' : ($n >= $i - 0.5 ? 'bi-star-half' : 'bi-star')); ?>"></i>
<?php endfor; ?>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/estrellas.blade.php ENDPATH**/ ?>