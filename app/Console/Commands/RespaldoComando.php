<?php

namespace App\Console\Commands;

use App\Support\Respaldo;
use Illuminate\Console\Command;

// php artisan tampu:respaldo            -> crea una copia
// php artisan tampu:respaldo --lista    -> muestra las copias guardadas
// php artisan tampu:respaldo --restaurar=respaldos/tampu-2026-11-28-030000.json.gz
class RespaldoComando extends Command
{
    protected $signature = 'tampu:respaldo {--lista} {--restaurar=}';

    protected $description = 'Copias de seguridad de la base de datos de TAMPU';

    public function handle(): int
    {
        if ($this->option('lista')) {
            $this->table(['Archivo', 'KB'], array_map(fn ($f) => [$f['ruta'], round($f['peso'] / 1024)], Respaldo::lista()));

            return self::SUCCESS;
        }
        if ($ruta = $this->option('restaurar')) {
            if (! $this->confirm("Se reemplazarán los datos actuales por los de $ruta. ¿Seguro?")) {
                return self::FAILURE;
            }
            Respaldo::crear('antes de restaurar');   // por si acaso, primero una copia de lo actual
            foreach (Respaldo::restaurar($ruta) as $tabla => $n) {
                $this->line("  $tabla: $n filas");
            }
            $this->info('Listo, datos restaurados.');

            return self::SUCCESS;
        }
        $this->info('Copia creada: ' . Respaldo::crear('desde la consola'));

        return self::SUCCESS;
    }
}
