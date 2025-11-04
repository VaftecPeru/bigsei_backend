<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoDocentes extends Model
{
    protected $table = 'curso_docentes'; 
    protected $primaryKey = 'idCursoDocente'; 
    public $timestamps = false;

    protected $fillable = [
        'idCurso',
        'idUsuario'
    ];

    // Relación con el modelo Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario','id_usuario');
    }

    // Relación con Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso','id_curso');
    }

    // Relacion con CursoHorario
    public function cursoHorario()
    {
        return $this->hasMany(CursoHorario::class, 'idCursoDocente', 'idCursoDocente');
    }

}
