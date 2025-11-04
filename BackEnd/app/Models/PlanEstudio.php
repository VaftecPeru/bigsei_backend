<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanEstudio extends Model
{
    protected $table = 'plan_estudio';
    protected $primaryKey = 'id_planestudio';
    public $timestamps = false;

    protected $fillable = [
        'id_planestudio',
        'id_empresa',
        'id_carrera',
        'fecha_inicio',
        'id_usuarioreg',
        'fechareg',
        'nombre',
        'estado',
        'esta_publicado'
    ];

}
