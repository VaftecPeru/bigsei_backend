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
        Schema::create('matricula_pagos', function (Blueprint $table) {
            $table->id('idMatriculaPago');
            $table->unsignedBigInteger('idMatricula');
            $table->unsignedBigInteger('idPago');

            $table->foreign('idMatricula')->references('idMatricula')->on('matricula')->onDelete('cascade');
            $table->foreign('idPago')->references('idPago')->on('pago')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matricula_pagos');
    }
};
