<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';
    protected $primaryKey = 'id_lesson_progress';
    public $timestamps = false;

    protected $fillable = [
        'id_lesson_progress',
        'id_usuario',
        'id_periodocurso',
        'id_lesson',
        'lesson_type',
        'status',
        'completed_at',
        'fechareg'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'fechareg' => 'datetime',
    ];

    /**
     * Tipos de lección válidos
     */
    const TYPE_VIDEO = 'video';
    const TYPE_TAREA = 'tarea';
    const TYPE_CUESTIONARIO = 'cuestionario';

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Relación con el periodo curso
     */
    public function periodoCurso()
    {
        return $this->belongsTo(PeriodoCurso::class, 'id_periodocurso', 'id_periodocurso');
    }

    /**
     * Verifica si una lección ya fue registrada para un usuario
     * Hito 6: Una lección solo se registra una vez
     */
    public static function exists(int $id_usuario, int $id_periodocurso, int $id_lesson, string $lesson_type): bool
    {
        return self::where('id_usuario', $id_usuario)
            ->where('id_periodocurso', $id_periodocurso)
            ->where('id_lesson', $id_lesson)
            ->where('lesson_type', $lesson_type)
            ->exists();
    }

    /**
     * Registra una lección como completada (solo si no existe)
     * Retorna null si ya existía
     */
    public static function registerIfNotExists(int $id_usuario, int $id_periodocurso, int $id_lesson, string $lesson_type): ?self
    {
        if (self::exists($id_usuario, $id_periodocurso, $id_lesson, $lesson_type)) {
            return null;
        }

        return self::create([
            'id_usuario' => $id_usuario,
            'id_periodocurso' => $id_periodocurso,
            'id_lesson' => $id_lesson,
            'lesson_type' => $lesson_type,
            'status' => 'completed',
            'completed_at' => now(),
            'fechareg' => now(),
        ]);
    }

    /**
     * Cuenta las lecciones completadas de un curso
     */
    public static function countCompleted(int $id_usuario, int $id_periodocurso): int
    {
        return self::where('id_usuario', $id_usuario)
            ->where('id_periodocurso', $id_periodocurso)
            ->count();
    }
}
