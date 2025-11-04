<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoEntregaTarea extends Model
{
    protected $table = 'periodo_entrega_tarea';
    protected $primaryKey = 'id_periodoentregatarea';
    public $timestamps = false;

    protected $fillable = [
        'id_periodoentregatarea',
        'id_empresa',
        'id_estudiante',
        'id_periodotarea',
        'id_archivo',
        'comentario',
        'id_usuarioreg',
        'fechareg'
    ];

}