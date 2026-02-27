<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Licencia extends Model
{
    protected $table = 'licencia';
    protected $primaryKey = 'id_licencia';
    public $timestamps = false;

    protected $fillable = [
        'id_licencia',
        'id_empresa',
        'id_licenciatipo',
        'precio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_usuarioreg',
        'fechareg',
    ];

    public function tipo()
    {
        return $this->belongsTo(\App\Models\LicenciaTipo::class, 'id_licenciatipo', 'id_licenciatipo');
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'id_empresa', 'id_empresa');
    }
}