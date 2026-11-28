<?php

namespace App\Support;

/**
 * Generador de códigos QR (modo byte, corrección de errores M), sin librerías externas.
 * Basado en el algoritmo de "QR Code generator" de Project Nayuki (licencia MIT).
 * Uso: Qr::svg('https://...')  ->  <svg> listo para un <img src="data:image/svg+xml;base64,...">
 */
class Qr
{
    // nivel M: [palabras de corrección por bloque, cantidad de bloques] por versión 1..40
    private const ECC = [-1, 10, 16, 26, 18, 24, 16, 18, 22, 22, 26, 30, 22, 22, 24, 24, 28, 28, 26, 26, 26, 26, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28];
    private const BLOQUES = [-1, 1, 1, 1, 2, 2, 4, 4, 4, 5, 5, 5, 8, 9, 9, 10, 10, 11, 13, 14, 16, 17, 17, 18, 20, 21, 23, 25, 26, 28, 29, 31, 33, 35, 37, 38, 40, 43, 45, 47, 49];
    private const FORMATO_M = 0;

    private int $version;
    private int $tam;
    private array $modulos = [];
    private array $fijo = [];

    public static function matriz(string $texto): array
    {
        return (new self($texto))->modulos;
    }

    // dibujo en SVG con margen blanco de 4 módulos (lo pide el estándar).
    // Las coordenadas ya van multiplicadas por $escala porque dompdf no escala bien el viewBox.
    public static function svg(string $texto, string $color = '#1f2a24', int $escala = 4): string
    {
        $m = self::matriz($texto);
        $total = (count($m) + 8) * $escala;
        $camino = '';
        foreach ($m as $y => $fila) {
            foreach ($fila as $x => $oscuro) {
                if ($oscuro) {
                    $camino .= 'M' . (($x + 4) * $escala) . ',' . (($y + 4) * $escala) . "h{$escala}v{$escala}h-{$escala}z";
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $total . ' ' . $total . '" width="' . $total . '" height="' . $total . '" shape-rendering="crispEdges">'
            . '<rect width="' . $total . '" height="' . $total . '" fill="#ffffff"/><path fill="' . $color . '" d="' . $camino . '"/></svg>';
    }

    public static function dataUri(string $texto, string $color = '#1f2a24', int $escala = 4): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(self::svg($texto, $color, $escala));
    }

    private function __construct(string $texto)
    {
        $bytes = array_values(unpack('C*', $texto) ?: []);

        // la versión más chica en la que entra el texto
        for ($v = 1; $v <= 40; $v++) {
            $bitsLargo = $v <= 9 ? 8 : 16;
            $usados = 4 + $bitsLargo + count($bytes) * 8;
            if ($usados <= self::palabrasDatos($v) * 8) {
                break;
            }
        }
        if ($v > 40) {
            throw new \InvalidArgumentException('Texto demasiado largo para un QR');
        }
        $this->version = $v;
        $this->tam = $v * 4 + 17;

        // bits: modo byte (0100), largo, datos, terminador y relleno
        $bits = [];
        $poner = function (int $valor, int $largo) use (&$bits) {
            for ($i = $largo - 1; $i >= 0; $i--) {
                $bits[] = ($valor >> $i) & 1;
            }
        };
        $poner(4, 4);
        $poner(count($bytes), $v <= 9 ? 8 : 16);
        foreach ($bytes as $b) {
            $poner($b, 8);
        }
        $capacidad = self::palabrasDatos($v) * 8;
        $poner(0, min(4, $capacidad - count($bits)));
        $poner(0, (8 - count($bits) % 8) % 8);
        for ($relleno = 0xEC; count($bits) < $capacidad; $relleno ^= 0xEC ^ 0x11) {
            $poner($relleno, 8);
        }
        $datos = [];
        foreach (array_chunk($bits, 8) as $grupo) {
            $datos[] = array_reduce($grupo, fn ($a, $b) => ($a << 1) | $b, 0);
        }

        $todo = $this->agregarCorreccion($datos);

        $this->modulos = array_fill(0, $this->tam, array_fill(0, $this->tam, false));
        $this->fijo = $this->modulos;
        $this->dibujarPatrones();
        $this->dibujarPalabras($todo);

        // se prueba cada máscara y se queda la de menor penalidad
        $mejor = 0;
        $menor = PHP_INT_MAX;
        $base = $this->modulos;
        for ($mascara = 0; $mascara < 8; $mascara++) {
            $this->modulos = $base;
            $this->aplicarMascara($mascara);
            $this->dibujarFormato($mascara);
            $p = $this->penalidad();
            if ($p < $menor) {
                $menor = $p;
                $mejor = $mascara;
            }
        }
        $this->modulos = $base;
        $this->aplicarMascara($mejor);
        $this->dibujarFormato($mejor);
    }

    private static function modulosCrudos(int $v): int
    {
        $r = (16 * $v + 128) * $v + 64;
        if ($v >= 2) {
            $n = intdiv($v, 7) + 2;
            $r -= (25 * $n - 10) * $n - 55;
            if ($v >= 7) {
                $r -= 36;
            }
        }

        return $r;
    }

    private static function palabrasDatos(int $v): int
    {
        return intdiv(self::modulosCrudos($v), 8) - self::ECC[$v] * self::BLOQUES[$v];
    }

    private function agregarCorreccion(array $datos): array
    {
        $v = $this->version;
        $nBloques = self::BLOQUES[$v];
        $eccLargo = self::ECC[$v];
        $crudas = intdiv(self::modulosCrudos($v), 8);
        $cortos = $nBloques - $crudas % $nBloques;
        $largoCorto = intdiv($crudas, $nBloques);
        $divisor = self::divisorRS($eccLargo);

        $bloques = [];
        for ($i = 0, $k = 0; $i < $nBloques; $i++) {
            $largo = $largoCorto - $eccLargo + ($i < $cortos ? 0 : 1);
            $dat = array_slice($datos, $k, $largo);
            $k += $largo;
            $ecc = self::restoRS($dat, $divisor);
            if ($i < $cortos) {
                $dat[] = 0;
            }
            $bloques[] = array_merge($dat, $ecc);
        }

        $salida = [];
        for ($i = 0; $i < count($bloques[0]); $i++) {
            foreach ($bloques as $j => $b) {
                if ($i !== $largoCorto - $eccLargo || $j >= $cortos) {
                    $salida[] = $b[$i];
                }
            }
        }

        return $salida;
    }

    private static function multiplicar(int $x, int $y): int
    {
        $z = 0;
        for ($i = 7; $i >= 0; $i--) {
            $z = (($z << 1) ^ (($z >> 7) * 0x11D)) & 0xFF;
            $z ^= (($y >> $i) & 1) * $x;
        }

        return $z;
    }

    private static function divisorRS(int $grado): array
    {
        $r = array_fill(0, $grado, 0);
        $r[$grado - 1] = 1;
        $raiz = 1;
        for ($i = 0; $i < $grado; $i++) {
            for ($j = 0; $j < $grado; $j++) {
                $r[$j] = self::multiplicar($r[$j], $raiz);
                if ($j + 1 < $grado) {
                    $r[$j] ^= $r[$j + 1];
                }
            }
            $raiz = self::multiplicar($raiz, 0x02);
        }

        return $r;
    }

    private static function restoRS(array $datos, array $divisor): array
    {
        $r = array_fill(0, count($divisor), 0);
        foreach ($datos as $b) {
            $factor = $b ^ array_shift($r);
            $r[] = 0;
            foreach ($divisor as $i => $d) {
                $r[$i] ^= self::multiplicar($d, $factor);
            }
        }

        return $r;
    }

    private function poner(int $x, int $y, bool $oscuro): void
    {
        $this->modulos[$y][$x] = $oscuro;
        $this->fijo[$y][$x] = true;
    }

    private function dibujarPatrones(): void
    {
        $n = $this->tam;
        for ($i = 0; $i < $n; $i++) {
            $this->poner(6, $i, $i % 2 === 0);
            $this->poner($i, 6, $i % 2 === 0);
        }
        foreach ([[3, 3], [$n - 4, 3], [3, $n - 4]] as [$x, $y]) {
            for ($dy = -4; $dy <= 4; $dy++) {
                for ($dx = -4; $dx <= 4; $dx++) {
                    $d = max(abs($dx), abs($dy));
                    $xx = $x + $dx;
                    $yy = $y + $dy;
                    if ($xx >= 0 && $xx < $n && $yy >= 0 && $yy < $n) {
                        $this->poner($xx, $yy, $d !== 2 && $d !== 4);
                    }
                }
            }
        }
        $pos = $this->posicionesAlineacion();
        $c = count($pos);
        for ($i = 0; $i < $c; $i++) {
            for ($j = 0; $j < $c; $j++) {
                if (($i === 0 && $j === 0) || ($i === 0 && $j === $c - 1) || ($i === $c - 1 && $j === 0)) {
                    continue;
                }
                for ($dy = -2; $dy <= 2; $dy++) {
                    for ($dx = -2; $dx <= 2; $dx++) {
                        $this->poner($pos[$i] + $dx, $pos[$j] + $dy, max(abs($dx), abs($dy)) !== 1);
                    }
                }
            }
        }
        $this->dibujarFormato(0);
        if ($this->version >= 7) {
            $resto = $this->version;
            for ($i = 0; $i < 12; $i++) {
                $resto = ($resto << 1) ^ (($resto >> 11) * 0x1F25);
            }
            $bits = ($this->version << 12) | $resto;
            for ($i = 0; $i < 18; $i++) {
                $bit = (($bits >> $i) & 1) === 1;
                $a = $n - 11 + $i % 3;
                $b = intdiv($i, 3);
                $this->poner($a, $b, $bit);
                $this->poner($b, $a, $bit);
            }
        }
    }

    private function posicionesAlineacion(): array
    {
        $v = $this->version;
        if ($v === 1) {
            return [];
        }
        $cantidad = intdiv($v, 7) + 2;
        $paso = $v === 32 ? 26 : (int) ceil(($v * 4 + 4) / ($cantidad * 2 - 2)) * 2;
        $r = [6];
        for ($p = $this->tam - 7; count($r) < $cantidad; $p -= $paso) {
            array_splice($r, 1, 0, [$p]);
        }

        return $r;
    }

    private function dibujarFormato(int $mascara): void
    {
        $datos = (self::FORMATO_M << 3) | $mascara;
        $resto = $datos;
        for ($i = 0; $i < 10; $i++) {
            $resto = ($resto << 1) ^ (($resto >> 9) * 0x537);
        }
        $bits = (($datos << 10) | $resto) ^ 0x5412;
        $bit = fn (int $i) => (($bits >> $i) & 1) === 1;
        $n = $this->tam;

        for ($i = 0; $i <= 5; $i++) {
            $this->poner(8, $i, $bit($i));
        }
        $this->poner(8, 7, $bit(6));
        $this->poner(8, 8, $bit(7));
        $this->poner(7, 8, $bit(8));
        for ($i = 9; $i < 15; $i++) {
            $this->poner(14 - $i, 8, $bit($i));
        }
        for ($i = 0; $i < 8; $i++) {
            $this->poner($n - 1 - $i, 8, $bit($i));
        }
        for ($i = 8; $i < 15; $i++) {
            $this->poner(8, $n - 15 + $i, $bit($i));
        }
        $this->poner(8, $n - 8, true);
    }

    private function dibujarPalabras(array $datos): void
    {
        $n = $this->tam;
        $i = 0;
        $total = count($datos) * 8;
        for ($derecha = $n - 1; $derecha >= 1; $derecha -= 2) {
            if ($derecha === 6) {
                $derecha = 5;
            }
            for ($vert = 0; $vert < $n; $vert++) {
                for ($j = 0; $j < 2; $j++) {
                    $x = $derecha - $j;
                    $subiendo = (($derecha + 1) & 2) === 0;
                    $y = $subiendo ? $n - 1 - $vert : $vert;
                    if (! $this->fijo[$y][$x] && $i < $total) {
                        $this->modulos[$y][$x] = (($datos[$i >> 3] >> (7 - ($i & 7))) & 1) === 1;
                        $i++;
                    }
                }
            }
        }
    }

    private function aplicarMascara(int $m): void
    {
        for ($y = 0; $y < $this->tam; $y++) {
            for ($x = 0; $x < $this->tam; $x++) {
                if ($this->fijo[$y][$x]) {
                    continue;
                }
                $invertir = match ($m) {
                    0 => ($x + $y) % 2 === 0,
                    1 => $y % 2 === 0,
                    2 => $x % 3 === 0,
                    3 => ($x + $y) % 3 === 0,
                    4 => (intdiv($x, 3) + intdiv($y, 2)) % 2 === 0,
                    5 => $x * $y % 2 + $x * $y % 3 === 0,
                    6 => ($x * $y % 2 + $x * $y % 3) % 2 === 0,
                    default => (($x + $y) % 2 + $x * $y % 3) % 2 === 0,
                };
                if ($invertir) {
                    $this->modulos[$y][$x] = ! $this->modulos[$y][$x];
                }
            }
        }
    }

    // penalidad simplificada: filas/columnas con 5+ iguales, bloques 2x2 y equilibrio de oscuros
    private function penalidad(): int
    {
        $n = $this->tam;
        $p = 0;
        $oscuros = 0;
        for ($a = 0; $a < $n; $a++) {
            foreach ([true, false] as $horizontal) {
                $racha = 0;
                $anterior = null;
                for ($b = 0; $b < $n; $b++) {
                    $c = $horizontal ? $this->modulos[$a][$b] : $this->modulos[$b][$a];
                    if ($c === $anterior) {
                        $racha++;
                        if ($racha === 5) {
                            $p += 3;
                        } elseif ($racha > 5) {
                            $p++;
                        }
                    } else {
                        $racha = 1;
                        $anterior = $c;
                    }
                }
            }
        }
        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                $c = $this->modulos[$y][$x];
                $oscuros += $c ? 1 : 0;
                if ($x < $n - 1 && $y < $n - 1 && $c === $this->modulos[$y][$x + 1] && $c === $this->modulos[$y + 1][$x] && $c === $this->modulos[$y + 1][$x + 1]) {
                    $p += 3;
                }
            }
        }
        $k = (int) ceil(abs($oscuros * 20 - $n * $n * 10) / ($n * $n)) - 1;

        return $p + max(0, $k) * 10;
    }
}
