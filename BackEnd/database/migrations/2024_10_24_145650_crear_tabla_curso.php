<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso', function (Blueprint $table) {
            $table->id('idCurso');
            $table->string('codigoCurso');
            $table->unsignedBigInteger('idSeccion');
            $table->unsignedBigInteger('idModalidad');
            $table->string('nombreCurso', 50);
            $table->integer('creditos');
            $table->date('fecha_ini');
            $table->date('fecha_fin');
            
            $table->foreign('idSeccion')->references('idSeccion')->on('seccion')->onDelete('cascade');
            $table->foreign('idModalidad')->references('idModalidad')->on('modalidad')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso');
    }
};