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
        Schema::create('curso_evaluaciones', function (Blueprint $table) {
            $table->id('idCursoEvaluacion');
            $table->unsignedBigInteger('idCurso');
            $table->unsignedBigInteger('idEvaluacion');
            $table->integer('porcentaje');
            $table->date('fechaEvaluacion');
            
            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
            $table->foreign('idEvaluacion')->references('idEvaluacion')->on('evaluacion')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_evaluaciones');
    }
};
