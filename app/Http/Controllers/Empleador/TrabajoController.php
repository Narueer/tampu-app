<?php

namespace App\Http\Controllers\Empleador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HabitacionController as Publico;
use App\Models\Horario;
use App\Models\Postulacion;
use App\Models\Trabajo;
use App\Notifications\Aviso;
use Illuminate\Http\Request;

class TrabajoController extends Controller
{
    public function index(Request $request)
    {
        $trabajos = $request->user()->trabajos()->with('horarios')->withCount('postulaciones')->latest()->get();

        return view('empleador.index', compact('trabajos'));
    }

    public function create(Request $request)
    {
        $this->soloVerificado($request);

        return view('empleador.form', ['trabajo' => new Trabajo(), 'marcados' => [], 'distritos' => Publico::DISTRITOS]);
    }

    public function store(Request $request)
    {
        $this->soloVerificado($request);
        $trabajo = $request->user()->trabajos()->create($this->validar($request));
        $this->guardarTurnos($request, $trabajo);

        return redirect()->route('empleador.trabajos.index')->with('ok', 'Oferta publicada.');
    }

    public function edit(Request $request, Trabajo $trabajo)
    {
        $this->esSuyo($request, $trabajo);
        $marcados = $trabajo->horarios->map(fn ($h) => $h->dia . '-' . $h->turno)->all();

        return view('empleador.form', ['trabajo' => $trabajo, 'marcados' => $marcados, 'distritos' => Publico::DISTRITOS]);
    }

    public function update(Request $request, Trabajo $trabajo)
    {
        $this->esSuyo($request, $trabajo);
        $trabajo->update($this->validar($request));
        $this->guardarTurnos($request, $trabajo);

        return redirect()->route('empleador.trabajos.index')->with('ok', 'Oferta actualizada.');
    }

    public function destroy(Request $request, Trabajo $trabajo)
    {
        $this->esSuyo($request, $trabajo);
        $trabajo->delete();

        return back()->with('ok', 'Oferta eliminada.');
    }

    public function postulaciones(Request $request)
    {
        $postulaciones = Postulacion::with(['trabajo', 'estudiante.horarios'])
            ->whereHas('trabajo', fn ($q) => $q->where('usuario_id', $request->user()->id))
            ->latest()->get();

        return view('empleador.postulaciones', compact('postulaciones'));
    }

    public function responder(Request $request, Postulacion $postulacion)
    {
        abort_unless($postulacion->trabajo->usuario_id === $request->user()->id, 403);
        $request->validate(['estado' => 'required|in:aceptada,rechazada']);

        $postulacion->update(['estado' => $request->estado]);

        $texto = $request->estado === 'aceptada' ? 'aceptó' : 'no aceptó';
        $postulacion->estudiante->notify(new Aviso(
            $postulacion->trabajo->empresa . ' ' . $texto . ' tu postulación a "' . $postulacion->trabajo->titulo . '"',
            route('estudiante.panel'), 'briefcase'
        ));

        return back()->with('ok', 'Postulación ' . $request->estado . '.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'titulo' => 'required|string|max:120',
            'empresa' => 'required|string|max:100',
            'descripcion' => 'required|string|max:1000',
            'distrito' => 'required|in:' . implode(',', Publico::DISTRITOS),
            'modalidad' => 'required|in:medio tiempo,por horas,practicas',
            'pago' => 'required|numeric|min:1|max:5000',
            'pago_por' => 'required|in:hora,dia,mes',
            'estado' => 'required|in:activa,cerrada',
            'turnos' => 'required|array|min:1',
            'turnos.*' => ['regex:/^[a-z]+-(manana|tarde|noche)$/'],
        ], [
            'turnos.required' => 'Marca al menos un turno de trabajo.',
        ]);
    }

    private function guardarTurnos(Request $request, Trabajo $trabajo): void
    {
        $trabajo->horarios()->delete();
        foreach ($request->input('turnos', []) as $t) {
            [$dia, $turno] = explode('-', $t);
            if (array_key_exists($dia, Horario::DIAS)) {
                $trabajo->horarios()->create(['dia' => $dia, 'turno' => $turno]);
            }
        }
    }

    private function esSuyo(Request $request, Trabajo $trabajo): void
    {
        abort_unless($trabajo->usuario_id === $request->user()->id, 403);
    }

    private function soloVerificado(Request $request): void
    {
        abort_unless($request->user()->esVerificado(), 403, 'Tu cuenta aún no fue verificada por el administrador.');
    }
}
