<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajo extends Model
{
    protected $table = 'trabajos';

    protected $fillable = [
        'titulo', 'empresa', 'descripcion', 'distrito',
        'modalidad', 'pago', 'pago_por', 'estado',
        'direccion', 'lat', 'lng', 'requisitos', 'vacantes',
    ];

    public function listaRequisitos(): array
    {
        return $this->requisitos ? array_values(array_filter(array_map('trim', explode("\n", $this->requisitos)))) : [];
    }

    // iniciales de la empresa para su logo
    public function inicialesEmpresa(): string
    {
        $palabras = preg_split('/\s+/', trim($this->empresa));

        return mb_strtoupper(mb_substr($palabras[0], 0, 1) . mb_substr($palabras[1] ?? '', 0, 1));
    }

    public function pagoTexto(): string
    {
        return 'S/ ' . number_format($this->pago, 0) . ' por ' . ($this->pago_por === 'dia' ? 'día' : $this->pago_por);
    }

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
