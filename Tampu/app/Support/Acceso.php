<?php

namespace App\Support;

use App\Models\EventoSeguridad;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ultimo paso del login, sea con contraseña, Google o Microsoft
class Acceso
{
    public static function completar(Request $request, Usuario $usuario, bool $recordar = false, ?string $via = null)
    {
        // si tiene dos pasos, todavia no se abre la sesion: primero el codigo del celular
        if ($usuario->dos_pasos_activo) {
            $request->session()->put('dos_pasos', [
                'id' => $usuario->id,
                'recordar' => $recordar,
                'via' => $via,
                'hasta' => now()->addMinutes(5)->timestamp,
            ]);

            return redirect()->route('dos-pasos');
        }

        return self::entrar($request, $usuario, $recordar, $via);
    }

    public static function entrar(Request $request, Usuario $usuario, bool $recordar = false, ?string $via = null)
    {
        Auth::login($usuario, $recordar);
        $request->session()->regenerate();
        EventoSeguridad::registrar('login', $via, $usuario);

        return redirect()->intended(route('panel'));
    }

    public static function esInstitucional(?string $email): bool
    {
        return $email && str_ends_with(strtolower($email), '@' . strtolower(config('tampu.dominio_institucional')));
    }
}
