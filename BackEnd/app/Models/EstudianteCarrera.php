<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudianteCarrera extends Model
{
    protected $table = 'estudiante_carrera';
    protected $primaryKey = 'id_estudiantecarrera';
    public $timestamps = false;

    protected $fillable = [
        'id_estudiantecarrera',
        'id_empresa',
        'id_estudiante',
        'id_carrera',
        'id_planestudio',
        'fecha_inicio',
        'id_usuarioreg',
        'fechareg'
    ];

}