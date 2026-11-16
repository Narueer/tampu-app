<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trabajos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('titulo', 120);
            $table->string('empresa', 100);
            $table->text('descripcion');
            $table->string('distrito', 40);
            $table->enum('modalidad', ['medio tiempo', 'por horas', 'practicas']);
            $table->decimal('pago', 7, 2);
            $table->enum('pago_por', ['hora', 'dia', 'mes']);
            $table->enum('estado', ['activa', 'cerrada'])->default('activa');
            $table->timestamps();
        });

        // turnos de cada trabajo
        Schema::create('trabajo_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajo_id')->constrained('trabajos')->cascadeOnDelete();
            $table->enum('dia', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']);
            $table->enum('turno', ['manana', 'tarde', 'noche']);
            $table->unique(['trabajo_id', 'dia', 'turno']);
        });

        // horario de clases
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->enum('dia', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']);
            $table->enum('turno', ['manana', 'tarde', 'noche']);
            $table->unique(['usuario_id', 'dia', 'turno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('trabajo_horarios');
        Schema::dropIfExists('trabajos');
    }
};
