<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaEstudiante extends Model
{
    use HasFactory;

    protected $table = 'asistencias_estudiantes';

    protected $fillable = [
        'id_curso',
        'id_estudiante',
        'fecha',
        'estado',
    ];

    public function curso()
        {
    return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function estudiante()
    {
    return $this->belongsTo(Usuario::class, 'id_estudiante');
    }

    public function horario()
    {
    return $this->belongsTo(CursoHorario::class, 'id_horario');
    }
}