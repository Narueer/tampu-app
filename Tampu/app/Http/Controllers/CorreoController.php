<?php

namespace App\Http\Controllers;

use App\Models\EventoSeguridad;
use App\Notifications\CodigoCorreo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

// confirmar el correo con un codigo de 6 numeros
class CorreoController extends Controller
{
    public function formulario(Request $request)
    {
        if ($request->user()->correoConfirmado()) {
            return redirect()->route('perfil')->with('ok', 'Tu correo ya está confirmado.');
        }

        return view('auth.verificar-correo');
    }

    public function enviar(Request $request)
    {
        $usuario = $request->user();
        if ($usuario->correoConfirmado()) {
            return redirect()->route('perfil');
        }

        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $usuario->forceFill([
            'codigo_correo' => Hash::make($codigo),
            'codigo_correo_vence' => now()->addMinutes(15),
        ])->save();

        try {
            $usuario->notify(new CodigoCorreo($codigo));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el código: ' . $e->getMessage());
        }

        $volver = redirect()->route('correo.verificar')->with('ok', 'Te enviamos un código a ' . $usuario->email . '. Revisa también la carpeta de spam.');

        // en local no hay correo de verdad, asi que se muestra para poder probar
        if (app()->isLocal() && config('mail.default') === 'log') {
            $volver->with('codigo_local', $codigo);
        }

        return $volver;
    }

    public function confirmar(Request $request)
    {
        $request->validate(['codigo' => 'required|digits:6']);
        $usuario = $request->user();

        $vencido = ! $usuario->codigo_correo_vence || $usuario->codigo_correo_vence->isPast();
        if ($vencido || ! Hash::check($request->codigo, $usuario->codigo_correo)) {
            EventoSeguridad::registrar('codigo_fallido');

            return back()->withErrors(['codigo' => $vencido ? 'El código venció. Pide uno nuevo.' : 'El código no es correcto.']);
        }

        $usuario->forceFill([
            'email_verificado_at' => now(), 'codigo_correo' => null, 'codigo_correo_vence' => null,
        ])->save();
        EventoSeguridad::registrar('correo_confirmado');

        return redirect()->intended(route('panel'))->with('ok', 'Tu correo quedó confirmado.');
    }
}
