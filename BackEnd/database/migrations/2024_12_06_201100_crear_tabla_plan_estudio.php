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
        Schema::create('plan_estudio', function (Blueprint $table) {
            $table->id('idPlanEstudio');
            $table->unsignedBigInteger('idCarrera');
            $table->integer('anio');

            $table->foreign('idCarrera')->references('idCarrera')->on('carrera')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_estudio');
    }
};
