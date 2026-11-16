<?php ($rol = auth()->user()->rol); ?>
<nav class="subnav">
    <?php if($rol === 'estudiante'): ?>
        <a href="<?php echo e(route('estudiante.panel')); ?>" class="<?php echo e(request()->routeIs('estudiante.panel') ? 'activo' : ''); ?>"><i class="bi bi-grid"></i> Mi panel</a>
        <a href="<?php echo e(route('estudiante.favoritos')); ?>" class="<?php echo e(request()->routeIs('estudiante.favoritos') ? 'activo' : ''); ?>"><i class="bi bi-heart"></i> Favoritos</a>
    <?php elseif($rol === 'arrendador'): ?>
        <a href="<?php echo e(route('arrendador.habitaciones.index')); ?>" class="<?php echo e(request()->routeIs('arrendador.habitaciones.*') ? 'activo' : ''); ?>"><i class="bi bi-house"></i> Mis habitaciones</a>
        <a href="<?php echo e(route('arrendador.solicitudes')); ?>" class="<?php echo e(request()->routeIs('arrendador.solicitudes') ? 'activo' : ''); ?>"><i class="bi bi-envelope"></i> Solicitudes</a>
    <?php elseif($rol === 'empleador'): ?>
        <a href="<?php echo e(route('empleador.trabajos.index')); ?>" class="<?php echo e(request()->routeIs('empleador.trabajos.*') ? 'activo' : ''); ?>"><i class="bi bi-briefcase"></i> Mis ofertas</a>
        <a href="<?php echo e(route('empleador.postulaciones')); ?>" class="<?php echo e(request()->routeIs('empleador.postulaciones') ? 'activo' : ''); ?>"><i class="bi bi-people"></i> Postulaciones</a>
    <?php else: ?>
        <a href="<?php echo e(route('admin.panel')); ?>" class="<?php echo e(request()->routeIs('admin.panel') ? 'activo' : ''); ?>"><i class="bi bi-speedometer2"></i> Panel</a>
        <a href="<?php echo e(route('admin.usuarios')); ?>" class="<?php echo e(request()->routeIs('admin.usuarios') ? 'activo' : ''); ?>"><i class="bi bi-people"></i> Usuarios</a>
        <a href="<?php echo e(route('admin.reportes')); ?>" class="<?php echo e(request()->routeIs('admin.reportes') ? 'activo' : ''); ?>"><i class="bi bi-flag"></i> Reportes</a>
        <a href="<?php echo e(route('admin.contactos')); ?>" class="<?php echo e(request()->routeIs('admin.contactos') ? 'activo' : ''); ?>"><i class="bi bi-envelope"></i> Contacto</a>
    <?php endif; ?>
    <?php if($rol !== 'admin'): ?>
        <a href="<?php echo e(route('calendario')); ?>" class="<?php echo e(request()->routeIs('calendario') ? 'activo' : ''); ?>"><i class="bi bi-calendar3"></i> Calendario</a>
    <?php endif; ?>
    <a href="<?php echo e(route('perfil')); ?>" class="<?php echo e(request()->routeIs('perfil') ? 'activo' : ''); ?>"><i class="bi bi-person-gear"></i> Mi perfil</a>
</nav>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/subnav.blade.php ENDPATH**/ ?>