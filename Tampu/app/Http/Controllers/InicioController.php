<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Resena;
use App\Models\Trabajo;
use App\Models\Usuario;

class InicioController extends Controller
{
    public function inicio()
    {
        $destacadas = Habitacion::publicadas()->withAvg('resenas', 'estrellas')->withCount('fotos')->latest()->take(6)->get();
        $trabajos = Trabajo::publicadas()->with('horarios')->latest()->take(3)->get();

        // opiniones reales que dejaron los estudiantes despues de visitar
        $resenas = Resena::with(['estudiante', 'habitacion'])
            ->whereHas('habitacion', fn ($q) => $q->publicadas())
            ->where('estrellas', '>=', 4)->latest()->take(6)->get();

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

        // cuartos cerca de cada universidad
        $universidades = collect(\App\Support\Lugares::UNIVERSIDADES)->map(fn ($u, $clave) => $u + [
            'clave' => $clave,
            'total' => Habitacion::publicadas()->where('cerca_de', $clave)->count(),
        ]);

        $puntos = Habitacion::publicadas()->whereNotNull('lat')->get()->map(fn ($h) => [
            'lat' => (float) $h->lat, 'lng' => (float) $h->lng, 'titulo' => $h->titulo,
            'precio' => (int) $h->precio, 'url' => route('habitaciones.show', $h),
        ])->merge(collect(\App\Support\Lugares::UNIVERSIDADES)->map(fn ($u) => [
            'lat' => $u['lat'], 'lng' => $u['lng'], 'titulo' => $u['nombre'], 'tipo' => 'uni',
        ])->values());

        return view('inicio', [
            'porDistrito' => $porDistrito,
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
