<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoTarea extends Model
{
    protected $table = 'periodo_tarea';
    protected $primaryKey = 'id_periodotarea';
    public $timestamps = false;

    protected $fillable = [
        'id_periodotarea',
        'id_empresa',
        'id_periodotema',
        'titulo',
        'instruccion',
        'fecha_entrega',
        'hora_entrega',
        'numero_intentos',
        'calificacion_maxima',
        'fecha_mostrar_desde',
        'fecha_mostrar_hasta',
        'id_usuarioreg',
        'fechareg'
    ];

}