<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// después de visitar, el estudiante confirma si el cuarto era como en las fotos
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('confirmacion', 12)->nullable()->after('estado');   // 'coincide' o 'no_coincide'
            $table->timestamp('confirmado_at')->nullable()->after('confirmacion');
            $table->index(['habitacion_id', 'confirmacion']);
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropIndex(['habitacion_id', 'confirmacion']);
            $table->dropColumn(['confirmacion', 'confirmado_at']);
        });
    }
};
