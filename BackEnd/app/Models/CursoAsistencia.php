<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoAsistencia extends Model
{
    protected $table = 'curso_asistencia';
    protected $primaryKey = 'idAsistencia';
    public $timestamps = false;

    protected $fillable = [
        'idCursoEstudiante',
        'idCursoHorario',
        'estado',
        'justificacion',
        'fechaRegistro'
    ];

    // Relación con la tabla cursoEstudiantes
    public function cursoEstudiantes()
    {
        return $this->belongsTo(CursoEstudiantes::class, 'idCursoEstudiante', 'idCursoEstudiante');
    }

    // Relación con la tabla cursoHorario
    public function cursoHorario()
    {
        return $this->belongsTo(CursoHorario::class, 'idCursoHorario', 'idCursoHorario');
    }
}
