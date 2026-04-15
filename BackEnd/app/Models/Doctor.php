<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Empresa;
use App\Models\Especialidad;

class Doctor extends Model
{
    protected $table = 'doctor';
    protected $primaryKey = 'id_doctor';
    public $timestamps = false;

    protected $fillable = [
        'dni',
        'nombre',
        'apellido',
        'telefono',
        'email',
        'fecha_contratacion',
        'estado',
        'id_empresa',
        'id_especialidad'
    ];

    // valor por defecto si no se envía estado
    protected $attributes = [
        'estado' => 'Activo',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'id_especialidad', 'id_especialidad');
    }
}