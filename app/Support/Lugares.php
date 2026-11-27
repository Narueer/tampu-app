<?php

namespace App\Support;

/**
 * Universidades e institutos de Cusco.
 *
 * - lat/lng: ubicación confirmada en OpenStreetMap. Solo se listan lugares con ubicación,
 *   para no mostrar distancias inventadas (para agregar uno nuevo, poner sus coordenadas).
 * - palabras: cómo la escribe la gente (en minúsculas y sin tildes); sirven para el
 *   filtro "cerca de" y para el asistente virtual.
 * - Las 4 primeras claves no se cambian: ya están guardadas en los cuartos publicados.
 */
class Lugares
{
    const TIPOS = [
        'universidad' => 'Universidades',
        'tecnologico' => 'Institutos tecnológicos',
        'pedagogico' => 'Institutos pedagógicos',
        'gastronomia' => 'Gastronomía y turismo',
        'tecnica' => 'Formación técnica',
    ];

    // radio para "cerca de": unos 2 km alrededor del local
    const RADIO_LAT = 0.018;
    const RADIO_LNG = 0.0185;

    const UNIVERSIDADES = [
        // universidades
        'UNSAAC' => ['nombre' => 'UNSAAC', 'completo' => 'Universidad Nacional de San Antonio Abad del Cusco', 'tipo' => 'universidad',
            'lugar' => 'Av. de la Cultura 733', 'lat' => -13.5217864, 'lng' => -71.9587621,
            'palabras' => ['unsaac', 'san antonio abad', 'la san antonio', 'la nacional', 'perayoc']],
        'U. Andina' => ['nombre' => 'U. Andina (Larapa)', 'completo' => 'Universidad Andina del Cusco', 'tipo' => 'universidad',
            'lugar' => 'Campus Larapa, San Jerónimo', 'lat' => -13.537127, 'lng' => -71.9040268,
            'palabras' => ['andina', 'uac', 'larapa']],
        'U. Continental' => ['nombre' => 'U. Continental (Angostura)', 'completo' => 'Universidad Continental – Campus Cusco', 'tipo' => 'universidad',
            'lugar' => 'Angostura, Saylla', 'lat' => -13.5563057, 'lng' => -71.8563072,
            'palabras' => ['continental', 'ucontinental']],
        'UTEA' => ['nombre' => 'UTEA', 'completo' => 'Universidad Tecnológica de los Andes – Filial Cusco', 'tipo' => 'universidad',
            'lugar' => 'Av. Grau, Cusco', 'lat' => -13.524033, 'lng' => -71.977881,
            'palabras' => ['utea', 'tecnologica de los andes']],

        // institutos tecnológicos
        'IST Túpac Amaru' => ['nombre' => 'IST Túpac Amaru', 'completo' => 'Instituto Superior Tecnológico Público Túpac Amaru', 'tipo' => 'tecnologico',
            'lugar' => 'San Sebastián', 'lat' => -13.5331318, 'lng' => -71.9274753,
            'palabras' => ['tupac amaru']],
        'Khipu' => ['nombre' => 'Instituto Khipu', 'completo' => 'Instituto Superior Tecnológico Khipu', 'tipo' => 'tecnologico',
            'lugar' => 'Av. Manantiales, Urb. Magisterial', 'lat' => -13.524311, 'lng' => -71.943873,
            'palabras' => ['khipu', 'kipu', 'quipu']],
        'Antonio Lorena' => ['nombre' => 'Instituto Antonio Lorena', 'completo' => 'Instituto Superior Tecnológico Antonio Lorena', 'tipo' => 'tecnologico',
            'lugar' => 'Av. de la Cultura, Urb. Magisterial', 'lat' => -13.527119, 'lng' => -71.945426,
            'palabras' => ['instituto antonio lorena', 'antonio lorena instituto', 'el lorena']],
        'Tuinen Star' => ['nombre' => 'Tuinen Star', 'completo' => 'Instituto Tuinen Star', 'tipo' => 'tecnologico',
            'lugar' => 'Calle Bellavista, San Sebastián', 'lat' => -13.5294, 'lng' => -71.9332,
            'palabras' => ['tuinen']],

        // institutos pedagógicos
        'Pukllasunchis' => ['nombre' => 'Pedagógico Pukllasunchis', 'completo' => 'Instituto Superior Pedagógico Pukllasunchis', 'tipo' => 'pedagogico',
            'lugar' => 'San Blas, Cusco', 'lat' => -13.512765, 'lng' => -71.975132,
            'palabras' => ['pukllasunchis', 'puckllasunchis']],

        // gastronomía y turismo
        'CENFOTUR' => ['nombre' => 'CENFOTUR Cusco', 'completo' => 'CENFOTUR – Filial Cusco', 'tipo' => 'gastronomia',
            'lugar' => 'Av. Confraternidad 438, Wanchaq', 'lat' => -13.527, 'lng' => -71.968,
            'palabras' => ['cenfotur']],

        // formación técnica
        'SENATI' => ['nombre' => 'SENATI Cusco', 'completo' => 'SENATI – Cusco', 'tipo' => 'tecnica',
            'lugar' => 'Av. Micaela Bastidas, Wanchaq', 'lat' => -13.523995, 'lng' => -71.961844,
            'palabras' => ['senati']],
        'SENCICO' => ['nombre' => 'SENCICO Cusco', 'completo' => 'SENCICO – Cusco', 'tipo' => 'tecnica',
            'lugar' => 'Av. Tomasa Tito Condemayta 411, Wanchaq', 'lat' => -13.525228, 'lng' => -71.961971,
            'palabras' => ['sencico']],
        'Leandro Alviña' => ['nombre' => 'Instituto de Música Leandro Alviña', 'completo' => 'Instituto Superior de Música Leandro Alviña Miranda', 'tipo' => 'tecnica',
            'lugar' => 'Calle Tocuyeros, San Blas', 'lat' => -13.515625, 'lng' => -71.975073,
            'palabras' => ['leandro alvina', 'alvina', 'conservatorio', 'escuela de musica']],
    ];

