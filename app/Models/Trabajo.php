<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajo extends Model
{
    protected $table = 'trabajos';

    protected $fillable = [
        'titulo', 'empresa', 'descripcion', 'distrito',
        'modalidad', 'pago', 'pago_por', 'estado',
    ];

    public function empleador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function horarios()
    {
        return $this->hasMany(TrabajoHorario::class);
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class);
    }

    // ej: Lun tarde, Mié tarde
    public function horarioTexto(): string
    {
        return $this->horarios->sortBy(fn ($h) => Horario::orden($h))
            ->map(fn ($h) => Horario::corto($h->dia, $h->turno))
            ->implode(', ');
    }

    // solo de empresas verificadas
    public function scopePublicadas($query)
    {
        return $query->where('estado', 'activa')
            ->whereHas('empleador', fn ($q) => $q->where('verificacion', 'verificado')->where('suspendido', false));
    }
}
