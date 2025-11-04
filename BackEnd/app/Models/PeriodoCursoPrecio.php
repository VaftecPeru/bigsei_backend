<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoCursoPrecio extends Model
{
    protected $table = 'periodo_curso_precio';
    protected $primaryKey = 'id_periodocursoprecio';
    public $timestamps = false;

    protected $fillable = [
        'id_periodocursoprecio',
        'id_empresa',
        'id_periodocurso',
        'importe',
        'estado',
        'id_usuarioreg',
        'fechareg',
        'tipo'
    ];
}