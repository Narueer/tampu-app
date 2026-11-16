<?php

namespace App\Http\Controllers;

use App\Support\Archivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function editar(Request $request)
    {
        return view('perfil', ['usuario' => $request->user()]);
    }

    public function actualizar(Request $request)
    {
        $usuario = $request->user();
        $datos = $request->validate([
            'nombre' => 'required|string|min:3|max:100',
            'celular' => 'nullable|digits:9',
            'procedencia' => 'nullable|string|max:60',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            Archivos::borrarFoto($usuario->foto);
            $datos['foto'] = Archivos::guardarFoto($request->file('foto'), 'perfiles');
        }

        $usuario->update($datos);

        return back()->with('ok', 'Tus datos se guardaron.');
    }

    public function cambiarClave(Request $request)
    {
        $request->validate([
            'actual' => 'required',
            'password' => 'required|min:6|confirmed|different:actual',
        ], [
            'password.different' => 'La nueva contraseña debe ser distinta a la actual.',
        ]);

        if (! Hash::check($request->actual, $request->user()->password)) {
            return back()->withErrors(['actual' => 'Tu contraseña actual no es correcta.']);
        }

        $request->user()->update(['password' => $request->password]);

        return back()->with('ok', 'Tu contraseña se cambió.');
    }
}
