
<a href="<?php echo e(route('trabajos.show', $t)); ?>" class="tarjeta-trabajo">
    <span class="logo-empresa"><?php echo e($t->inicialesEmpresa()); ?></span>
    <span class="etiqueta"><?php echo e(ucfirst($t->modalidad)); ?></span>
    <h3><?php echo e($t->titulo); ?></h3>
    <p class="texto-suave"><?php echo e($t->empresa); ?> · <i class="bi bi-geo-alt"></i> <?php echo e($t->distrito); ?></p>
    <p class="tarjeta-trabajo-horario"><i class="bi bi-clock"></i> <?php echo e($t->horarioTexto()); ?></p>
    <p class="precio">S/ <?php echo e(number_format($t->pago, 0)); ?> <small>por <?php echo e($t->pago_por === 'dia' ? 'día' : $t->pago_por); ?></small></p>
</a>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/tarjeta-trabajo.blade.php ENDPATH**/ ?>