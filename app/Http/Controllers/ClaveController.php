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
        $request->validate(['email' => 'required|email'], [
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Ese correo no tiene un formato válido (ejemplo: nombre@gmail.com).',
        ]);

        $correo = mb_strtolower(trim($request->email));
        $usuario = Usuario::whereRaw('LOWER(email) = ?', [$correo])->first();

        // decir que no existe ayuda a quien se equivocó al escribir; el límite de 5 intentos por minuto evita que lo usen para adivinar correos
        if (! $usuario) {
            return back()->withInput()
                ->withErrors(['email' => 'No encontramos ninguna cuenta con ese correo. Revisa que esté bien escrito o crea una cuenta nueva.'])
                ->with('sin_cuenta', true);
        }

        $token = Password::broker()->createToken($usuario);
        $enlace = route('clave.reset', ['token' => $token, 'email' => $usuario->email]);
        EventoSeguridad::registrar('clave_enlace', null, $usuario);

        try {
            $usuario->notify(new RestablecerClave($enlace, RestablecerClave::describir($request->userAgent())));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo: ' . $e->getMessage());
            Log::info('Enlace para restablecer la contraseña de ' . $usuario->email . ': ' . $enlace);
            if (! app()->isLocal()) {
                return back()->withInput()->withErrors(['email' => 'No pudimos enviar el correo en este momento. Intenta de nuevo en unos minutos.']);
            }
        }

        $volver = redirect()->route('clave.enviado')->with('correo_enviado', $usuario->email);

        // en local no hay correo de verdad: se muestra el enlace para poder probar
        return app()->isLocal() && config('mail.default') === 'log' ? $volver->with('enlace', $enlace) : $volver;
    }

    // pantalla de "revisa tu correo"
    public function enviado(Request $request)
    {
        $correo = $request->session()->get('correo_enviado');
        if (! $correo) {
            return redirect()->route('clave.olvide');
        }
        $request->session()->keep(['correo_enviado', 'enlace']);

        return view('auth.olvide-enviado', ['correo' => $correo, 'oculto' => self::ocultar($correo)]);
    }

    // cuentacuentos602@gmail.com -> cu**********02@gmail.com
    public static function ocultar(string $correo): string
    {
        [$nombre, $dominio] = array_pad(explode('@', $correo, 2), 2, '');
        $largo = mb_strlen($nombre);
        $visible = $largo <= 4 ? mb_substr($nombre, 0, 1) . str_repeat('•', max($largo - 1, 1))
            : mb_substr($nombre, 0, 2) . str_repeat('•', min($largo - 4, 6)) . mb_substr($nombre, -2);

        return $visible . '@' . $dominio;
    }

    public function formReset(Request $request, string $token)
    {
        $usuario = $request->email ? Usuario::where('email', $request->email)->first() : null;
        $valido = $usuario && Password::broker()->tokenExists($usuario, $token);

        return view('auth.restablecer', [
            'token' => $token,
            'email' => $request->email,
            'valido' => $valido,
            'nombre' => $usuario ? explode(' ', trim($usuario->nombre))[0] : null,
        ]);
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
