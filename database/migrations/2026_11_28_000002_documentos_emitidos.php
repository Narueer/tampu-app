<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// cada constancia que TAMPU emite queda registrada con un código único y secreto (el del QR)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('tipo', 20);                       // visita, postulacion
            $table->unsignedBigInteger('referencia_id');       // id de la solicitud o postulación
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete(); // el estudiante
            $table->json('datos');                             // copia de lo que dice el PDF el día que se emitió
            $table->unsignedInteger('descargas')->default(0);
            $table->unsignedInteger('verificaciones')->default(0);
            $table->timestamp('revocado_at')->nullable();
            $table->string('motivo_revocacion', 200)->nullable();
            $table->timestamps();
            $table->unique(['tipo', 'referencia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
