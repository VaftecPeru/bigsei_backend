<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanEstudioCurso extends Model
{
    protected $table = 'plan_estudio_curso';
    protected $primaryKey = 'id_planestudiocurso';
    public $timestamps = false;

    protected $fillable = [
        'id_planestudiocurso',
        'id_empresa',
        'id_planestudio',
        'id_planestudiociclo',
        'id_curso',
        'creditos',
        'horas_semanal',
        'tipo',
        'id_usuarioreg',
        'fechareg'
    ];

}
