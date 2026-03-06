<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctor';
    protected $primaryKey = 'id_doctor';
    public $timestamps = false;

    protected $fillable = [
        'especialidad',
        'nombre',
        'apellido',
        'telefono',
        'email',
        'fecha_contratacion',
        'estado',
        'id_empresa'
    ];

    // 🔗 Relación: Doctor pertenece a Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }
}