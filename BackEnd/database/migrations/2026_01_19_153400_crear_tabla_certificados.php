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
        Schema::create('certificados', function (Blueprint $table) {
            $table->id('id_certificado');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_periodocurso');
            $table->string('codigo_certificado', 50)->unique();
            $table->string('ruta_archivo', 255);
            $table->string('nombre_archivo', 255);
            $table->timestamp('fecha_emision');
            $table->timestamp('fechareg')->useCurrent();
            $table->boolean('estado')->default(true);

            // Indexes for performance
            $table->index('id_usuario', 'idx_certificado_usuario');
            $table->index('id_periodocurso', 'idx_certificado_curso');
            $table->unique(['id_usuario', 'id_periodocurso'], 'unique_user_course_certificate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
