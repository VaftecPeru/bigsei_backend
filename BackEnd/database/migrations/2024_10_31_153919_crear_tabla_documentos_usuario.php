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
        Schema::create('documentos_usuario', function (Blueprint $table) {
            $table->id('idDocumento');
            $table->unsignedBigInteger('idUsuario');
            $table->string('nombreArchivo', 255);
            $table->string('rutaArchivo', 255);
            $table->string('tipoArchivo', 10);
            $table->date('fechaSubida');

            //Clave foranea con idUsuario
            $table->foreign('idUsuario')->references('idusuario')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_usuario');
    }
};
