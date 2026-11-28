<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Arrendador\HabitacionController as ArrendadorHabitacion;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ClaveController;
use App\Http\Controllers\ConstanciaController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\CorreoController;
use App\Http\Controllers\DosPasosController;
use App\Http\Controllers\Empleador\TrabajoController as EmpleadorTrabajo;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\ExternoController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MensajeController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TrabajoController;
use Illuminate\Support\Facades\Route;

// paginas publicas
Route::get('/', [InicioController::class, 'inicio'])->name('inicio');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/nosotros', [InicioController::class, 'nosotros'])->name('nosotros');
Route::get('/me-alcanza', [\App\Http\Controllers\CalculadoraController::class, 'index'])->name('calculadora');
Route::post('/me-alcanza/presupuesto', [\App\Http\Controllers\CalculadoraController::class, 'guardar'])->middleware(['auth', 'throttle:30,1'])->name('presupuesto.guardar');
Route::get('/habitaciones', [HabitacionController::class, 'index'])->name('habitaciones.index');
Route::get('/habitaciones/{habitacion}', [HabitacionController::class, 'show'])->name('habitaciones.show');
Route::get('/trabajos', [TrabajoController::class, 'index'])->name('trabajos.index');
Route::get('/trabajos/{trabajo}', [TrabajoController::class, 'show'])->whereNumber('trabajo')->name('trabajos.show');
Route::get('/contacto', [ContactoController::class, 'formulario'])->name('contacto');
Route::post('/contacto', [ContactoController::class, 'enviar'])->middleware('throttle:5,1');
Route::post('/boletin', [ContactoController::class, 'boletin'])->middleware('throttle:5,1')->name('boletin');
Route::post('/asistente', [ChatbotController::class, 'responder'])->middleware('throttle:30,1')->name('asistente');

// login, registro y recuperar contraseña
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'formRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registro']);
    Route::get('/olvide', [ClaveController::class, 'formOlvide'])->name('clave.olvide');
    Route::post('/olvide', [ClaveController::class, 'enviarEnlace'])->middleware('throttle:5,1');
    Route::get('/olvide/enviado', [ClaveController::class, 'enviado'])->name('clave.enviado');
    Route::get('/restablecer/{token}', [ClaveController::class, 'formReset'])->name('clave.reset');
    Route::post('/restablecer', [ClaveController::class, 'restablecer'])->name('clave.guardar');

    // segundo paso del login
    Route::get('/dos-pasos', [DosPasosController::class, 'formulario'])->name('dos-pasos');
    Route::post('/dos-pasos', [DosPasosController::class, 'verificar'])->middleware('throttle:dos-pasos');
});

// Google y Microsoft: sin sesion sirve para entrar, con sesion para vincular el correo institucional
Route::get('/externo/{nombre}', [ExternoController::class, 'ir'])->whereIn('nombre', ['google', 'microsoft'])
    ->middleware('throttle:externo')->name('externo.ir');
