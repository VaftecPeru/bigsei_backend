<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanEstudioCiclo extends Model
{
    protected $table = 'plan_estudio_ciclo';
    protected $primaryKey = 'id_planestudiociclo';
    public $timestamps = false;

    protected $fillable = [
        'id_planestudiociclo',
        'id_empresa',
        'id_planestudio',
        'id_ciclo',
        'id_usuarioreg',
        'fechareg'
    ];

}
