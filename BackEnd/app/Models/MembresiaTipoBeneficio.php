<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembresiaTipoBeneficio extends Model
{
    protected $table = 'membresia_tipo_beneficio';
    protected $primaryKey = ['id_membresiatipo', 'id_tipobeneficio'];
    public $timestamps = false;

    protected $fillable = [
        'id_membresiatipo',
        'id_tipobeneficio',
        'esta_habilitado'
    ];
}