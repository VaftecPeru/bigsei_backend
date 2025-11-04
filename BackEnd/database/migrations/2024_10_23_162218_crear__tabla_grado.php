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
        Schema::create('grado', function (Blueprint $table) {
            $table->id('idGrado'); // idGrado
            $table->string('nombre', 50); // Ejemplo: 1er Grado, 2do Grado
            $table->unsignedBigInteger('idNivel'); // Relación con nivel
            $table->timestamps();

            // Clave foránea
            $table->foreign('idNivel')->references('idNivel')->on('nivel')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grado');
    }
};

