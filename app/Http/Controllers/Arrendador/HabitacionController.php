<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HabitacionController as Publico;
use App\Models\Habitacion;
use App\Models\Solicitud;
use App\Notifications\Aviso;
use App\Support\Archivos;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {
        $habitaciones = $request->user()->habitaciones()->withCount('solicitudes')->withAvg('resenas', 'estrellas')->latest()->get();

        return view('arrendador.index', compact('habitaciones'));
    }

    public function create(Request $request)
    {
        $this->soloVerificado($request);

        return view('arrendador.form', ['habitacion' => new Habitacion(), 'distritos' => Publico::DISTRITOS]);
    }

    public function store(Request $request)
    {
        $this->soloVerificado($request);
        $datos = $this->validar($request);
        $datos['foto'] = $this->subirFoto($request);

        $request->user()->habitaciones()->create($datos);

        return redirect()->route('arrendador.habitaciones.index')->with('ok', 'Habitación publicada.');
    }

    public function edit(Request $request, Habitacion $habitacion)
    {
        $this->esSuya($request, $habitacion);

        return view('arrendador.form', ['habitacion' => $habitacion, 'distritos' => Publico::DISTRITOS]);
    }

    public function update(Request $request, Habitacion $habitacion)
    {
        $this->esSuya($request, $habitacion);
        $datos = $this->validar($request, true);

        if ($request->hasFile('foto')) {
            $this->borrarFoto($habitacion);
            $datos['foto'] = $this->subirFoto($request);
        }

        $habitacion->update($datos);

        return redirect()->route('arrendador.habitaciones.index')->with('ok', 'Habitación actualizada.');
    }

    public function destroy(Request $request, Habitacion $habitacion)
    {
        $this->esSuya($request, $habitacion);
        $this->borrarFoto($habitacion);
        $habitacion->delete();

        return back()->with('ok', 'Habitación eliminada.');
    }

    public function solicitudes(Request $request)
    {
        $solicitudes = Solicitud::with(['habitacion', 'estudiante'])
            ->whereHas('habitacion', fn ($q) => $q->where('usuario_id', $request->user()->id))
            ->latest()->get();

        return view('arrendador.solicitudes', compact('solicitudes'));
    }

    public function responder(Request $request, Solicitud $solicitud)
    {
        abort_unless($solicitud->habitacion->usuario_id === $request->user()->id, 403);
        $request->validate(['estado' => 'required|in:aceptada,rechazada']);

        $solicitud->update(['estado' => $request->estado]);

        $texto = $request->estado === 'aceptada' ? 'aceptó' : 'rechazó';
        $solicitud->estudiante->notify(new Aviso(
            $request->user()->nombre . ' ' . $texto . ' tu visita a "' . $solicitud->habitacion->titulo . '"',
            route('estudiante.panel'), 'house-door'
        ));

        return back()->with('ok', 'Solicitud ' . $request->estado . '.');
    }

    private function validar(Request $request, bool $editando = false): array
    {
        $datos = $request->validate([
            'titulo' => 'required|string|max:120',
            'descripcion' => 'required|string|max:1000',
            'distrito' => 'required|in:' . implode(',', Publico::DISTRITOS),
            'direccion' => 'required|string|max:150',
            'cerca_de' => 'nullable|string|max:80',
            'lat' => 'nullable|numeric|between:-14,-13',
            'lng' => 'nullable|numeric|between:-72.5,-71.5',
            'precio' => 'required|numeric|min:50|max:5000',
            'tipo' => 'required|in:individual,compartida,minidepartamento',
            'servicios' => 'nullable|array',
            'estado' => 'required|in:disponible,ocupada',
            'foto' => ($editando ? 'nullable' : 'required') . '|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $datos['servicios'] = implode(', ', $request->input('servicios', []));
        unset($datos['foto']);

        return $datos;
    }

    // nombre aleatorio para la foto
    private function subirFoto(Request $request): string
    {
        return Archivos::guardarFoto($request->file('foto'), 'habitaciones');
    }

    private function borrarFoto(Habitacion $habitacion): void
    {
        Archivos::borrarFoto($habitacion->foto);
    }

    private function esSuya(Request $request, Habitacion $habitacion): void
    {
        abort_unless($habitacion->usuario_id === $request->user()->id, 403);
    }

    private function soloVerificado(Request $request): void
    {
        abort_unless($request->user()->esVerificado(), 403, 'Tu cuenta aún no fue verificada por el administrador.');
    }
}
