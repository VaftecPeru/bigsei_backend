<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoNota extends Model
{
    protected $table = 'tipo_nota';
    protected $primaryKey = 'id_tiponota';
    public $timestamps = false;

    protected $fillable = [
        'id_tiponota',
        'nombre'
    ];

}