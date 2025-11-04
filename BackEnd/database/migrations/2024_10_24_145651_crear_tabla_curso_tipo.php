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
        Schema::create('curso_tipo', function (Blueprint $table) {
            $table->id('idCursoTipo');
            $table->unsignedBigInteger('idCurso');
            $table->unsignedBigInteger('idTipoCurso');

            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
            $table->foreign('idTipoCurso')->references('idTipoCurso')->on('tipo_curso')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_tipo');
    }
};
