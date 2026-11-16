<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use Illuminate\Http\Request;

class TrabajoController extends Controller
{
    public function index(Request $request)
    {
        $consulta = Trabajo::publicadas()->with('horarios');

        if ($request->filled('modalidad')) {
            $consulta->where('modalidad', $request->modalidad);
        }

        // filtro por horario
        $clases = collect();
        $usuario = $request->user();
        if ($usuario && $usuario->rol === 'estudiante') {
            $clases = $usuario->horarios;
        }

        $compatible = $request->boolean('compatible') && $clases->isNotEmpty();
        if ($compatible) {
            // quita los que chocan con sus clases
            $consulta->whereDoesntHave('horarios', function ($q) use ($clases) {
                $q->where(function ($w) use ($clases) {
                    foreach ($clases as $c) {
                        $w->orWhere(fn ($x) => $x->where('dia', $c->dia)->where('turno', $c->turno));
                    }
                });
            });
        }

        $trabajos = $consulta->latest()->get();

        $postulados = [];
        if ($usuario && $usuario->rol === 'estudiante') {
            $postulados = $usuario->postulaciones()->pluck('estado', 'trabajo_id')->all();
        }

        return view('trabajos.index', compact('trabajos', 'compatible', 'clases', 'postulados'));
    }
}
