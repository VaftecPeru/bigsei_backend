<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembresiaTipo extends Model
{
    protected $table = 'membresia_tipo';
    protected $primaryKey = 'id_membresiatipo';
    public $timestamps = false;

    protected $fillable = [
        'id_membresiatipo',
        'nombre',
        'descripcion',
        'precio',
        'precio_mes',
        'estado',
        'id_usuarioreg',
        'fechareg',
        'es_anual'
    ];
}