<?php

namespace App\Http\Controllers;

use App\Models\EventoSeguridad;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function formLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // maximo 5 intentos por minuto
        $clave = Str::lower($datos['email']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);

            return back()->withErrors(['email' => "Demasiados intentos. Espera $segundos segundos y vuelve a probar."])->onlyInput('email');
        }

        if (! Auth::attempt($datos, $request->boolean('recordar'))) {
            $intentos = RateLimiter::hit($clave, 60);
            $dueno = Usuario::where('email', $datos['email'])->first();
            EventoSeguridad::registrar('login_fallido', "Intento $intentos de 5", $dueno, $datos['email']);

            // al quinto intento se bloquea y se avisa al admin
            if ($intentos === 5) {
                EventoSeguridad::registrar('bloqueo', 'Bloqueado 60 segundos', $dueno, $datos['email']);
                EventoSeguridad::alertarAdmins('5 intentos fallidos seguidos con ' . $datos['email'] . ' desde ' . $request->ip());
            }

            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos.'])->onlyInput('email');
        }

        RateLimiter::clear($clave);

        if (Auth::user()->suspendido) {
            EventoSeguridad::registrar('login_suspendido');
            Auth::logout();

            return back()->withErrors(['email' => 'Tu cuenta fue suspendida por el administrador.'])->onlyInput('email');
        }

        // nueva sesion
        $request->session()->regenerate();
        EventoSeguridad::registrar('login');

        return redirect()->intended(route('panel'));
    }

    public function formRegistro()
    {
        return view('auth.registro');
    }

    public function registro(Request $request)
    {
        $datos = $request->validate([
            'rol' => 'required|in:estudiante,arrendador,empleador',
            'nombre' => 'required|string|min:3|max:100',
            'dni' => 'required|digits:8|unique:usuarios,dni',
            'celular' => 'nullable|digits:9',
            'procedencia' => 'nullable|string|max:60',
            'email' => 'required|email|max:100|unique:usuarios,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'documento' => 'required_unless:rol,estudiante|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'documento.required_unless' => 'Sube tu DNI o RUC para que el administrador pueda verificarte.',
        ]);

        // el documento va en una carpeta privada
        if ($request->hasFile('documento')) {
            $datos['documento'] = $request->file('documento')->store('documentos', 'documentos');
            $datos['verificacion'] = 'pendiente';
        }

        $usuario = Usuario::create($datos);
        Auth::login($usuario);
        $request->session()->regenerate();
        EventoSeguridad::registrar('registro', 'Rol ' . $usuario->rol);

        return redirect()->route('panel')->with('ok', 'Tu cuenta fue creada. ¡Bienvenido a TAMPU!');
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            EventoSeguridad::registrar('logout');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio');
    }

    // cada rol a su panel
    public function panel(Request $request)
    {
        return match ($request->user()->rol) {
            'admin' => redirect()->route('admin.panel'),
            'arrendador' => redirect()->route('arrendador.habitaciones.index'),
            'empleador' => redirect()->route('empleador.trabajos.index'),
            default => redirect()->route('estudiante.panel'),
        };
    }
}
