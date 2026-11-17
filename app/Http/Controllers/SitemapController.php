<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Trabajo;

class SitemapController extends Controller
{
    // lista de paginas publicas para Google
    public function index()
    {
        $paginas = collect([
            ['url' => route('inicio'), 'fecha' => now()],
            ['url' => route('habitaciones.index'), 'fecha' => now()],
            ['url' => route('trabajos.index'), 'fecha' => now()],
            ['url' => route('nosotros'), 'fecha' => null],
            ['url' => route('contacto'), 'fecha' => null],
        ]);

        foreach (HabitacionController::DISTRITOS as $d) {
            $paginas->push(['url' => route('habitaciones.index', ['distrito' => $d]), 'fecha' => null]);
        }

        foreach (Habitacion::publicadas()->get() as $h) {
            $paginas->push(['url' => route('habitaciones.show', $h), 'fecha' => $h->updated_at]);
        }

        foreach (Trabajo::publicadas()->get() as $t) {
            $paginas->push(['url' => route('trabajos.show', $t), 'fecha' => $t->updated_at]);
        }

        return response()->view('sitemap', ['paginas' => $paginas])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
