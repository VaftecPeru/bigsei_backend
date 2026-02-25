<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea la tabla padre_hijo que vincula un padre (usuario) con sus hijos (usuarios estudiantes).
     */
    public function up(): void
    {
        Schema::create('padre_hijo', function (Blueprint $table) {
            $table->id('idPadreHijo');
            $table->unsignedBigInteger('idPadre')->comment('ID del usuario con rol padre');
            $table->unsignedBigInteger('idHijo')->comment('ID del usuario estudiante (hijo)');
            $table->timestamps();

            $table->foreign('idPadre')->references('idUsuario')->on('usuario')->onDelete('cascade');
            $table->foreign('idHijo')->references('idUsuario')->on('usuario')->onDelete('cascade');

            // Un padre no puede tener el mismo hijo más de una vez
            $table->unique(['idPadre', 'idHijo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('padre_hijo');
    }
};
