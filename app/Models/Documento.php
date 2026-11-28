<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// una constancia emitida por TAMPU; el código va en el QR y en el PDF
class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = ['codigo', 'tipo', 'referencia_id', 'usuario_id', 'datos', 'descargas', 'verificaciones', 'revocado_at', 'motivo_revocacion'];

    // sin 0/O ni 1/I/L para que nadie se confunda al escribirlo
    const LETRAS = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    protected function casts(): array
    {
        return ['datos' => 'array', 'revocado_at' => 'datetime'];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    // TMP-V-K7Q2-9XMF: imposible de adivinar (31^8 combinaciones)
    public static function nuevoCodigo(string $tipo): string
    {
        do {
            $azar = '';
            for ($i = 0; $i < 8; $i++) {
                $azar .= self::LETRAS[random_int(0, strlen(self::LETRAS) - 1)];
            }
            $codigo = 'TMP-' . ($tipo === 'visita' ? 'V' : 'P') . '-' . substr($azar, 0, 4) . '-' . substr($azar, 4);
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }

    public function vigente(): bool
    {
        return $this->revocado_at === null;
    }

    public function url(): string
    {
        return route('documento.verificar', $this->codigo);
    }

    public function nombreTipo(): string
    {
        return $this->tipo === 'visita' ? 'Constancia de visita' : 'Constancia de postulación';
    }
}
