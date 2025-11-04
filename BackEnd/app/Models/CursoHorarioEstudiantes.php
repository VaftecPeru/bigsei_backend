<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoHorarioEstudiantes extends Model
{
    protected $table = 'curso_horario_estudiantes';
    protected $primaryKey = 'idCurHorEstudiante';
    public $timestamps = false;

    protected $fillable = [
        'idCursoHorario',
        'idUsuario'
    ];

    // Relacion con la tabla cursoHorario
    public function cursoHorario()
    {
        return $this->belongsTo(CursoHorario::class, 'idCursoHorario');
    }

    // Relacion con la tabla usuarios
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
}
