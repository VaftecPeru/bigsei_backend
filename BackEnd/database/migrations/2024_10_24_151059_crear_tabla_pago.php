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
        Schema::create('pago', function (Blueprint $table) {
            $table->id('idPago');
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedBigInteger('idMetodoPago');
            $table->unsignedBigInteger('idNivel'); // Nuevo campo para nivel
            $table->unsignedBigInteger('idGrado'); // Nuevo campo para grado
            $table->string('descripcion', 100);
            $table->decimal('importe', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamp('fechaPago')->useCurrent();

            // Relaciones
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('idMetodoPago')->references('idMetodoPago')->on('metodo_pago')->onDelete('cascade');
            $table->foreign('idNivel')->references('idNivel')->on('nivel')->onDelete('cascade');
            $table->foreign('idGrado')->references('idGrado')->on('grado')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};

