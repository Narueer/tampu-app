<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    const DISTRITOS = ['Cusco', 'Wanchaq', 'Santiago', 'San Sebastián', 'San Jerónimo'];

    // filtros
    public function index(Request $request)
    {
        $consulta = Habitacion::publicadas()->with('arrendador')->withAvg('resenas', 'estrellas')->withCount('resenas');

        if ($request->filled('distrito')) {
            $consulta->where('distrito', $request->distrito);
        }
        if ($request->filled('precio_max')) {
            $consulta->where('precio', '<=', (float) $request->precio_max);
        }
        if ($request->filled('tipo')) {
            $consulta->where('tipo', $request->tipo);
        }
        if ($request->filled('cerca_de')) {
            $consulta->whereLike('cerca_de', '%' . $request->cerca_de . '%');
        }

        $orden = $request->orden === 'mayor' ? 'desc' : 'asc';
        $habitaciones = $consulta->orderBy('precio', $orden)->get();

        // datos para el mapa
        $puntos = $habitaciones->whereNotNull('lat')->map(fn ($h) => [
            'lat' => (float) $h->lat,
            'lng' => (float) $h->lng,
            'titulo' => $h->titulo,
            'precio' => (int) $h->precio,
            'url' => route('habitaciones.show', $h),
        ])->values();

        return view('habitaciones.index', [
            'habitaciones' => $habitaciones,
            'distritos' => self::DISTRITOS,
            'puntos' => $puntos,
            'favoritos' => $this->idsFavoritos(),
        ]);
    }

    public function show(Habitacion $habitacion)
    {
        abort_unless($habitacion->arrendador->esVerificado(), 404);

        $habitacion->load('resenas.estudiante');
        $usuario = auth()->user();
        $yaSolicito = false;
        $puedeResenar = false;
        $miResena = null;

        if ($usuario && $usuario->rol === 'estudiante') {
            $yaSolicito = $habitacion->solicitudes()
                ->where('usuario_id', $usuario->id)
                ->where('estado', 'pendiente')->exists();

            $puedeResenar = $habitacion->solicitudes()
                ->where('usuario_id', $usuario->id)
                ->where('estado', 'aceptada')
                ->whereDate('fecha_visita', '<=', now())->exists();

            $miResena = $habitacion->resenas->firstWhere('usuario_id', $usuario->id);
        }

        $esFavorito = in_array($habitacion->id, $this->idsFavoritos());

        return view('habitaciones.show', compact('habitacion', 'yaSolicito', 'puedeResenar', 'miResena', 'esFavorito'));
    }

    private function idsFavoritos(): array
    {
        $usuario = auth()->user();
        if (! $usuario || $usuario->rol !== 'estudiante') {
            return [];
        }

        return $usuario->favoritos()->pluck('habitaciones.id')->all();
    }
}
