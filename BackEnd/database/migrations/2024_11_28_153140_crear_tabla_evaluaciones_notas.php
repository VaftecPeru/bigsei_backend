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
        Schema::create('evaluaciones_notas', function (Blueprint $table) {
            $table->id('idEvaluacionNota');
            $table->unsignedBigInteger('idCursoEvaluacion');
            $table->unsignedBigInteger('idUsuario');
            $table->integer('nota');

            $table->foreign('idCursoEvaluacion')->references('idCursoEvaluacion')->on('curso_evaluaciones')->onDelete('cascade');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_notas');
    }
};
