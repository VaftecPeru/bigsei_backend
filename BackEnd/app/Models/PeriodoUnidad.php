<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoUnidad extends Model
{
    protected $table = 'periodo_unidad';
    protected $primaryKey = 'id_periodounidad';
    public $timestamps = false;

    protected $fillable = [
        'id_periodounidad',
        'id_periodotema',
        'titulo',
        'descripcion',
        'fecha',
        'id_usuarioreg',
        'fechareg'
    ];
}