<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';

    public $timestamps = false;

    protected $fillable = ['dia', 'turno'];

    const DIAS = [
        'lunes' => 'Lun', 'martes' => 'Mar', 'miercoles' => 'Mié', 'jueves' => 'Jue',
        'viernes' => 'Vie', 'sabado' => 'Sáb', 'domingo' => 'Dom',
    ];

    const TURNOS = ['manana' => 'mañana', 'tarde' => 'tarde', 'noche' => 'noche'];

    public static function corto($dia, $turno): string
    {
        return self::DIAS[$dia] . ' ' . self::TURNOS[$turno];
    }

    public static function orden($h): int
    {
        return array_search($h->dia, array_keys(self::DIAS)) * 3 + array_search($h->turno, array_keys(self::TURNOS));
    }
}
