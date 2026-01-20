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
        Schema::table('persona', function (Blueprint $table) {
            // Nivel educativo (Información Académica)
            $table->unsignedInteger('id_tiponiveleducativo')->nullable()->after('id_tipodocumento');
            
            // Programa de estudios (texto libre)
            $table->string('programa_estudios', 255)->nullable()->after('id_tiponiveleducativo');
            
            // Nivel formativo (Programa de Estudios)
            $table->unsignedInteger('id_tiponiveleducativo_formativo')->nullable()->after('programa_estudios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $table->dropColumn(['id_tiponiveleducativo', 'programa_estudios', 'id_tiponiveleducativo_formativo']);
        });
    }
};
