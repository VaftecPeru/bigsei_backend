<?php

// app/Models/HorarioCurso.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioCurso extends Model
{
    protected $table = 'horarios_curso';
    protected $primaryKey = 'idhorario';

    public $timestamps = false;

    protected $fillable = [
        'idCursoTipo',
        'idCursoDocente',
        'aula',
        'dia',
        'fecha_ini',
        'fecha_fin'
    ];

    // Relación con la tabla cursoTipo
    public function cursoTipo()
    {
        return $this->belongsTo(CursoTipo::class, 'idCursoTipo', 'idCursoTipo');
    }

    // Relación con la tabla cursoDocentes
    public function cursoDocentes()
    {
        return $this->hasMany(CursoDocentes::class, 'idCurso', 'idCurso');
    }

    // Relación con la tabla cursoEstudiantes
    public function cursoEstudiantes()
    {
        return $this->hasMany(CursoEstudiantes::class, 'idCurso', 'idCurso');
    }
}
