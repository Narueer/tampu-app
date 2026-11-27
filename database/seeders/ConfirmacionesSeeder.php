<?php

namespace Database\Seeders;

use App\Models\Solicitud;
use Illuminate\Database\Seeder;

// datos de demostración: marca como "visitado y confirmado" parte de las visitas aceptadas que ya pasaron
// (no toca las de luis@tampu.pe, para poder probar la confirmación con esa cuenta)
class ConfirmacionesSeeder extends Seeder
{
    public function run(): void
    {
        Solicitud::where('estado', 'aceptada')->whereNull('confirmacion')
            ->whereDate('fecha_visita', '<', now())
            ->whereHas('estudiante', fn ($q) => $q->where('email', '!=', 'luis@tampu.pe'))
            ->get()
            ->filter(fn ($s) => $s->id % 5 !== 0)            // ~80 %: no todas confirman
            ->take(40)
            ->each(fn ($s) => $s->update(['confirmacion' => 'coincide', 'confirmado_at' => $s->fecha_visita->copy()->addDay()]));
    }
}
