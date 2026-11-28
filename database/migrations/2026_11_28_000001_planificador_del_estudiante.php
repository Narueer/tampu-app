<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "Mi planificador": exámenes, entregas, pagos y horas de estudio que el estudiante anota en su calendario
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('titulo', 80);
            $table->string('tipo', 15);                 // examen, entrega, pago, estudio, personal
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();     // sin hora = todo el día
            $table->time('hora_fin')->nullable();
            $table->string('repetir', 10)->default('no'); // no, semanal, mensual
            $table->date('hasta')->nullable();           // fin de la repetición
            $table->decimal('monto', 8, 2)->nullable();  // solo para pagos
            $table->string('nota', 300)->nullable();
            $table->boolean('hecha')->default(false);
            $table->timestamps();
            $table->index(['usuario_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
