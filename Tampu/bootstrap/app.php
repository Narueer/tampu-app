<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rol' => \App\Http\Middleware\RolMiddleware::class,
            'correo' => \App\Http\Middleware\CorreoConfirmado::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\CuentaActiva::class,
            \App\Http\Middleware\CabecerasSeguridad::class,
        ]);
        // en la nube el https lo maneja el proxy del hosting
        $middleware->trustProxies(at: '*');
        $middleware->redirectUsersTo('/panel');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // se anota quien intento entrar a algo que no le corresponde
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 403 && $request->user()) {
                \App\Models\EventoSeguridad::registrar('acceso_denegado', $request->method() . ' /' . $request->path());
            }

            return null;
        });
    })->create();
