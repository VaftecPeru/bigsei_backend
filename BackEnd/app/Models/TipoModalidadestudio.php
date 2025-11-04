<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoModalidadestudio extends Model
{
    protected $table = 'tipo_modalidadestudio';
    protected $primaryKey = 'id_tipomodalidadestudio';
    public $timestamps = false;

    protected $fillable = [
        'id_tipomodalidadestudio',
        'nombre'
    ];
}