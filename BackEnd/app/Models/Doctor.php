<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctor';
    protected $primaryKey = 'id_doctor';
    public $incrementing = false; // viene de persona
    protected $keyType = 'int';

    protected $fillable = [
        'id_doctor',
        'especialidad',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_doctor', 'id_persona');
    }

    public function citas()
    {
        return $this->hasMany(CitaMedica::class, 'id_doctor');
    }
}