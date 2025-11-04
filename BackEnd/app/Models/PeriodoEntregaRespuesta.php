<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoEntregaRespuesta extends Model
{
    protected $table = 'periodo_entrega_respuesta';
    protected $primaryKey = 'id_periodoentregarespuesta';
    public $timestamps = false;

    protected $fillable = [
        'id_periodoentregarespuesta',
        'id_empresa',
        'id_periodopregunta',
        'id_periodorespuesta',
        'id_estudiante',
        'es_correcto',
        'id_usuarioreg',
        'fechareg'
    ];

}