<?php
    $clases = [
        'pendiente' => 'est-pendiente', 'aceptada' => 'est-ok', 'rechazada' => 'est-mal',
        'verificado' => 'est-ok', 'rechazado' => 'est-mal', 'no_aplica' => 'est-gris',
        'disponible' => 'est-ok', 'ocupada' => 'est-gris', 'activa' => 'est-ok', 'cerrada' => 'est-gris',
        'descartado' => 'est-gris', 'sancionado' => 'est-mal',
    ];
    $textos = ['no_aplica' => 'No aplica'];
?>
<span class="estado <?php echo e($clases[$estado] ?? 'est-gris'); ?>"><?php echo e($textos[$estado] ?? ucfirst($estado)); ?></span>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/estado.blade.php ENDPATH**/ ?>