<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarreraEstudiantes extends Model
{
    protected $table = 'carrera_estudiantes';
    protected $primaryKey = 'idCarreraEstudiante';
    public $timestamps = false;

    protected $fillable = [
        'idCarrera',
        'idEstudiante',
    ];

    // Relación con Carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'idCarrera');
    }

    // Relación con Usuario (Estudiante)
    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'idEstudiante', 'idUsuario');
    }
}
