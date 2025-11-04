<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asistencias_docentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idCurso');       // Relación con cursos
            $table->unsignedBigInteger('idUsuario');     // Relación con docentes
            $table->date('fecha');                        // Fecha de la asistencia
            $table->string('estado');                     // Estado('asistió', 'falta', 'justificado')
            $table->timestamps();

            // Relaciones
            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asistencias_docentes');
    }
};