<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sedes extends Model
{
    protected $table = 'empresa';
    protected $primaryKey = 'id_empresa';
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'id_tipodocumento',
        'numero_documento',
        'razon_social',
        'condicion_sunat',
        'estado_contribuyente',
        'tipo_relacion',
        'id_vendedor',
        'correo',
        'contacto',
        'direccion_fiscal',
        'telefono',
        'departamento',
        'provincia',
        'distrito',
        'direccion',
        'atencion_desde',
        'atencion_hasta',
        'atencion_dias',
        'url_maps',
        'url_img',
        'id_archivo'
    ];

    public function getNombreSedeAttribute()
    {
        return $this->razon_social;
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_empresa', 'id_empresa');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_empresa', 'id_empresa');
    }
}
