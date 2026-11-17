<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('titulo', 'Inicio'); ?> - TAMPU</title>
    <link rel="icon" href="<?php echo e(asset('img/favicon.png')); ?>">
    <!-- para instalarla como app en el celular -->
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="theme-color" content="#17352f">
    <link rel="apple-touch-icon" href="<?php echo e(asset('img/apple-touch-icon.png')); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="TAMPU">
    <!-- fuentes Fraunces y Plus Jakarta Sans, Bootstrap 5 y Bootstrap Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/estilos.css')); ?>?v=<?php echo e(filemtime(public_path('css/estilos.css'))); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/tema.css')); ?>?v=<?php echo e(filemtime(public_path('css/tema.css'))); ?>">
    <script>document.documentElement.classList.add('con-js');</script>
    <?php echo $__env->yieldPushContent('estilos'); ?>
</head>
<body class="<?php echo e(request()->routeIs('mensajes.ver') ? 'en-chat' : ''); ?>">

    <!-- menu -->
    <header id="cabecera">
        <div class="contenedor">
            <a href="<?php echo e(route('inicio')); ?>" class="logo">
                <img src="<?php echo e(asset('img/logo-160.png')); ?>" alt="Logo de Tampu">
                <span>Tampu<small>Cusco · estudiantes</small></span>
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
                    <a href="<?php echo e(route('notificaciones')); ?>" id="iconoAvisos" class="icono-menu <?php echo e(request()->routeIs('notificaciones') ? 'activo' : ''); ?>" title="Notificaciones">
                        <i class="bi bi-bell"></i><span class="solo-celular"> Notificaciones</span>
                        <?php if($sinLeer): ?><span class="globo"><?php echo e($sinLeer); ?></span><?php endif; ?>
                    </a>
                    <a href="<?php echo e(route('mensajes')); ?>" id="iconoChats" class="icono-menu <?php echo e(request()->routeIs('mensajes*') ? 'activo' : ''); ?>" title="Mensajes">
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
                    <a href="<?php echo e(route('login')); ?>" class="menu-cta <?php echo e(request()->routeIs('login', 'registro') ? 'activo' : ''); ?>">Iniciar sesión</a>
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
    <div class="banda-andina" aria-hidden="true"></div>
    <footer class="pie">
        <div class="contenedor">
            <p class="pie-lema">Tu casa de paso <em>en el camino</em><br>hacia la universidad.</p>
            <div class="row g-4 pie-columnas">
                <div class="col-lg-4 col-md-6">
                    <a href="<?php echo e(route('inicio')); ?>" class="logo logo-pie">
                        <img src="<?php echo e(asset('img/logo-160.png')); ?>" alt="Logo de Tampu"> Tampu
                    </a>
                    <p class="pie-texto">Habitaciones seguras y trabajos que encajan con tus clases, para estudiantes que llegan a Cusco.</p>
                    <button type="button" id="btnInstalar" class="btn-instalar oculto"><i class="bi bi-phone"></i> Instalar la app</button>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h4>Explora</h4>
                    <a href="<?php echo e(route('habitaciones.index')); ?>">Habitaciones</a>
                    <a href="<?php echo e(route('trabajos.index')); ?>">Trabajos</a>
                    <a href="<?php echo e(route('nosotros')); ?>">Nosotros</a>
                    <a href="<?php echo e(route('contacto')); ?>">Contacto</a>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <h4>Tu cuenta</h4>
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('panel')); ?>">Mi panel</a>
                        <a href="<?php echo e(route('perfil')); ?>">Mi perfil</a>
                        <a href="<?php echo e(route('mensajes')); ?>">Mensajes</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('registro')); ?>">Soy estudiante</a>
                        <a href="<?php echo e(route('registro')); ?>">Alquilo un cuarto</a>
                        <a href="<?php echo e(route('registro')); ?>">Ofrezco trabajo</a>
                        <a href="<?php echo e(route('login')); ?>">Iniciar sesión</a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4>Contacto</h4>
                    <p><i class="bi bi-envelope"></i> <a href="mailto:<?php echo e(config('tampu.correo')); ?>"><?php echo e(config('tampu.correo')); ?></a></p>
                    <p><i class="bi bi-geo-alt"></i> Cusco, Perú</p>
                    <p class="pie-aviso"><i class="bi bi-shield-check"></i> Nunca pagues un adelanto sin visitar el cuarto.</p>
                </div>
            </div>
            <div class="pie-final">
                <span>© 2026 TAMPU · Proyecto del curso Programación Web · Universidad Continental</span>
                <a href="#" class="subir" aria-label="Volver arriba"><i class="bi bi-arrow-up"></i></a>
            </div>
        </div>
    </footer>

    <?php echo $__env->make('partials.asistente', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(auth()->guard()->check()): ?>
        <a href="<?php echo e(route('notificaciones')); ?>" id="avisoNuevo" class="aviso-nuevo oculto" data-estado="<?php echo e(route('estado')); ?>">
            <i class="bi bi-bell"></i> <span></span>
        </a>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo e(asset('js/script.js')); ?>?v=<?php echo e(filemtime(public_path('js/script.js'))); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/layouts/app.blade.php ENDPATH**/ ?>