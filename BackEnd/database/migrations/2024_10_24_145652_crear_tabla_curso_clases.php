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
        Schema::create('curso_clases', function (Blueprint $table) {
            $table->id('idCursoClase');
            $table->unsignedBigInteger('idCurso');
            $table->integer('totalClases');

            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_clases');
    }
};