Route::get('/externo/{nombre}/vuelta', [ExternoController::class, 'vuelta'])->whereIn('nombre', ['google', 'microsoft'])
    ->name('externo.vuelta');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/panel', [AuthController::class, 'panel'])->name('panel');

    // para todos los roles
    Route::get('/perfil', [PerfilController::class, 'editar'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'actualizar']);
    Route::put('/perfil/clave', [PerfilController::class, 'cambiarClave'])->name('perfil.clave');
    Route::get('/perfil/dos-pasos', [DosPasosController::class, 'activar'])->name('dos-pasos.activar');
    Route::post('/perfil/dos-pasos', [DosPasosController::class, 'confirmar'])->middleware('throttle:codigo-correo')->name('dos-pasos.confirmar');
    Route::delete('/perfil/dos-pasos', [DosPasosController::class, 'desactivar'])->name('dos-pasos.desactivar');

    Route::get('/verificar-correo', [CorreoController::class, 'formulario'])->name('correo.verificar');
    Route::post('/verificar-correo/enviar', [CorreoController::class, 'enviar'])->middleware('throttle:enviar-correo')->name('correo.enviar');
    Route::post('/verificar-correo', [CorreoController::class, 'confirmar'])->middleware('throttle:codigo-correo')->name('correo.confirmar');

    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones');
    Route::get('/notificaciones/{id}', [NotificacionController::class, 'abrir'])->name('notificaciones.abrir');
    Route::post('/notificaciones/leer', [NotificacionController::class, 'leerTodas'])->name('notificaciones.leer');

    Route::get('/estado', [NotificacionController::class, 'estado'])->name('estado');
    Route::get('/asesor', [MensajeController::class, 'asesor'])->name('asesor');
    Route::get('/mensajes', [MensajeController::class, 'index'])->name('mensajes');
    Route::post('/mensajes/iniciar', [MensajeController::class, 'iniciar'])->middleware('correo')->name('mensajes.iniciar');
    Route::get('/mensajes/{conversacion}', [MensajeController::class, 'ver'])->name('mensajes.ver');
    Route::post('/mensajes/{conversacion}', [MensajeController::class, 'enviar'])->middleware(['throttle:30,1', 'correo'])->name('mensajes.enviar');
    Route::get('/mensajes/{conversacion}/nuevos', [MensajeController::class, 'nuevos'])->name('mensajes.nuevos');

    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario');
    Route::get('/calendario/eventos', [CalendarioController::class, 'eventos'])->name('calendario.eventos');

    Route::post('/reportes', [ReporteController::class, 'store'])->middleware(['rol:estudiante,arrendador,empleador', 'correo'])->name('reportes.store');

    Route::get('/constancia/visita/{solicitud}', [ConstanciaController::class, 'visita'])->name('constancia.visita');
    Route::get('/constancia/postulacion/{postulacion}', [ConstanciaController::class, 'postulacion'])->name('constancia.postulacion');

    // estudiante
    Route::middleware('rol:estudiante')->group(function () {
        Route::get('/estudiante', [EstudianteController::class, 'panel'])->name('estudiante.panel');
        Route::post('/estudiante/horario', [EstudianteController::class, 'guardarHorario'])
            ->name('estudiante.horario');
        Route::get('/estudiante/favoritos', [EstudianteController::class, 'favoritos'])->name('estudiante.favoritos');
        Route::post('/habitaciones/{habitacion}/solicitar', [EstudianteController::class, 'solicitar'])
            ->middleware('correo')->name('estudiante.solicitar');
        Route::post('/habitaciones/{habitacion}/resena', [EstudianteController::class, 'resenar'])
            ->middleware('correo')->name('estudiante.resenar');
        Route::post('/estudiante/visitas/{solicitud}/confirmar', [EstudianteController::class, 'confirmarVisita'])
            ->name('estudiante.confirmar');
        Route::post('/habitaciones/{habitacion}/favorito', [EstudianteController::class, 'favorito'])
            ->name('estudiante.favorito');
        Route::post('/trabajos/{trabajo}/postular', [EstudianteController::class, 'postular'])
            ->middleware('correo')->name('estudiante.postular');
    });

    // arrendador
    Route::middleware(['rol:arrendador', 'correo'])->prefix('arrendador')->name('arrendador.')->group(function () {
        Route::resource('habitaciones', ArrendadorHabitacion::class)
            ->except('show')->parameters(['habitaciones' => 'habitacion']);
        Route::get('solicitudes', [ArrendadorHabitacion::class, 'solicitudes'])->name('solicitudes');
        Route::patch('solicitudes/{solicitud}', [ArrendadorHabitacion::class, 'responder'])
            ->name('solicitudes.responder');
    });

    // empleador
    Route::middleware(['rol:empleador', 'correo'])->prefix('empleador')->name('empleador.')->group(function () {
        Route::resource('trabajos', EmpleadorTrabajo::class)->except('show');
        Route::get('postulaciones', [EmpleadorTrabajo::class, 'postulaciones'])->name('postulaciones');
        Route::patch('postulaciones/{postulacion}', [EmpleadorTrabajo::class, 'responder'])
            ->name('postulaciones.responder');
    });

    // administrador
    Route::middleware('rol:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'panel'])->name('panel');
        Route::get('usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
        Route::get('usuarios/{usuario}/documento', [AdminController::class, 'documento'])->name('documento');
        Route::patch('usuarios/{usuario}/verificar', [AdminController::class, 'verificar'])->name('verificar');
        Route::patch('usuarios/{usuario}/suspender', [AdminController::class, 'suspender'])->name('suspender');
        Route::get('reportes', [AdminController::class, 'reportes'])->name('reportes');
        Route::patch('reportes/{reporte}', [AdminController::class, 'resolver'])->name('reportes.resolver');
        Route::get('contactos', [AdminController::class, 'contactos'])->name('contactos');
        Route::patch('contactos/{contacto}', [AdminController::class, 'leerContacto'])->name('contactos.leer');
        Route::get('seguridad', [AdminController::class, 'seguridad'])->name('seguridad');
    });
});
