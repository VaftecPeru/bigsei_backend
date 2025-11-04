<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatriculaCurso extends Model
{
    protected $table = 'matricula_curso';
    protected $primaryKey = 'id_matriculacurso';
    public $timestamps = false;

    protected $fillable = [
        'id_matriculacurso',
        'id_empresa',
        'id_matricula',
        'id_cursohorario',
        'id_periodocurso',
        'id_curso',
        'id_usuarioreg',
        'fechareg'
    ];
    
    // Relación con el modelo Matricula
    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'idMatricula');
    }

    // Relación con el modelo CursoHorario
    public function cursoHorario()
    {
        return $this->belongsTo(CursoHorario::class, 'idCursoHorario');
    }
}
