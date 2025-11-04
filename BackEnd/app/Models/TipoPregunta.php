<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPregunta extends Model
{
    protected $table = 'tipo_pregunta';
    protected $primaryKey = 'id_tipopregunta';
    public $timestamps = false;

    protected $fillable = [
        'id_tipopregunta',
        'nombre',
        'orden',
        'codigo'
    ];

}
