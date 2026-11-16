<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// si el admin suspende la cuenta, se cierra la sesion
class CuentaActiva
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->suspendido) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta fue suspendida por el administrador.']);
        }

        return $next($request);
    }
}
