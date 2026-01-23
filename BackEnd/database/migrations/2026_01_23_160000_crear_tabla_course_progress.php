<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Hito 12: Tabla course_progress para seguimiento del progreso general del curso
     */
    public function up(): void
    {
        Schema::create('course_progress', function (Blueprint $table) {
            $table->id('id_course_progress');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_periodocurso');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('fechareg')->useCurrent();

            // Indexes para mejorar rendimiento
            $table->index('id_usuario', 'idx_course_progress_usuario');
            $table->index('id_periodocurso', 'idx_course_progress_curso');
            $table->index('status', 'idx_course_progress_status');
            
            // Un usuario solo puede tener un registro de progreso por curso
            $table->unique(['id_usuario', 'id_periodocurso'], 'unique_user_course_progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_progress');
    }
};
