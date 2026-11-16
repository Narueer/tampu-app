<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\Solicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ConstanciaController extends Controller
{
    public function visita(Request $request, Solicitud $solicitud)
    {
        $yo = $request->user();
        abort_unless($solicitud->estado === 'aceptada', 404);
        abort_unless(in_array($yo->id, [$solicitud->usuario_id, $solicitud->habitacion->usuario_id]), 403);

        $pdf = Pdf::loadView('pdf.constancia', [
            'tipo' => 'visita',
            'codigo' => 'TMP-V-' . str_pad($solicitud->id, 5, '0', STR_PAD_LEFT),
            'estudiante' => $solicitud->estudiante,
            'contraparte' => $solicitud->habitacion->arrendador,
            'solicitud' => $solicitud,
            'habitacion' => $solicitud->habitacion,
        ]);

        return $pdf->download('constancia-visita-' . $solicitud->id . '.pdf');
    }

    public function postulacion(Request $request, Postulacion $postulacion)
    {
        $yo = $request->user();
        abort_unless($postulacion->estado === 'aceptada', 404);
        abort_unless(in_array($yo->id, [$postulacion->usuario_id, $postulacion->trabajo->usuario_id]), 403);

        $pdf = Pdf::loadView('pdf.constancia', [
            'tipo' => 'postulacion',
            'codigo' => 'TMP-P-' . str_pad($postulacion->id, 5, '0', STR_PAD_LEFT),
            'estudiante' => $postulacion->estudiante,
            'contraparte' => $postulacion->trabajo->empleador,
            'postulacion' => $postulacion,
            'trabajo' => $postulacion->trabajo,
        ]);

        return $pdf->download('constancia-postulacion-' . $postulacion->id . '.pdf');
    }
}
