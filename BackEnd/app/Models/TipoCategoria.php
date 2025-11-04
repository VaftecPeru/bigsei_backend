<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCategoria extends Model
{
    protected $table = 'tipo_categoria';
    protected $primaryKey = 'id_tipocategoria';
    public $timestamps = false;

    protected $fillable = [
        'id_tipocategoria',
        'nombre',
        'estado',
        'orden',
        'visible_web'
    ];
}