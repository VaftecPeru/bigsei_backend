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
        Schema::create('curso_docentes', function (Blueprint $table) {
            $table->id('idCursoDocente'); 
            $table->unsignedBigInteger('idCurso');
            $table->unsignedBigInteger('idUsuario');

             // Relaciones
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_docentes');
    }
};