<?php

namespace App\Models;

use App\Support\Archivos;
use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    protected $fillable = [
        'titulo', 'descripcion', 'distrito', 'direccion', 'cerca_de',
        'lat', 'lng', 'precio', 'tipo', 'servicios', 'foto', 'estado',
    ];

    public function arrendador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    public function promedio(): float
    {
        return round((float) ($this->resenas_avg_estrellas ?? $this->resenas()->avg('estrellas')), 1);
    }

    // servicios separados por coma
    public function listaServicios(): array
    {
        return $this->servicios ? array_map('trim', explode(',', $this->servicios)) : [];
    }

    public function fotoUrl(): string
    {
        return Archivos::urlFoto($this->foto ?: 'img/logo-160.png');
    }

    // solo de arrendadores verificados
    public function scopePublicadas($query)
    {
        return $query->where('estado', 'disponible')
            ->whereHas('arrendador', fn ($q) => $q->where('verificacion', 'verificado')->where('suspendido', false));
    }
}
