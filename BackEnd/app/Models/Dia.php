<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dia extends Model
{
    protected $table = 'dia';
    protected $primaryKey = 'id_dia';
    public $timestamps = false;

    protected $fillable = [
        'id_dia',
        'nombre',
        'estado',
        'orden'
    ];
}
