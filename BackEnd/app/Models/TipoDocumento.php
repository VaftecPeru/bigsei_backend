<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    protected $table = 'tipo_documento';
    protected $primaryKey = 'id_tipodocumento';
    public $timestamps = false;

    protected $fillable = [
        'id_tipodocumento',
        'nombre',
        'siglas'
    ];
}