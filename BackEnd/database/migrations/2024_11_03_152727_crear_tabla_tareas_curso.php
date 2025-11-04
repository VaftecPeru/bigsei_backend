<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaTareasCurso extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tareas_curso', function (Blueprint $table) {
            $table->id('idTareaCurso');
            $table->unsignedBigInteger('idCurso');
            $table->string('descripcion');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            // Definición de claves foráneas
            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tareas_curso');
    }
}
