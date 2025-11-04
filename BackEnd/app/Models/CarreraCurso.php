<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarreraCurso extends Model
{
    protected $table = 'carrera_curso';
    protected $primaryKey = 'idCarreraCurso';
    public $timestamps = false;

    protected $fillable = [
        'idCarrera',
        'idCurso',
        'tipoCurso'
    ];

    // Relación con la tabla carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'idCarrera');
    }

    // Relación con la tabla curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso');
    }
}
