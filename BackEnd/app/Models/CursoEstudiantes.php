<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoEstudiantes extends Model
{
    protected $table = 'curso_estudiantes';
    protected $primaryKey = 'idCursoEstudiante';
    public $timestamps = false;

    protected $fillable = [
        'idCurso',
        'idUsuario',
        'cantidadRepitencias'
    ];

    // Relacion con la tabla curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso','id_curso');
    }

    // Relacion con la tabla usuarios
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario','id_usuario');
    }

    // Relacion con la tabla cursoAsistencia
    public function cursoAsistencia()
    {
        return $this->hasMany(CursoAsistencia::class, 'idCursoEstudiante', 'idCursoEstudiante');
    }
}
