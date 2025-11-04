<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoNiveleducativo extends Model
{
    protected $table = 'tipo_niveleducativo';
    protected $primaryKey = 'id_tiponiveleducativo';
    public $timestamps = false;

    protected $fillable = [
        'id_tiponiveleducativo',
        'nombre'
    ];

}