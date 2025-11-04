<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoBeneficio extends Model
{
    protected $table = 'tipo_beneficio';
    protected $primaryKey = 'id_tipobeneficio';
    public $timestamps = false;

    protected $fillable = [
        'id_tipobeneficio',
        'descripcion'
    ];
}