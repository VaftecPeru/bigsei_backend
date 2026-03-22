<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    protected $primaryKey = 'id_especialidad';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion'];

    public function doctores()
    {
        return $this->hasMany(Doctor::class, 'id_especialidad');
    }
}