    // las que tienen ubicación confirmada (para mapas y distancias)
    public static function conUbicacion(): array
    {
        return array_filter(self::UNIVERSIDADES, fn ($u) => $u['lat'] !== null);
    }

    // agrupadas por tipo, para listas y selects
    public static function porTipo(): array
    {
        $grupos = [];
        foreach (self::TIPOS as $tipo => $titulo) {
            $grupos[$titulo] = array_filter(self::UNIVERSIDADES, fn ($u) => $u['tipo'] === $tipo);
        }

        return $grupos;
    }

    // busca la clave a partir de lo que alguien escribió ("unsaac", "Senati", "cerca de la andina")
    public static function buscar(?string $texto): ?string
    {
        if (! filled($texto)) {
            return null;
        }
        if (isset(self::UNIVERSIDADES[$texto])) {
            return $texto;
        }
        $t = ' ' . strtolower(\Illuminate\Support\Str::ascii($texto)) . ' ';
        foreach (self::UNIVERSIDADES as $clave => $u) {
            $variantes = array_merge($u['palabras'], [strtolower(\Illuminate\Support\Str::ascii($clave)), strtolower(\Illuminate\Support\Str::ascii($u['completo']))]);
            foreach ($variantes as $p) {
                if (preg_match('/(?<![a-z])' . preg_quote($p, '/') . '(?![a-z])/', $t)) {
                    return $clave;
                }
            }
        }

        return null;
    }

    /**
     * Filtra cuartos "cerca de" un lugar: los que están a unos 2 km (si tiene ubicación)
     * o los que el arrendador marcó como cerca de ese lugar.
     */
    public static function filtrar($consulta, string $texto)
    {
        $clave = self::buscar($texto);
        if (! $clave) {
            return $consulta->whereLike('cerca_de', '%' . $texto . '%');
        }
        $u = self::UNIVERSIDADES[$clave];

        return $consulta->where(function ($q) use ($clave, $u) {
            $q->where('cerca_de', $clave)->orWhereLike('cerca_de', '%' . $clave . '%');
            foreach ($u['palabras'] as $p) {
                $q->orWhereLike('cerca_de', '%' . $p . '%');
            }
            if ($u['lat'] !== null) {
                $q->orWhere(fn ($c) => $c->whereBetween('lat', [$u['lat'] - self::RADIO_LAT, $u['lat'] + self::RADIO_LAT])
                    ->whereBetween('lng', [$u['lng'] - self::RADIO_LNG, $u['lng'] + self::RADIO_LNG]));
            }
        });
    }

    // lo mismo que filtrar(), pero para un cuarto ya cargado (sirve para contar sin hacer 30 consultas)
    public static function estaCerca(?float $lat, ?float $lng, ?string $cercaDe, string $clave): bool
    {
        $u = self::UNIVERSIDADES[$clave];
        if ($cercaDe && self::buscar($cercaDe) === $clave) {
            return true;
        }

        return $u['lat'] !== null && $lat !== null
            && abs($lat - $u['lat']) <= self::RADIO_LAT && abs($lng - $u['lng']) <= self::RADIO_LNG;
    }

    // distancia en linea recta (formula del haversine), en km
    public static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    // cada lugar con ubicación, con su distancia y cuanto se tarda (a pie hasta 2 km, si no en bus)
    public static function distancias(float $lat, float $lng): array
    {
        $lista = [];
        foreach (self::conUbicacion() as $clave => $u) {
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
