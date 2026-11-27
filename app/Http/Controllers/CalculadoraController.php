<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Support\Lugares;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// "¿Me alcanza?": compara el presupuesto del estudiante con lo que cuesta vivir en cada distrito
class CalculadoraController extends Controller
{
    public function index(Request $request)
    {
        // por distrito: alquiler promedio y mínimo, y a qué distancia promedio quedan sus cuartos de cada lugar de estudio
        $distritos = Cache::remember('calculadora.distritos', 600, function () {
            $cuartos = Habitacion::publicadas()->get(['distrito', 'precio', 'lat', 'lng', 'servicios']);

            return $cuartos->groupBy('distrito')->map(function ($lista, $distrito) {
                $conUbicacion = $lista->whereNotNull('lat');
                $km = [];
                foreach (Lugares::conUbicacion() as $clave => $u) {
                    $km[$clave] = $conUbicacion->isEmpty() ? null : round($conUbicacion->avg(
                        fn ($h) => Lugares::km((float) $h->lat, (float) $h->lng, $u['lat'], $u['lng']) * 1.3), 1);
                }

                return [
                    'distrito' => $distrito,
                    'promedio' => (int) round($lista->avg('precio')),
                    'minimo' => (int) $lista->min('precio'),
                    'cuartos' => $lista->count(),
                    // qué parte de los cuartos ya incluye agua, luz e internet
                    'incluyeServicios' => round($lista->filter(fn ($h) => str_contains($h->servicios ?? '', 'Agua y luz') && str_contains($h->servicios ?? '', 'Wifi'))->count() / max(1, $lista->count()), 2),
                    'km' => $km,
                ];
            })->sortBy('promedio')->values();
        });

        // si viene desde un cuarto: su precio y la universidad más cercana
        $cuarto = $request->filled('cuarto') ? Habitacion::publicadas()->find($request->cuarto) : null;
        $uniSugerida = null;
        if ($cuarto && $cuarto->lat) {
            $uniSugerida = $cuarto->distancias()[0]['clave'] ?? null;
        }
        $lugares = collect(Lugares::conUbicacion())->map(fn ($u) => $u['nombre']);

        return view('calculadora', [
            'distritos' => $distritos,
            'lugares' => $lugares,
            'cuarto' => $cuarto,
            'uni' => Lugares::buscar($request->uni) ?? $uniSugerida ?? 'UNSAAC',
            'presupuesto' => (int) $request->input('presupuesto', 900),
            'guardado' => $request->user()?->presupuesto,
            'pestana' => $request->input('ver', $request->filled('cuarto') ? 'distritos' : 'mio'),
        ]);
    }

    // guarda "Mi presupuesto" en la cuenta (sin sesión se queda solo en el navegador)
    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'ingreso' => ['required', 'numeric', 'min:0', 'max:100000'],
            'periodo' => ['required', 'in:mes,semana'],
            'cuarto' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'ahorro' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'items' => ['present', 'array', 'max:30'],
            'items.*.nombre' => ['required', 'string', 'max:40'],
            'items.*.monto' => ['required', 'numeric', 'min:0', 'max:100000'],
            'items.*.cada' => ['required', 'in:dia,clase,semana,mes'],
            'items.*.icono' => ['nullable', 'string', 'max:30', 'regex:/^[a-z0-9-]+$/'],
            'dias' => ['nullable', 'integer', 'min:1', 'max:7'],
        ]);
        $datos['actualizado'] = now()->toIso8601String();
        $request->user()->forceFill(['presupuesto' => $datos])->save();

        return response()->json(['ok' => true, 'actualizado' => $datos['actualizado']]);
    }
}
