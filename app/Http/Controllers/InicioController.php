<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Resena;
use App\Models\Trabajo;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InicioController extends Controller
{
    // una linea sobre cada distrito para la seccion de barrios
    const BARRIOS = [
        'Cusco' => 'El centro histórico: plazas, museos y la vida de la ciudad a pocos pasos.',
        'Wanchaq' => 'Comercial y bien conectado, con mercados, bancos y buses a todas partes.',
        'Santiago' => 'Barrio tradicional con precios más bajos y mucho movimiento.',
        'San Sebastián' => 'Residencial y tranquilo, camino a los campus del este.',
        'San Jerónimo' => 'Cerca del campus de Larapa, con calles amplias y menos ruido.',
        'Saylla' => 'Al sureste, la opción más cercana a Angostura.',
    ];

    public function inicio()
    {
        $destacadas = Habitacion::publicadas()->withAvg('resenas', 'estrellas')->withCount('fotos')->latest()->take(6)->get();
        $trabajos = Trabajo::publicadas()->with('horarios')->latest()->take(3)->get();

        // opiniones reales que dejaron los estudiantes despues de visitar
        $resenas = Resena::with(['estudiante', 'habitacion'])
            ->whereHas('habitacion', fn ($q) => $q->publicadas())
            ->where('estrellas', '>=', 4)->latest()->take(6)->get();

        // los numeros y conteos de la portada cambian poco: se guardan 60 segundos en cache
        // para no repetir las mismas consultas agrupadas en cada visita
        ['numeros' => $numeros, 'porDistrito' => $porDistrito, 'mejores' => $mejores, 'porUniversidad' => $porUniversidad] =
            Cache::remember('inicio.resumen.v2', 60, function () {
                $numeros = [
                    'habitaciones' => Habitacion::publicadas()->count(),
                    'trabajos' => Trabajo::publicadas()->count(),
                    'verificados' => Usuario::where('verificacion', 'verificado')->where('suspendido', false)->count(),
                    'distritos' => count(HabitacionController::DISTRITOS),
                    'promedio' => round((float) Resena::avg('estrellas'), 1),
                ];

                // cuantas habitaciones hay por distrito y precio desde
                $porDistrito = Habitacion::publicadas()->selectRaw('distrito, count(*) as total, min(precio) as desde')
                    ->groupBy('distrito')->get()->keyBy('distrito');

                // barrios: el cuarto mejor calificado de cada distrito pone la foto.
                // Antes era una consulta por distrito (6) que traia todos los cuartos;
                // ahora es una sola consulta que trae solo un cuarto por distrito
                $promedio = '(select coalesce(avg(estrellas), 0) from resenas where resenas.habitacion_id = habitaciones.id)';
                $mejores = DB::query()->fromSub(
                    Habitacion::publicadas()->select('id', 'distrito', 'foto')
                        ->selectRaw("row_number() over (partition by distrito order by $promedio desc, id) as puesto"),
                    'm'
                )->where('puesto', 1)->get()->keyBy('distrito');

                // cuartos cerca de cada universidad: un solo conteo agrupado en vez de uno por universidad
                // (se traen solo 3 columnas de cada cuarto y se cuenta en PHP: una consulta para los 30 lugares)
                $cuartos = Habitacion::publicadas()->get(['lat', 'lng', 'cerca_de']);
                $porUniversidad = collect(\App\Support\Lugares::UNIVERSIDADES)->map(fn ($u, $clave) => $cuartos
                    ->filter(fn ($h) => \App\Support\Lugares::estaCerca($h->lat !== null ? (float) $h->lat : null, $h->lng !== null ? (float) $h->lng : null, $h->cerca_de, $clave))
                    ->count());

                return compact('numeros', 'porDistrito', 'mejores', 'porUniversidad');
            });

        $barrios = collect(HabitacionController::DISTRITOS)->filter(fn ($d) => isset($porDistrito[$d]))->map(fn ($d) => [
            'nombre' => $d,
            'total' => $porDistrito[$d]->total,
            'desde' => $porDistrito[$d]->desde,
            'texto' => self::BARRIOS[$d] ?? '',
            'foto' => isset($mejores[$d]) ? (new Habitacion(['foto' => $mejores[$d]->foto]))->fotoUrl() : null,
        ])->values();

        $universidades = collect(\App\Support\Lugares::UNIVERSIDADES)->map(fn ($u, $clave) => $u + [
            'clave' => $clave,
            'total' => (int) ($porUniversidad[$clave] ?? 0),
            'icono' => \App\Support\Lugares::icono($clave),
            'foto' => \App\Support\Lugares::foto($clave),
            'credito' => \App\Support\Lugares::credito($clave),
        ]);

        // para el mapa solo hacen falta 5 columnas, no todas
        $puntos = Habitacion::publicadas()->whereNotNull('lat')->get(['id', 'titulo', 'precio', 'lat', 'lng', 'tipo'])->map(fn ($h) => $h->puntoMapa())->merge(collect(\App\Support\Lugares::conUbicacion())->keys()->map(fn ($clave) => \App\Support\Lugares::marcador($clave)));

        return view('inicio', [
            'porDistrito' => $porDistrito,
            'barrios' => $barrios,
            'universidades' => $universidades,
            'puntos' => $puntos,
            'destacadas' => $destacadas,
            'trabajos' => $trabajos,
            'resenas' => $resenas,
            'numeros' => $numeros,
            'distritos' => HabitacionController::DISTRITOS,
        ]);
    }

    public function nosotros()
    {
        return view('nosotros');
    }
}
