<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// para publicar, postular, pedir visitas o escribir hay que confirmar el correo primero
class CorreoConfirmado
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->user();

        if ($usuario && config('tampu.verificar_correo') && ! $usuario->correoConfirmado()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Confirma tu correo primero.'], 403);
            }
            if ($request->isMethod('GET')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('correo.verificar')->withErrors(['codigo' => 'Antes de continuar, confirma tu correo.']);
        }

        return $next($request);
    }
}
