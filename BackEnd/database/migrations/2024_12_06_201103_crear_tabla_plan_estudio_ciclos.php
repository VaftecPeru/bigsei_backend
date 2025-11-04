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
        Schema::create('plan_estudio_ciclos', function (Blueprint $table) {
            $table->id('idPlanEstudioCiclo');
            $table->unsignedBigInteger('idPlanEstudio');
            $table->unsignedBigInteger('idCiclo');

            $table->foreign('idPlanEstudio')->references('idPlanEstudio')->on('plan_estudio')->onDelete('cascade');
            $table->foreign('idCiclo')->references('idCiclo')->on('ciclo')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_estudio_ciclos');
    }
};
