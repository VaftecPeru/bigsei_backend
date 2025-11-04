<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEspecializacion extends Model
{
    protected $table = 'tipo_especializacion';
    protected $primaryKey = 'id_tipoespecializacion';
    public $timestamps = false;

    protected $fillable = [
        'id_tipoespecializacion',
        'nombre'
    ];

}