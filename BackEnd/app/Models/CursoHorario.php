<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoHorario extends Model
{
    protected $table = 'curso_horario';
    protected $primaryKey = 'idCursoHorario';
    public $timestamps = false;

    protected $fillable = [
        'idCursoDocente',
        'aula',
        'dia',
        'hora_ini',
        'hora_fin',
        'vacantes',
        'vacantes_disponibles'
    ];

    // Relación con la tabla CursoDocentes
    public function cursoDocentes()
    {
        return $this->belongsTo(CursoDocentes::class, 'idCursoDocente');
    }

    // Relacion con la tabla Matricula
    public function matricula()
    {
        return $this->hasMany(Matricula::class, 'idCursoHorario');
    }

    // Relación con la tabla CursoHorarioEstudiantes
    public function cursoHorarioEstudiantes()
    {
        return $this->hasMany(CursoHorarioEstudiantes::class, 'idCursoHorario');
    }

    // Relación con la tabla CursoAsistencia
    public function cursoAsistencia()
    {
        return $this->hasMany(CursoAsistencia::class, 'idCursoHorario');
    }
}
