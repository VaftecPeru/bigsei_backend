<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResenaCurso extends Model
{
    protected $table = 'resenas_curso';
    protected $primaryKey = 'idResena';

    protected $fillable = [
        'idUsuario',
        'idPeriodoCurso',
        'calificacion',
        'comentario',
    ];

    /**
     * Relación con el usuario estudiante
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    /**
     * Relación con el periodo-curso (curso matriculado)
     */
    public function periodoCurso()
    {
        return $this->belongsTo(PeriodoCurso::class, 'idPeriodoCurso', 'idPeriodoCurso');
    }
}
