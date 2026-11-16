<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $table = 'reportes';

    protected $fillable = ['usuario_id', 'habitacion_id', 'trabajo_id', 'motivo', 'detalle', 'estado'];

    const MOTIVOS = [
        'Pide adelanto sin visita',
        'Fotos o datos falsos',
        'Precio distinto al publicado',
        'Oferta de trabajo engañosa',
        'Trato inadecuado',
        'Otro',
    ];

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class);
    }

    // dueño de lo que se reporta
    public function denunciado(): ?Usuario
    {
        return $this->habitacion?->arrendador ?? $this->trabajo?->empleador;
    }

    public function publicacion(): string
    {
        return $this->habitacion?->titulo ?? $this->trabajo?->titulo ?? '—';
    }
}
