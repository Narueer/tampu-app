<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Trabajo;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    // lista de paginas publicas para Google, armada como texto xml
    public function index()
    {
        $paginas = [
            [route('inicio'), now()],
            [route('habitaciones.index'), now()],
            [route('trabajos.index'), now()],
            [route('nosotros'), null],
            [route('contacto'), null],
        ];

        foreach (HabitacionController::DISTRITOS as $d) {
            $paginas[] = [route('habitaciones.index', ['distrito' => $d]), null];
        }

        // si falla la consulta igual se entregan las paginas principales
        try {
            foreach (Habitacion::publicadas()->get(['habitaciones.id', 'habitaciones.updated_at']) as $h) {
                $paginas[] = [route('habitaciones.show', $h->id), $h->updated_at];
            }
            foreach (Trabajo::publicadas()->get(['trabajos.id', 'trabajos.updated_at']) as $t) {
                $paginas[] = [route('trabajos.show', $t->id), $t->updated_at];
            }
        } catch (\Throwable $e) {
            Log::error('Sitemap: ' . $e->getMessage());
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($paginas as [$url, $fecha]) {
            $xml .= '  <url><loc>' . htmlspecialchars($url, ENT_XML1) . '</loc>';
            if ($fecha) {
                $xml .= '<lastmod>' . $fecha->toAtomString() . '</lastmod>';
            }
            $xml .= "</url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
