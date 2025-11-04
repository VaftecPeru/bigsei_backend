<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoRespuesta extends Model
{
    protected $table = 'periodo_respuesta';
    protected $primaryKey = 'id_periodorespuesta';
    public $timestamps = false;

    protected $fillable = [
        'id_periodorespuesta',
        'id_periodopregunta',
        'descripcion',
        'orden',
        'es_valida',
        'id_usuarioreg',
        'fechareg'
    ];

}
