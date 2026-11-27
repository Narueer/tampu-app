<div class="tarjeta" data-precio-cuarto="<?php echo e((int) $h->precio); ?>">
    <div class="tarjeta-foto">
        <img src="<?php echo e($h->fotoUrl()); ?>" alt="<?php echo e($h->titulo); ?>" loading="lazy">
        <span class="precio-foto">S/ <?php echo e(number_format($h->precio, 0)); ?><small>/mes</small></span>
        <?php if(($h->fotos_count ?? 0) > 0): ?>
            <span class="cuenta-fotos"><i class="bi bi-images"></i> <?php echo e($h->fotos_count + 1); ?></span>
        <?php endif; ?>
        <?php if(auth()->check() && auth()->user()->rol === 'estudiante'): ?>
            <form action="<?php echo e(route('estudiante.favorito', $h)); ?>" method="POST" class="form-favorito">
                <?php echo csrf_field(); ?>
                <?php ($fav = in_array($h->id, $favoritos ?? [])); ?>
                <button type="submit" class="btn-corazon <?php echo e($fav ? 'marcado' : ''); ?>" aria-label="Guardar en favoritos">
                    <i class="bi <?php echo e($fav ? 'bi-heart-fill' : 'bi-heart'); ?>"></i>
                </button>
            </form>
        <?php endif; ?>
    </div>
    <div class="info" data-presu-aqui>
        <span class="etiqueta verificado">✔ Verificado</span>
        <?php if(($h->confirmadas_count ?? 0) > 0): ?>
            <span class="etiqueta etiqueta-visitado" title="<?php echo e($h->confirmadas_count); ?> <?php echo e($h->confirmadas_count === 1 ? 'estudiante confirmó' : 'estudiantes confirmaron'); ?> que el cuarto es como en las fotos"><i class="bi bi-patch-check-fill"></i> Visitado y confirmado</span>
        <?php endif; ?>
        <span class="etiqueta etiqueta-tipo tipo-<?php echo e($h->tipo); ?>"><i class="bi bi-<?php echo e($h->icono()); ?>"></i> <?php echo e($h->tipoTexto()); ?></span>
        <?php if($h->created_at && $h->created_at->gt(now()->subDays(2))): ?><span class="etiqueta etiqueta-nuevo">Nuevo</span><?php endif; ?>
        <h3><?php echo e($h->titulo); ?></h3>
        <p><i class="bi bi-geo-alt"></i> <?php echo e($h->distrito); ?><?php if($h->cerca_de): ?> - cerca de <?php echo e($h->cerca_de); ?><?php endif; ?></p>
        <?php if($h->metros || $h->bano): ?>
            <p class="datos-tarjeta">
                <?php if($h->metros): ?><span><i class="bi bi-arrows-angle-expand"></i> <?php echo e($h->metros); ?> m²</span><?php endif; ?>
                <?php if($h->bano): ?><span><i class="bi bi-droplet"></i> Baño <?php echo e($h->bano); ?></span><?php endif; ?>
                <?php if($h->piso): ?><span><i class="bi bi-building"></i> <?php echo e($h->piso); ?>° piso</span><?php endif; ?>
            </p>
        <?php endif; ?>
        <?php if($h->resenas_avg_estrellas): ?>
            <p class="estrellas"><?php echo $__env->make('partials.estrellas', ['n' => $h->resenas_avg_estrellas], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <small><?php echo e(number_format($h->resenas_avg_estrellas, 1)); ?></small></p>
        <?php endif; ?>
        <?php if($h->created_at): ?><p class="publicado"><i class="bi bi-clock-history"></i> Publicado <?php echo e($h->created_at->locale('es')->diffForHumans()); ?></p><?php endif; ?>
        <a href="<?php echo e(route('habitaciones.show', $h)); ?>" class="btn btn-borde">Ver detalle</a>
    </div>
</div>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/tarjeta-habitacion.blade.php ENDPATH**/ ?>