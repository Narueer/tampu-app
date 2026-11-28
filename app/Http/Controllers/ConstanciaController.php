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

        $pdf = Pdf::setOption(['isFontSubsettingEnabled' => true])->loadView('pdf.constancia', [
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

        $pdf = Pdf::setOption(['isFontSubsettingEnabled' => true])->loadView('pdf.constancia', [
            'tipo' => 'postulacion',
            'codigo' => 'TMP-P-' . str_pad($postulacion->id, 5, '0', STR_PAD_LEFT),
            'estudiante' => $postulacion->estudiante,
            'contraparte' => $postulacion->trabajo->empleador,
            'postulacion' => $postulacion,
            'trabajo' => $postulacion->trabajo,
        ]);

        return $pdf->download('constancia-postulacion-' . $postulacion->id . '.pdf');
    }

    // página pública para comprobar que una constancia es real (el código está impreso en el PDF)
    public function verificar(string $codigo)
    {
        $codigo = strtoupper(trim($codigo));
        $datos = null;

        if (preg_match('/^TMP-([VP])-(\d{1,9})$/', $codigo, $m)) {
            $id = (int) $m[2];
            if ($m[1] === 'V' && ($s = Solicitud::with('habitacion', 'estudiante')->find($id)) && $s->estado === 'aceptada') {
                $datos = [
                    'tipo' => 'Visita aceptada',
                    'estudiante' => $s->estudiante,
                    'que' => $s->habitacion->titulo,
                    'donde' => $s->habitacion->distrito,
                    'fecha' => $s->updated_at,
                    'extra' => 'Visita programada para el ' . $s->fecha_visita->translatedFormat('j \d\e F \d\e Y'),
                ];
            }
            if ($m[1] === 'P' && ($p = Postulacion::with('trabajo', 'estudiante')->find($id)) && $p->estado === 'aceptada') {
                $datos = [
                    'tipo' => 'Postulación aceptada',
                    'estudiante' => $p->estudiante,
                    'que' => $p->trabajo->titulo,
                    'donde' => $p->trabajo->empresa,
                    'fecha' => $p->updated_at,
                    'extra' => ucfirst($p->trabajo->modalidad),
                ];
            }
        }

        // del estudiante solo se muestra el primer nombre y la inicial del apellido
        if ($datos) {
            $partes = preg_split('/\s+/', trim($datos['estudiante']->nombre));
            $datos['nombre'] = $partes[0] . (isset($partes[1]) ? ' ' . mb_substr($partes[1], 0, 1) . '.' : '');
        }

        return view('constancia-verificar', ['codigo' => $codigo, 'datos' => $datos]);
    }
}
