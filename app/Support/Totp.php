<?php

namespace App\Support;

// codigos de 6 numeros que cambian cada 30 segundos (RFC 6238),
// los mismos que muestran Google Authenticator o Microsoft Authenticator
class Totp
{
    const LETRAS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function nuevoSecreto(): string
    {
        $bytes = random_bytes(20);
        $bits = '';
        foreach (str_split($bytes) as $b) {
            $bits .= str_pad(decbin(ord($b)), 8, '0', STR_PAD_LEFT);
        }

        return implode('', array_map(fn ($g) => self::LETRAS[bindec($g)], str_split($bits, 5)));
    }

    private static function decodificar(string $secreto): string
    {
        $bits = '';
        foreach (str_split(strtoupper($secreto)) as $c) {
            $bits .= str_pad(decbin(strpos(self::LETRAS, $c)), 5, '0', STR_PAD_LEFT);
        }

        return implode('', array_map(fn ($b) => chr(bindec($b)), str_split(substr($bits, 0, intdiv(strlen($bits), 8) * 8), 8)));
    }

    public static function codigo(string $secreto, int $paso): string
    {
        $hash = hash_hmac('sha1', pack('J', $paso), self::decodificar($secreto), true);
        $inicio = ord($hash[19]) & 0x0f;
        $numero = unpack('N', substr($hash, $inicio, 4))[1] & 0x7fffffff;

        return str_pad($numero % 1000000, 6, '0', STR_PAD_LEFT);
    }

    // devuelve el paso de tiempo del codigo si es valido; se acepta 30 s antes o despues por si el reloj del celular no esta exacto
    public static function verificar(string $secreto, string $codigo): ?int
    {
        $codigo = preg_replace('/\D/', '', $codigo);
        $ahora = intdiv(time(), 30);
        foreach ([0, -1, 1] as $d) {
            if (hash_equals(self::codigo($secreto, $ahora + $d), $codigo)) {
                return $ahora + $d;
            }
        }

        return null;
    }

    // lo que va dentro del codigo QR
    public static function enlace(string $secreto, string $email): string
    {
        return 'otpauth://totp/TAMPU:' . rawurlencode($email) . '?secret=' . $secreto . '&issuer=TAMPU&digits=6&period=30';
    }
}
