<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Postulacion;
use App\Models\Solicitud;
use App\Support\Qr;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ConstanciaController extends Controller
{
    public function visita(Request $request, Solicitud $solicitud)
    {
        $yo = $request->user();
        abort_unless($solicitud->estado === 'aceptada', 404);
        abort_unless(in_array($yo->id, [$solicitud->usuario_id, $solicitud->habitacion->usuario_id]), 403);

        $h = $solicitud->habitacion;
        $documento = $this->emitir('visita', $solicitud->id, $solicitud->usuario_id, [
            'estudiante' => $solicitud->estudiante->nombre,
            'dni' => $solicitud->estudiante->dni,
            'contraparte' => $h->arrendador->nombre,
            'que' => $h->titulo,
            'donde' => $h->distrito,
            'aceptada' => $solicitud->updated_at->toIso8601String(),
            'extra' => 'Visita para el ' . $solicitud->fecha_visita->translatedFormat('j \d\e F \d\e Y'),
        ]);

        return $this->pdf($documento, 'constancia-visita-' . $solicitud->id . '.pdf', [
            'tipo' => 'visita',
            'estudiante' => $solicitud->estudiante,
            'contraparte' => $h->arrendador,
            'solicitud' => $solicitud,
            'habitacion' => $h,
        ]);
    }

    public function postulacion(Request $request, Postulacion $postulacion)
    {
        $yo = $request->user();
        abort_unless($postulacion->estado === 'aceptada', 404);
        abort_unless(in_array($yo->id, [$postulacion->usuario_id, $postulacion->trabajo->usuario_id]), 403);

        $t = $postulacion->trabajo;
        $documento = $this->emitir('postulacion', $postulacion->id, $postulacion->usuario_id, [
            'estudiante' => $postulacion->estudiante->nombre,
            'dni' => $postulacion->estudiante->dni,
            'contraparte' => $t->empresa,
            'que' => $t->titulo,
            'donde' => $t->empresa,
            'aceptada' => $postulacion->updated_at->toIso8601String(),
            'extra' => ucfirst($t->modalidad) . ' · ' . $t->horarioTexto(),
        ]);

        return $this->pdf($documento, 'constancia-postulacion-' . $postulacion->id . '.pdf', [
            'tipo' => 'postulacion',
            'estudiante' => $postulacion->estudiante,
            'contraparte' => $t->empleador,
            'postulacion' => $postulacion,
            'trabajo' => $t,
        ]);
    }

    // página que abre el QR: dice si la constancia es auténtica y sigue vigente
    public function verificar(Request $request, string $codigo)
    {
        $codigo = strtoupper(trim($codigo));
        $documento = Documento::where('codigo', $codigo)->first();
        $datos = null;

        if ($documento) {
            $documento->increment('verificaciones');
            $d = $documento->datos;
            // del estudiante solo el primer nombre y la inicial; el DNI con asteriscos
            $partes = preg_split('/\s+/', trim($d['estudiante'] ?? ''));
            $datos = [
                'tipo' => $documento->tipo === 'visita' ? 'Visita aceptada' : 'Postulación aceptada',
                'nombre' => $partes[0] . (isset($partes[1]) ? ' ' . mb_substr($partes[1], 0, 1) . '.' : ''),
                'dni' => isset($d['dni']) ? '••••' . substr($d['dni'], -4) : '—',
                'que' => $d['que'] ?? '—',
                'donde' => $d['donde'] ?? '—',
                'extra' => $d['extra'] ?? '',
                'fecha' => \Illuminate\Support\Carbon::parse($d['aceptada'] ?? $documento->created_at),
                'emitida' => $documento->created_at,
                'vigente' => $documento->vigente() && $this->sigueAceptada($documento),
                'motivo' => $documento->motivo_revocacion,
            ];
        }

        return view('constancia-verificar', ['codigo' => $codigo, 'datos' => $datos, 'documento' => $documento]);
    }

    // el formulario "verificar otro código" y los enlaces viejos
    public function buscar(Request $request, ?string $codigo = null)
    {
        $codigo = strtoupper(trim($codigo ?? (string) $request->query('codigo')));

        return $codigo ? redirect()->route('documento.verificar', $codigo) : redirect()->route('inicio');
    }

    private function emitir(string $tipo, int $id, int $usuarioId, array $datos): Documento
    {
        $documento = Documento::firstOrCreate(
            ['tipo' => $tipo, 'referencia_id' => $id],
            ['codigo' => Documento::nuevoCodigo($tipo), 'usuario_id' => $usuarioId, 'datos' => $datos]
        );
        $documento->increment('descargas');

        return $documento;
    }

    private function pdf(Documento $documento, string $archivo, array $vista)
    {
        abort_if($documento->revocado_at, 410, 'Esta constancia fue anulada por el administrador.');

        $pdf = Pdf::setOption(['isFontSubsettingEnabled' => true])->loadView('pdf.constancia', $vista + [
            'codigo' => $documento->codigo,
            'verificar' => $documento->url(),
            'qr' => Qr::dataUri($documento->url(), '#1f2a24'),
            'emitida' => $documento->created_at,
        ]);

        return $pdf->download($archivo);
    }

    private function sigueAceptada(Documento $documento): bool
    {
        $modelo = $documento->tipo === 'visita' ? Solicitud::find($documento->referencia_id) : Postulacion::find($documento->referencia_id);

        return $modelo && $modelo->estado === 'aceptada';
    }
}
