<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Usuario;
use App\Notifications\Aviso;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReporteController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'habitacion_id' => 'nullable|required_without:trabajo_id|exists:habitaciones,id',
            'trabajo_id' => 'nullable|exists:trabajos,id',
            'motivo' => ['required', Rule::in(Reporte::MOTIVOS)],
            'detalle' => 'nullable|string|max:500',
        ]);

        // un reporte pendiente por publicacion
        $repetido = Reporte::where('usuario_id', $request->user()->id)
            ->where('estado', 'pendiente')
            ->where('habitacion_id', $datos['habitacion_id'] ?? null)
            ->where('trabajo_id', $datos['trabajo_id'] ?? null)
            ->exists();

        if ($repetido) {
            return back()->with('error', 'Ya reportaste esta publicación. El administrador la está revisando.');
        }

        $datos['usuario_id'] = $request->user()->id;
        $reporte = Reporte::create($datos)->load(['habitacion', 'trabajo']);

        foreach (Usuario::where('rol', 'admin')->get() as $admin) {
            $admin->notify(new Aviso('Nuevo reporte: ' . $datos['motivo'], route('admin.reportes'), 'flag'));
        }

        // y al correo del equipo, para enterarse aunque no entre al panel
        $sobre = $reporte->habitacion ? 'Cuarto: ' . $reporte->habitacion->titulo : ($reporte->trabajo ? 'Trabajo: ' . $reporte->trabajo->titulo : '—');
        \App\Support\CorreoAdmin::enviar('Nuevo reporte: ' . $datos['motivo'], 'Reporte', [
            'Motivo' => $datos['motivo'],
            'Sobre' => $sobre,
            'Lo reporta' => $request->user()->nombre . ' (' . $request->user()->email . ')',
            'Cuándo' => now('America/Lima')->format('d/m/Y H:i'),
        ], $datos['detalle'] ?? null, route('admin.reportes'), 'Revisar el reporte');

        return back()->with('ok', 'Gracias. El administrador revisará tu reporte.');
    }
}
