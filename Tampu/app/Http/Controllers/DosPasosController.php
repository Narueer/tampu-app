<?php

namespace App\Http\Controllers;

use App\Models\EventoSeguridad;
use App\Models\Usuario;
use App\Support\Acceso;
use App\Support\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// verificacion en dos pasos con una app autenticadora
class DosPasosController extends Controller
{
    // paso 2 del login
    public function formulario(Request $request)
    {
        $pendiente = $request->session()->get('dos_pasos');
        if (! $pendiente || $pendiente['hasta'] < now()->timestamp) {
            $request->session()->forget('dos_pasos');

            return redirect()->route('login')->withErrors(['email' => 'Pasó mucho tiempo. Vuelve a iniciar sesión.']);
        }

        return view('auth.dos-pasos');
    }

    public function verificar(Request $request)
    {
        $request->validate(['codigo' => 'required|string|max:20']);
        $pendiente = $request->session()->get('dos_pasos');
        $usuario = $pendiente ? Usuario::find($pendiente['id']) : null;

        if (! $usuario || $pendiente['hasta'] < now()->timestamp || $usuario->suspendido) {
            $request->session()->forget('dos_pasos');

            return redirect()->route('login')->withErrors(['email' => 'Vuelve a iniciar sesión.']);
        }

        $codigo = trim($request->codigo);
        $via = trim(($pendiente['via'] ?? '') . ' + verificación en dos pasos', ' +');

        if ($this->codigoValido($usuario, $codigo)) {
            $request->session()->forget('dos_pasos');

            return Acceso::entrar($request, $usuario, $pendiente['recordar'], $via);
        }

        if ($this->usarRespaldo($usuario, $codigo)) {
            $request->session()->forget('dos_pasos');
            EventoSeguridad::registrar('codigo_respaldo', 'Le quedan ' . count($usuario->dos_pasos_codigos) . ' códigos', $usuario);

            return Acceso::entrar($request, $usuario, $pendiente['recordar'], $via);
        }

        EventoSeguridad::registrar('dos_pasos_fallido', null, $usuario);

        return back()->withErrors(['codigo' => 'El código no es correcto o ya se usó. Mira otra vez la app.']);
    }

    // el mismo codigo no sirve dos veces
    private function codigoValido(Usuario $usuario, string $codigo): bool
    {
        try {
            $paso = Totp::verificar($usuario->dos_pasos_secreto, $codigo);
        } catch (\Throwable $e) {
            return false;
        }

        if ($paso === null || $paso <= (int) $usuario->dos_pasos_ultimo) {
            return false;
        }
        $usuario->forceFill(['dos_pasos_ultimo' => $paso])->save();

        return true;
    }

    private function usarRespaldo(Usuario $usuario, string $codigo): bool
    {
        $codigos = $usuario->dos_pasos_codigos ?? [];
        foreach ($codigos as $i => $hash) {
            if (Hash::check(strtolower($codigo), $hash)) {
                unset($codigos[$i]);
                $usuario->forceFill(['dos_pasos_codigos' => array_values($codigos)])->save();

                return true;
            }
        }

        return false;
    }

    // activar desde Mi perfil: se muestra el QR y se confirma con un codigo
    public function activar(Request $request)
    {
        $usuario = $request->user();
        if ($usuario->dos_pasos_activo) {
            return redirect()->route('perfil');
        }

        $secreto = $request->session()->get('dos_pasos_nuevo') ?? Totp::nuevoSecreto();
        $request->session()->put('dos_pasos_nuevo', $secreto);

        return view('perfil-dos-pasos', [
            'secreto' => $secreto,
            'enlace' => Totp::enlace($secreto, $usuario->email),
        ]);
    }

    public function confirmar(Request $request)
    {
        $request->validate(['codigo' => 'required|string|max:10']);
        $usuario = $request->user();
        $secreto = $request->session()->get('dos_pasos_nuevo');

        $paso = $secreto ? Totp::verificar($secreto, $request->codigo) : null;
        if ($paso === null) {
            return back()->withErrors(['codigo' => 'El código no coincide. Revisa que la hora del celular esté bien y prueba con el siguiente.']);
        }

        // 8 codigos de respaldo por si pierde el celular; se guardan como hash
        $respaldo = collect(range(1, 8))->map(fn () => $this->codigoRespaldo());

        $usuario->forceFill([
            'dos_pasos_activo' => true,
            'dos_pasos_secreto' => $secreto,
            'dos_pasos_ultimo' => $paso,
            'dos_pasos_codigos' => $respaldo->map(fn ($c) => Hash::make($c))->all(),
        ])->save();
        $request->session()->forget('dos_pasos_nuevo');
        EventoSeguridad::registrar('dos_pasos_activado');

        return redirect()->route('perfil')->with('ok', 'Verificación en dos pasos activada.')->with('respaldo', $respaldo->all());
    }

    // sin letras que se confunden (0 y o, 1 y l)
    private function codigoRespaldo(): string
    {
        $letras = 'abcdefghjkmnpqrstuvwxyz23456789';
        $c = '';
        for ($i = 0; $i < 8; $i++) {
            $c .= $letras[random_int(0, strlen($letras) - 1)];
        }

        return substr($c, 0, 4) . '-' . substr($c, 4);
    }

    public function desactivar(Request $request)
    {
        $request->validate(['clave' => 'required']);
        $usuario = $request->user();

        if (! Hash::check($request->clave, $usuario->password)) {
            return back()->withErrors(['clave' => 'La contraseña no es correcta.']);
        }

        $usuario->forceFill([
            'dos_pasos_activo' => false, 'dos_pasos_secreto' => null,
            'dos_pasos_codigos' => null, 'dos_pasos_ultimo' => null,
        ])->save();
        EventoSeguridad::registrar('dos_pasos_desactivado');

        return back()->with('ok', 'Se desactivó la verificación en dos pasos.');
    }
}
