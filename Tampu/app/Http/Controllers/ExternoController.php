<?php

namespace App\Http\Controllers;

use App\Models\EventoSeguridad;
use App\Models\Usuario;
use App\Support\Acceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// entrar o vincular el correo institucional con Google o Microsoft (OAuth 2.0 / OpenID Connect)
class ExternoController extends Controller
{
    private function proveedor(string $nombre): array
    {
        $datos = config("tampu.oauth.$nombre");
        abort_unless($datos && $datos['id'], 404);

        return $datos;
    }

    public function ir(Request $request, string $nombre)
    {
        $p = $this->proveedor($nombre);

        // el state evita que otra pagina nos mande una respuesta que no pedimos
        $state = Str::random(40);
        $vincular = $request->user() !== null;
        $request->session()->put('externo', ['state' => $state, 'vincular' => $vincular]);

        $parametros = [
            'client_id' => $p['id'],
            'redirect_uri' => route('externo.vuelta', $nombre),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ];
        // al vincular, Google ya muestra solo las cuentas de la universidad
        if ($vincular && $nombre === 'google') {
            $parametros['hd'] = config('tampu.dominio_institucional');
        }

        return redirect()->away($p['autorizar'] . '?' . http_build_query($parametros));
    }

    public function vuelta(Request $request, string $nombre)
    {
        $p = $this->proveedor($nombre);
        $guardado = $request->session()->pull('externo');
        $volver = $guardado['vincular'] ?? false ? route('perfil') : route('login');

        if (! $guardado || ! hash_equals($guardado['state'], (string) $request->state)) {
            return redirect($volver)->withErrors(['email' => 'La respuesta de ' . $p['nombre'] . ' no es válida. Vuelve a intentarlo.']);
        }
        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect($volver)->withErrors(['email' => 'Cancelaste el ingreso con ' . $p['nombre'] . '.']);
        }

        $datos = $this->datosDelUsuario($p, $nombre, $request->code);
        if (! $datos) {
            return redirect($volver)->withErrors(['email' => 'No pudimos comprobar tu cuenta de ' . $p['nombre'] . '. Intenta otra vez.']);
        }

        return $guardado['vincular']
            ? $this->vincular($request, $datos, $p['nombre'])
            : $this->entrar($request, $datos, $p['nombre']);
    }

    // cambia el code por un token y pide el correo y el nombre
    private function datosDelUsuario(array $p, string $nombre, string $code): ?array
    {
        try {
            $token = Http::asForm()->timeout(10)->post($p['token'], [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => route('externo.vuelta', $nombre),
                'client_id' => $p['id'],
                'client_secret' => $p['secreto'],
            ]);
            if (! $token->ok() || ! $token->json('access_token')) {
                Log::warning("Token de $nombre rechazado: " . $token->body());

                return null;
            }

            $info = Http::withToken($token->json('access_token'))->timeout(10)->get($p['usuario'])->json() ?? [];

            // si no viene el correo se toma del id_token (llega directo del proveedor por https)
            if (empty($info['email']) && $token->json('id_token')) {
                $partes = explode('.', $token->json('id_token'));
                $claims = json_decode(base64_decode(strtr($partes[1] ?? '', '-_', '+/')), true) ?? [];
                $info['email'] = $claims['email'] ?? $claims['preferred_username'] ?? null;
                $info['name'] ??= $claims['name'] ?? null;
            }
        } catch (\Throwable $e) {
            Log::warning("Error al hablar con $nombre: " . $e->getMessage());

            return null;
        }

        // Google dice si el correo esta confirmado; en Microsoft lo da la cuenta de la organizacion
        $email = strtolower($info['email'] ?? '');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || (isset($info['email_verified']) && ! $info['email_verified'])) {
            return null;
        }

        return ['email' => $email, 'nombre' => $info['name'] ?? Str::before($email, '@')];
    }

    private function entrar(Request $request, array $datos, string $via)
    {
        $usuario = Usuario::where('email', $datos['email'])->orWhere('correo_institucional', $datos['email'])->first();

        // no tiene cuenta: se registra con el correo ya comprobado
        if (! $usuario) {
            $request->session()->put('registro_externo', $datos + ['via' => $via]);

            return redirect()->route('registro')->with('ok', "Tu correo ya fue comprobado con $via. Completa tus datos para crear la cuenta.");
        }

        if ($usuario->suspendido) {
            EventoSeguridad::registrar('externo_rechazado', "Cuenta suspendida, con $via", $usuario);

            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta fue suspendida por el administrador.']);
        }

        // alguien pudo registrarse antes con este correo sin ser suyo: como el dueño real
        // acaba de demostrarlo, se anula esa contraseña y se marca el correo como confirmado
        if (! $usuario->email_verificado_at && $usuario->email === $datos['email']) {
            $usuario->forceFill([
                'email_verificado_at' => now(), 'password' => Str::random(40),
                'dos_pasos_activo' => false, 'dos_pasos_secreto' => null, 'dos_pasos_codigos' => null,
            ])->save();
            EventoSeguridad::registrar('correo_confirmado', "Con $via; se anuló la contraseña anterior", $usuario);
        }

        return Acceso::completar($request, $usuario, false, "Con $via");
    }

    private function vincular(Request $request, array $datos, string $via)
    {
        $usuario = $request->user();

        if (! Acceso::esInstitucional($datos['email'])) {
            EventoSeguridad::registrar('externo_rechazado', $datos['email'] . ' no es de la universidad');

            return redirect()->route('perfil')->withErrors(['email' => 'Ese correo no es de la universidad. Elige tu cuenta @' . config('tampu.dominio_institucional') . '.']);
        }

        $otro = Usuario::where('correo_institucional', $datos['email'])->where('id', '!=', $usuario->id)->exists();
        if ($otro) {
            return redirect()->route('perfil')->withErrors(['email' => 'Ese correo institucional ya está vinculado a otra cuenta.']);
        }

        $usuario->update(['correo_institucional' => $datos['email']]);
        EventoSeguridad::registrar('institucional', $datos['email'] . " con $via");

        return redirect()->route('perfil')->with('ok', 'Listo, tu cuenta ahora figura como estudiante verificado.');
    }
}
