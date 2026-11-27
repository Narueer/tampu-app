<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Lo que hay alrededor de un cuarto: paraderos, líneas de bus, comisarías y centros de salud.
 * Datos de OpenStreetMap (resources/data/cusco-osm.json, licencia ODbL), descargados con Overpass.
 * Para actualizarlos: ver recursos/osm/LEEME.md.
 */
class Entorno
{
    const CERCA_LINEA = 0.35;     // km: una línea "pasa cerca" si su recorrido queda a menos de 350 m
    const CERCA_LUGAR = 1.2;      // km: comisarías y centros de salud que se muestran
    const CAMINAR_KMH = 4.5;

    public static function datos(): array
    {
        return Cache::rememberForever('entorno.osm.' . filemtime(resource_path('data/cusco-osm.json')), function () {
            return json_decode(file_get_contents(resource_path('data/cusco-osm.json')), true);
        });
    }

    public static function fuente(): string
    {
        return self::datos()['fuente'] ?? '© colaboradores de OpenStreetMap';
    }

    // distancia aproximada en km (suficiente para distancias cortas dentro de Cusco)
    private static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return Lugares::km($lat1, $lng1, $lat2, $lng2);
    }

    // distancia mínima (km) de un punto a una línea de bus (a sus puntos, que están cada ~60 m)
    private static function aLinea(float $lat, float $lng, array $linea): float
    {
        $min = INF;
        foreach ($linea['tramos'] as $tramo) {
            foreach ($tramo as [$la, $lo]) {
                // descarte rápido antes de calcular
                if (abs($la - $lat) > 0.01 || abs($lo - $lng) > 0.01) {
                    continue;
                }
                $d = self::km($lat, $lng, $la, $lo);
                if ($d < $min) {
                    $min = $d;
                }
            }
        }

        return $min;
    }

    // lugares de un tipo ordenados por cercanía
    public static function cercanos(float $lat, float $lng, string $tipo, float $radio, int $max = 4): array
    {
        $lista = [];
        foreach (self::datos()['lugares'] as $p) {
            if ($p['tipo'] !== $tipo || abs($p['lat'] - $lat) > 0.02 || abs($p['lng'] - $lng) > 0.02) {
                continue;
            }
            $d = self::km($lat, $lng, $p['lat'], $p['lng']);
            if ($d <= $radio) {
                $lista[] = $p + ['km' => round($d, 2), 'min' => max(1, (int) round($d * 1.25 / self::CAMINAR_KMH * 60))];
            }
        }
        usort($lista, fn ($a, $b) => $a['km'] <=> $b['km']);

        return array_slice($lista, 0, $max);
    }

    /**
     * Para cada universidad o instituto: qué líneas pasan cerca del cuarto y también cerca del lugar.
     * Solo se calcula para los que quedan a más de 1,2 km (más cerca, conviene caminar).
     */
    public static function comoLlegar(float $lat, float $lng): array
    {
        return Cache::remember('entorno.llegar.' . round($lat, 4) . ',' . round($lng, 4), 86400, function () use ($lat, $lng) {
            $lineas = self::datos()['lineas'];
            $cercaCuarto = [];
            foreach ($lineas as $l) {
                $d = self::aLinea($lat, $lng, $l);
                if ($d <= self::CERCA_LINEA) {
                    $cercaCuarto[] = $l + ['dCuarto' => $d];
                }
            }
            $resultado = [];
            foreach (Lugares::conUbicacion() as $clave => $u) {
                $directo = self::km($lat, $lng, $u['lat'], $u['lng']);
                if ($directo < 1.2) {
                    $resultado[$clave] = ['caminar' => true, 'lineas' => []];
                    continue;
                }
                $sirven = [];
                foreach ($cercaCuarto as $l) {
                    $dUni = self::aLinea($u['lat'], $u['lng'], $l);
                    if ($dUni <= self::CERCA_LINEA + 0.1) {
                        $sirven[] = ['ref' => $l['ref'], 'nombre' => $l['nombre'], 'caminar' => (int) round(($l['dCuarto'] + $dUni) * 1.25 / self::CAMINAR_KMH * 60)];
                    }
                }
                usort($sirven, fn ($a, $b) => $a['caminar'] <=> $b['caminar']);
                $resultado[$clave] = ['caminar' => false, 'lineas' => array_slice($sirven, 0, 3)];
            }

            return $resultado;
        });
    }

    // puntos para el mapa del detalle: comisarías, salud y paraderos cercanos
    public static function puntosMapa(float $lat, float $lng): array
    {
        $puntos = [];
        foreach ([['policia', 'shield-fill', 'Comisaría', self::CERCA_LUGAR, 3], ['salud', 'heart-pulse-fill', 'Centro de salud', self::CERCA_LUGAR, 3], ['paradero', 'bus-front-fill', 'Paradero', 0.5, 4]] as [$tipo, $icono, $grupo, $radio, $max]) {
            foreach (self::cercanos($lat, $lng, $tipo, $radio, $max) as $p) {
                $puntos[] = ['lat' => $p['lat'], 'lng' => $p['lng'], 'titulo' => $p['nombre'], 'tipo' => 'entorno', 'clase' => $tipo,
                    'icono' => $icono, 'grupo' => $grupo, 'detalle' => $p['min'] . ' min a pie'];
            }
        }

        return $puntos;
    }
}
