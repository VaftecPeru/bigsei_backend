<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mes extends Model
{
    protected $table = 'mes';
    protected $primaryKey = 'id_mes';
    public $timestamps = false;

    protected $fillable = [
        'id_mes',
        'nombre',
        'estado'
    ];
}
