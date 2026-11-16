<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// bitacora de seguridad: inicios de sesion, bloqueos, cambios de clave, etc.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_seguridad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('email', 100)->nullable();
            $table->string('tipo', 30);
            $table->string('detalle', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('navegador', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tipo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_seguridad');
    }
};
