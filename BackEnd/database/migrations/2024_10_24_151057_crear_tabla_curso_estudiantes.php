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
        Schema::create('curso_estudiantes', function (Blueprint $table) {
            $table->id('idCursoEstudiante');
            $table->unsignedBigInteger('idCurso');
            $table->unsignedBigInteger('idUsuario');
            $table->integer('cantidadRepitencias')->default(0);

            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_estudiantes');
    }
};
