<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    const DISTRITOS = ['Cusco', 'Wanchaq', 'Santiago', 'San Sebastián', 'San Jerónimo', 'Saylla'];

    // filtros
    public function index(Request $request)
    {
        $consulta = Habitacion::publicadas()->with('arrendador')->withAvg('resenas', 'estrellas')->withCount('fotos')->withCount('resenas')
            ->withCount(['solicitudes as confirmadas_count' => fn ($q) => $q->where('confirmacion', 'coincide')]);

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
            \App\Support\Lugares::filtrar($consulta, $request->cerca_de);
        }

        $orden = $request->orden === 'mayor' ? 'desc' : 'asc';

        // el mapa muestra todos los resultados, pero solo con las columnas que usa
        $paraMapa = (clone $consulta)->setEagerLoads([])->select('habitaciones.id', 'titulo', 'precio', 'lat', 'lng', 'tipo')
            ->whereNotNull('lat')->get();

        // la lista se muestra de 12 en 12 (paginacion)
        $habitaciones = $consulta->orderBy('precio', $orden)->orderBy('habitaciones.id')->paginate(12)->withQueryString();

        // datos para el mapa
        $puntos = $paraMapa->map(fn ($h) => $h->puntoMapa())->values();

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

        $habitacion->load('resenas.estudiante', 'fotos');
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

        $idsFavoritos = $this->idsFavoritos();
        $esFavorito = in_array($habitacion->id, $idsFavoritos);

        // primero las del mismo distrito, luego las de precio parecido
        $similares = Habitacion::publicadas()->withAvg('resenas', 'estrellas')->withCount('fotos')
            ->withCount(['solicitudes as confirmadas_count' => fn ($q) => $q->where('confirmacion', 'coincide')])
            ->where('id', '!=', $habitacion->id)
            ->orderByRaw('case when distrito = ? then 0 else 1 end', [$habitacion->distrito])
            ->orderByRaw('abs(precio - ?)', [$habitacion->precio])
            ->take(6)->get();

        return view('habitaciones.show', compact('habitacion', 'yaSolicito', 'puedeResenar', 'miResena', 'esFavorito', 'similares', 'idsFavoritos'));
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
