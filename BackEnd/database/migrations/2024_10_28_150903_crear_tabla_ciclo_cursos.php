<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciclo_cursos', function (Blueprint $table) {
            $table->id('idRegistroCurso');
            $table->unsignedBigInteger('idCurso');
            $table->unsignedBigInteger('idCiclo');

            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
            $table->foreign('idCiclo')->references('idCiclo')->on('ciclo')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciclo_cursos');
    }
};