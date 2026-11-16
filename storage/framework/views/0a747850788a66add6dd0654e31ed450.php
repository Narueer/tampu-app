<?php $__env->startSection('titulo', 'Calendario'); ?>

<?php $__env->startSection('contenido'); ?>
    <main class="contenedor">
        <?php echo $__env->make('partials.subnav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <h1 class="titulo-pagina">Mi calendario</h1>

        <div class="leyenda">
            <?php if(auth()->user()->rol === 'estudiante'): ?>
                <span><i class="punto" style="background:#1f3d36"></i> Clases</span>
                <span><i class="punto" style="background:#b4532a"></i> Turnos de trabajo</span>
                <span><i class="punto" style="background:#2e7d5b"></i> Visita aceptada</span>
                <span><i class="punto" style="background:#c99a2e"></i> Visita pendiente</span>
            <?php elseif(auth()->user()->rol === 'arrendador'): ?>
                <span><i class="punto" style="background:#2e7d5b"></i> Visita aceptada</span>
                <span><i class="punto" style="background:#c99a2e"></i> Visita pendiente</span>
            <?php else: ?>
                <span><i class="punto" style="background:#b4532a"></i> Turnos de tus ofertas (entre paréntesis, los estudiantes aceptados)</span>
            <?php endif; ?>
        </div>

        <div class="caja">
            <div id="calendario" data-eventos="<?php echo e(route('calendario.eventos')); ?>"></div>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
    <script>
        const divCal = document.getElementById('calendario');
        const enCelular = window.innerWidth < 700;

        const calendario = new FullCalendar.Calendar(divCal, {
            locale: 'es',
            initialView: enCelular ? 'listWeek' : 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: enCelular ? 'listWeek,dayGridMonth' : 'timeGridWeek,dayGridMonth,listWeek'
            },
            slotMinTime: '07:00:00',
            slotMaxTime: '23:00:00',
            allDayText: 'Día',
            height: 'auto',
            events: divCal.dataset.eventos
        });

        calendario.render();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/calendario.blade.php ENDPATH**/ ?>