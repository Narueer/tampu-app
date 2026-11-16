<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('titulo', 'Inicio'); ?> - TAMPU</title>
    <link rel="icon" href="<?php echo e(asset('img/favicon.png')); ?>">
    <!-- fuente Poppins y Bootstrap Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/estilos.css')); ?>">
    <?php echo $__env->yieldPushContent('estilos'); ?>
</head>
<body class="<?php echo e(request()->routeIs('mensajes.ver') ? 'en-chat' : ''); ?>">

    <!-- menu -->
    <header>
        <div class="contenedor">
            <a href="<?php echo e(route('inicio')); ?>" class="logo">
                <img src="<?php echo e(asset('img/logo-160.png')); ?>" alt="Logo de Tampu">
                TAMPU
            </a>
            <button id="btnMenu" aria-label="Abrir menú">☰</button>
            <nav id="menu">
                <a href="<?php echo e(route('inicio')); ?>" class="<?php echo e(request()->routeIs('inicio') ? 'activo' : ''); ?>">Inicio</a>
                <a href="<?php echo e(route('habitaciones.index')); ?>" class="<?php echo e(request()->routeIs('habitaciones.*') ? 'activo' : ''); ?>">Habitaciones</a>
                <a href="<?php echo e(route('trabajos.index')); ?>" class="<?php echo e(request()->routeIs('trabajos.*') ? 'activo' : ''); ?>">Trabajos</a>
                <a href="<?php echo e(route('nosotros')); ?>" class="<?php echo e(request()->routeIs('nosotros') ? 'activo' : ''); ?>">Nosotros</a>
                <a href="<?php echo e(route('contacto')); ?>" class="<?php echo e(request()->routeIs('contacto') ? 'activo' : ''); ?>">Contacto</a>
                <?php if(auth()->guard()->check()): ?>
                    <?php ($sinLeer = auth()->user()->unreadNotifications()->count()); ?>
                    <?php ($chats = auth()->user()->mensajesSinLeer()); ?>
                    <a href="<?php echo e(route('notificaciones')); ?>" class="icono-menu <?php echo e(request()->routeIs('notificaciones') ? 'activo' : ''); ?>" title="Notificaciones">
                        <i class="bi bi-bell"></i><span class="solo-celular"> Notificaciones</span>
                        <?php if($sinLeer): ?><span class="globo"><?php echo e($sinLeer); ?></span><?php endif; ?>
                    </a>
                    <a href="<?php echo e(route('mensajes')); ?>" class="icono-menu <?php echo e(request()->routeIs('mensajes*') ? 'activo' : ''); ?>" title="Mensajes">
                        <i class="bi bi-chat-dots"></i><span class="solo-celular"> Mensajes</span>
                        <?php if($chats): ?><span class="globo"><?php echo e($chats); ?></span><?php endif; ?>
                    </a>
                    <a href="<?php echo e(route('panel')); ?>" class="<?php echo e(request()->is('estudiante*', 'arrendador*', 'empleador*', 'admin*', 'perfil', 'calendario') ? 'activo' : ''); ?>">
                        <?php if(auth()->user()->foto): ?>
                            <img src="<?php echo e(auth()->user()->fotoUrl()); ?>" alt="" class="avatar-menu">
                        <?php else: ?>
                            <i class="bi bi-person-circle"></i>
                        <?php endif; ?>
                        Mi panel
                    </a>
                    <form action="<?php echo e(route('logout')); ?>" method="POST" class="form-salir">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-borde btn-chico">Salir</button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="<?php echo e(request()->routeIs('login', 'registro') ? 'activo' : ''); ?>">Iniciar sesión</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <?php if(session('ok') || session('error')): ?>
        <div class="contenedor">
            <?php if(session('ok')): ?>
                <p class="alerta alerta-ok"><i class="bi bi-check-circle"></i> <?php echo e(session('ok')); ?></p>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <p class="alerta alerta-error"><i class="bi bi-exclamation-circle"></i> <?php echo e(session('error')); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('contenido'); ?>

    <!-- footer -->
    <footer>
        <p>
            <a href="<?php echo e(route('inicio')); ?>">Inicio</a>
            <a href="<?php echo e(route('habitaciones.index')); ?>">Habitaciones</a>
            <a href="<?php echo e(route('trabajos.index')); ?>">Trabajos</a>
            <a href="<?php echo e(route('nosotros')); ?>">Nosotros</a>
            <a href="<?php echo e(route('contacto')); ?>">Contacto</a>
        </p>
        <p><i class="bi bi-envelope"></i> <a href="mailto:<?php echo e(config('tampu.correo')); ?>"><?php echo e(config('tampu.correo')); ?></a></p>
        <p>© 2026 TAMPU - Proyecto del curso Programación Web - Universidad Continental</p>
    </footer>

    <?php echo $__env->make('partials.asistente', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script src="<?php echo e(asset('js/script.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/layouts/app.blade.php ENDPATH**/ ?>