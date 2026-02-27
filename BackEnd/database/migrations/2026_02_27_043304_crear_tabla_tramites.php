<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tramites', function (Blueprint $table) {
        $table->id('idTramite');
        $table->unsignedBigInteger('idUsuario');
        $table->string('tipo_tramite');
        $table->string('estado')->default('Pendiente');
        $table->timestamp('fecha_solicitud')->useCurrent();

        // Relación con usuarios
        $table->foreign('idUsuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tramites');
    }
};
