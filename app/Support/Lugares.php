<?php

namespace App\Support;

// universidades de Cusco con su ubicacion (OpenStreetMap) para calcular distancias
class Lugares
{
    const UNIVERSIDADES = [
        'UNSAAC' => ['nombre' => 'UNSAAC', 'lugar' => 'Av. de la Cultura 733', 'lat' => -13.5217864, 'lng' => -71.9587621],
        'U. Andina' => ['nombre' => 'U. Andina (Larapa)', 'lugar' => 'Campus Larapa, San Jerónimo', 'lat' => -13.537127, 'lng' => -71.9040268],
        'U. Continental' => ['nombre' => 'U. Continental (Angostura)', 'lugar' => 'Angostura, Saylla', 'lat' => -13.5563057, 'lng' => -71.8563072],
        'IST Túpac Amaru' => ['nombre' => 'IST Túpac Amaru', 'lugar' => 'San Sebastián', 'lat' => -13.5331318, 'lng' => -71.9274753],
    ];

    // distancia en linea recta (formula del haversine), en km
    public static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    // cada universidad con su distancia y cuanto se tarda (a pie hasta 2 km, si no en bus)
    public static function distancias(float $lat, float $lng): array
    {
        $lista = [];
        foreach (self::UNIVERSIDADES as $clave => $u) {
            // se multiplica por 1.3 porque las calles no van en linea recta
            $km = self::km($lat, $lng, $u['lat'], $u['lng']) * 1.3;
            $aPie = $km <= 2;
            $lista[] = $u + [
                'clave' => $clave,
                'km' => round($km, 1),
                'minutos' => max(2, (int) round($aPie ? $km / 4.5 * 60 : $km / 18 * 60 + 5)),
                'modo' => $aPie ? 'a pie' : 'en bus',
            ];
        }
        usort($lista, fn ($a, $b) => $a['km'] <=> $b['km']);

        return $lista;
    }
}
