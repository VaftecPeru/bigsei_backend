<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoCiclo extends Model
{
    protected $table = 'periodo_ciclo';
    protected $primaryKey = 'id_periodociclo';
    public $timestamps = false;

    protected $fillable = [
        'id_periodociclo',
        'id_empresa',
        'id_periodo',
        'descripcion',
        'id_usuarioreg',
        'fechareg',
        'estado',
        'codigo',
        'id_carrera',
        'id_tituloacademico',
        'id_tipotituloacademico',
        'id_ciclo',
        'id_planestudiociclo'
    ];

}