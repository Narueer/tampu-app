<?php $__env->startSection('titulo', 'Mi panel'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <?php echo $__env->make('partials.subnav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <h1 class="titulo-pagina">Hola, <?php echo e(auth()->user()->nombre); ?></h1>

        <div class="caja">
            <h2><i class="bi bi-calendar-week"></i> Mi horario de clases</h2>
            <p class="texto-suave">Marca los turnos en los que tienes clases. En Trabajos podrás ver solo las ofertas que no se cruzan con ellos.</p>
            <form action="<?php echo e(route('estudiante.horario')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo $__env->make('partials.horario', ['marcados' => $marcados], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <button type="submit" class="btn">Guardar horario</button>
                <a href="<?php echo e(route('trabajos.index', ['compatible' => 1])); ?>" class="btn btn-borde">Ver trabajos compatibles</a>
                <a href="<?php echo e(route('calendario')); ?>" class="btn btn-borde"><i class="bi bi-calendar3"></i> Ver en el calendario</a>
            </form>
        </div>

        <div class="caja">
            <h2><i class="bi bi-house-door"></i> Mis solicitudes de visita</h2>
            <?php if($solicitudes->isEmpty()): ?>
                <p class="vacio">Aún no solicitaste visitas. <a href="<?php echo e(route('habitaciones.index')); ?>">Busca una habitación</a>.</p>
            <?php else: ?>
                <div class="tabla-scroll">
                    <table class="tabla">
                        <tr>
                            <th>Habitación</th>
                            <th>Fecha de visita</th>
                            <th>Estado</th>
                            <th>Contacto</th>
                            <th>Opciones</th>
                        </tr>
                        <?php $__currentLoopData = $solicitudes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><a href="<?php echo e(route('habitaciones.show', $s->habitacion)); ?>"><?php echo e($s->habitacion->titulo); ?></a></td>
                                <td><?php echo e($s->fecha_visita->format('d/m/Y')); ?></td>
                                <td><?php echo $__env->make('partials.estado', ['estado' => $s->estado], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                <td>
                                    <?php if($s->estado === 'aceptada'): ?>
                                        <?php echo e($s->habitacion->arrendador->nombre); ?>: <b><?php echo e($s->habitacion->arrendador->celular); ?></b>
                                    <?php else: ?>
                                        <span class="texto-suave">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acciones">
                                    <?php if($s->estado === 'aceptada'): ?>
                                        <a href="<?php echo e(route('constancia.visita', $s)); ?>" class="btn btn-borde btn-chico"><i class="bi bi-file-earmark-pdf"></i> Constancia</a>
                                        <?php if($s->fecha_visita->isPast() || $s->fecha_visita->isToday()): ?>
                                            <a href="<?php echo e(route('habitaciones.show', $s->habitacion)); ?>#resenas" class="btn btn-borde btn-chico"><i class="bi bi-star"></i> Reseña</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="caja">
            <h2><i class="bi bi-briefcase"></i> Mis postulaciones</h2>
            <?php if($postulaciones->isEmpty()): ?>
                <p class="vacio">Aún no postulaste a ningún trabajo. <a href="<?php echo e(route('trabajos.index')); ?>">Mira las ofertas</a>.</p>
            <?php else: ?>
                <div class="tabla-scroll">
                    <table class="tabla">
                        <tr>
                            <th>Oferta</th>
                            <th>Horario</th>
                            <th>Estado</th>
                            <th>Contacto</th>
                            <th>Opciones</th>
                        </tr>
                        <?php $__currentLoopData = $postulaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><b><?php echo e($p->trabajo->titulo); ?></b><br><span class="texto-suave"><?php echo e($p->trabajo->empresa); ?></span></td>
                                <td><?php echo e($p->trabajo->horarioTexto()); ?></td>
                                <td><?php echo $__env->make('partials.estado', ['estado' => $p->estado], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                <td>
                                    <?php if($p->estado === 'aceptada'): ?>
                                        <?php echo e($p->trabajo->empleador->nombre); ?>: <b><?php echo e($p->trabajo->empleador->celular); ?></b>
                                    <?php else: ?>
                                        <span class="texto-suave">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acciones">
                                    <?php if($p->estado === 'aceptada'): ?>
                                        <a href="<?php echo e(route('constancia.postulacion', $p)); ?>" class="btn btn-borde btn-chico"><i class="bi bi-file-earmark-pdf"></i> Constancia</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/estudiante/panel.blade.php ENDPATH**/ ?>