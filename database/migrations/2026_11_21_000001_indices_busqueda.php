<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// indices para las busquedas mas usadas (optimizacion de consultas)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('habitaciones', function (Blueprint $table) {
            // filtro del buscador: estado + distrito y orden por precio
            $table->index(['estado', 'distrito', 'precio'], 'hab_estado_distrito_precio');
            // cuartos cerca de cada universidad
            $table->index(['estado', 'cerca_de'], 'hab_estado_cerca');
        });
        Schema::table('trabajos', function (Blueprint $table) {
            $table->index(['estado', 'distrito'], 'trab_estado_distrito');
        });
        Schema::table('solicitudes', function (Blueprint $table) {
            // panel del estudiante: sus solicitudes por estado
            $table->index(['usuario_id', 'estado'], 'sol_usuario_estado');
        });
        Schema::table('mensajes', function (Blueprint $table) {
            // contador de mensajes sin leer de la campana
            $table->index(['conversacion_id', 'leido'], 'men_conversacion_leido');
        });
    }

    public function down(): void
    {
        Schema::table('mensajes', fn (Blueprint $t) => $t->dropIndex('men_conversacion_leido'));
        Schema::table('solicitudes', fn (Blueprint $t) => $t->dropIndex('sol_usuario_estado'));
        Schema::table('trabajos', fn (Blueprint $t) => $t->dropIndex('trab_estado_distrito'));
        Schema::table('habitaciones', function (Blueprint $t) {
            $t->dropIndex('hab_estado_cerca');
            $t->dropIndex('hab_estado_distrito_precio');
        });
    }
};
