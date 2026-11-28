<?php

namespace App\Http\Controllers;

use App\Models\EventoSeguridad;
use App\Models\Usuario;
use App\Notifications\RestablecerClave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as ReglaClave;

// recuperar contraseña
class ClaveController extends Controller
{
    public function formOlvide()
    {
        return view('auth.olvide');
    }

    public function enviarEnlace(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $enlace = null;
        $usuario = Usuario::where('email', $request->email)->first();

        if ($usuario) {
            $token = Password::broker()->createToken($usuario);
            $enlace = route('clave.reset', ['token' => $token, 'email' => $usuario->email]);
            EventoSeguridad::registrar('clave_enlace', null, $usuario);

            // si no hay correo configurado (MAIL_MAILER=log) el mensaje queda en storage/logs/laravel.log
            try {
                $usuario->notify(new RestablecerClave($enlace, RestablecerClave::describir($request->userAgent())));
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar el correo: ' . $e->getMessage());
                Log::info('Enlace para restablecer la contraseña de ' . $usuario->email . ': ' . $enlace);
            }
        }

        // en local mostramos el enlace para poder probar
        $mensaje = 'Si el correo está registrado, te enviamos un enlace para cambiar tu contraseña.';
        if ($enlace && app()->isLocal()) {
            return back()->with('ok', $mensaje)->with('enlace', $enlace);
        }

        return back()->with('ok', $mensaje);
    }

    public function formReset(Request $request, string $token)
    {
        return view('auth.restablecer', ['token' => $token, 'email' => $request->email]);
    }

    public function restablecer(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', ReglaClave::min(8)->letters()->numbers()],
        ]);

        $estado = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Usuario $usuario, string $clave) {
                $usuario->update(['password' => $clave]);
                EventoSeguridad::registrar('clave_recuperada', null, $usuario);
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'El enlace no es válido o ya venció. Pide uno nuevo.']);
        }

        return redirect()->route('login')->with('ok', 'Tu contraseña se cambió. Ya puedes iniciar sesión.');
    }
}
