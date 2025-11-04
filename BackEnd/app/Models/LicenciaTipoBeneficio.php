<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenciaTipoBeneficio extends Model
{
    protected $table = 'licencia_tipo_beneficio';
    protected $primaryKey = 'id_licenciatipo';
    public $timestamps = false;

    protected $fillable = [
        'id_licenciatipo',
        'id_tipobeneficio',
        'esta_habilitado',
        'orden',
    ];

}
