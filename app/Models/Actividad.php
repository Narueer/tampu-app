<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// algo que el estudiante anotó en su planificador
class Actividad extends Model
{
    protected $table = 'actividades';

    protected $fillable = ['titulo', 'tipo', 'fecha', 'hora_inicio', 'hora_fin', 'repetir', 'hasta', 'monto', 'nota', 'hecha'];

    // nombre, icono y color de cada tipo (los mismos en el calendario, la lista y el formulario)
    const TIPOS = [
        'examen' => ['Examen', 'bi-mortarboard', '#8e4b46'],
        'entrega' => ['Entrega o tarea', 'bi-journal-check', '#53677a'],
        'pago' => ['Pago', 'bi-cash-coin', '#86652f'],
        'estudio' => ['Estudio', 'bi-book', '#5f7c64'],
        'personal' => ['Personal', 'bi-star', '#6b6f6a'],
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'hasta' => 'date',
            'monto' => 'decimal:2',
            'hecha' => 'boolean',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function color(): string
    {
        return self::TIPOS[$this->tipo][2] ?? '#6b6f6a';
    }

    public function icono(): string
    {
        return self::TIPOS[$this->tipo][1] ?? 'bi-star';
    }

    // fechas en que ocurre entre $desde y $hasta (incluidas), contando las repeticiones
    public function fechasEntre(\Carbon\CarbonInterface $desde, \Carbon\CarbonInterface $hasta): array
    {
        $fechas = [];
        $fin = $this->hasta && $this->hasta->lt($hasta) ? $this->hasta : $hasta;
        $dia = $this->fecha->copy();
        $paso = 0;
        while ($dia->lte($fin) && $paso < 400) {
            if ($dia->gte($desde->copy()->startOfDay())) {
                $fechas[] = $dia->copy();
            }
            if ($this->repetir === 'no') {
                break;
            }
            $paso++;
            // mensual: el mismo día de cada mes (el 31 cae en el último día de los meses cortos)
            $dia = $this->repetir === 'semanal' ? $this->fecha->copy()->addWeeks($paso) : $this->fecha->copy()->addMonthsNoOverflow($paso);
        }

        return $fechas;
    }
}
