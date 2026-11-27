<?php

namespace App\Models;

use App\Support\Archivos;
use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    // tipos de vivienda: valor que se guarda => texto que se muestra
    const TIPOS = [
        'individual' => 'Cuarto individual',
        'compartida' => 'Cuarto compartido',
        'minidepartamento' => 'Minidepartamento',
        'roomies' => 'Depa con roomies',
        'residencia' => 'Casa de estudiantes',
    ];

    protected $fillable = [
        'titulo', 'descripcion', 'distrito', 'direccion', 'cerca_de',
        'lat', 'lng', 'precio', 'tipo', 'servicios', 'foto', 'estado',
        'metros', 'piso', 'bano', 'garantia',
    ];

    public function fotos()
    {
        return $this->hasMany(HabitacionFoto::class)->orderBy('orden')->orderBy('id');
    }

    // la foto principal y despues las de la galeria
    public function todasLasFotos(): array
    {
        $lista = [['url' => $this->fotoUrl(), 'descripcion' => 'Habitación']];
        foreach ($this->fotos as $f) {
            $lista[] = ['url' => $f->url(), 'descripcion' => $f->descripcion];
        }

        return $lista;
    }

    public function distancias(): array
    {
        return $this->lat ? \App\Support\Lugares::distancias((float) $this->lat, (float) $this->lng) : [];
    }

    // icono de Bootstrap Icons para cada servicio
    public static function iconoServicio(string $servicio): string
    {
        return [
            'Agua y luz' => 'lightbulb', 'Wifi' => 'wifi', 'Baño propio' => 'droplet', 'Agua caliente' => 'thermometer-sun',
            'Cocina' => 'cup-hot', 'Amoblado' => 'lamp', 'Lavandería' => 'basket', 'Cochera' => 'bicycle',
            'Terma' => 'thermometer-sun', 'Escritorio' => 'pc-display', 'Cable' => 'tv',
        ][$servicio] ?? 'check2-circle';
    }

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

    // ícono de Bootstrap Icons de cada tipo (tarjetas, ficha y mapas)
    const ICONOS = [
        'individual' => 'door-closed-fill',
        'compartida' => 'people-fill',
        'minidepartamento' => 'building-fill',
        'roomies' => 'person-hearts',
        'residencia' => 'house-door-fill',
    ];

    public function icono(): string
    {
        return self::ICONOS[$this->tipo] ?? 'house-fill';
    }

    // datos de este cuarto para mapa.js
    public function puntoMapa(): array
    {
        return [
            'lat' => (float) $this->lat, 'lng' => (float) $this->lng, 'titulo' => $this->titulo,
            'precio' => (int) $this->precio, 'url' => route('habitaciones.show', $this),
            'clase' => $this->tipo, 'icono' => $this->icono(), 'grupo' => $this->tipoTexto(),
        ];
    }

    public function tipoTexto(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }
}
