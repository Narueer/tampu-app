<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Trabajo;

class InicioController extends Controller
{
    public function inicio()
    {
        $destacadas = Habitacion::publicadas()->withAvg('resenas', 'estrellas')->latest()->take(3)->get();
        $trabajos = Trabajo::publicadas()->with('horarios')->latest()->take(3)->get();

        return view('inicio', compact('destacadas', 'trabajos'));
    }

    public function nosotros()
    {
        return view('nosotros');
    }
}
