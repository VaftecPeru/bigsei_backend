<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Paciente extends Model
{
    protected $table = 'paciente';
    protected $primaryKey = 'id_paciente';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'email',
        'direccion',
        'tipo_sangre',
    ];

    public function citas()
    {
        return $this->hasMany(CitaMedica::class, 'id_paciente');
    }
}