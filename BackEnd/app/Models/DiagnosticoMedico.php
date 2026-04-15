<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiagnosticoMedico extends Model
{
    use HasFactory;

    protected $table = 'diagnosticos_medicos';
    protected $primaryKey = 'id_diagnostico';
    public $timestamps = true;

    protected $fillable = [
        'id_cita',
        'descripcion',
        'observaciones',
        'fecha'
    ];

    // Relación con la cita
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'id_cita', 'id_cita');
    }

    // Relación con recetas
    public function recetas()
    {
        return $this->hasMany(RecetaMedica::class, 'id_diagnostico', 'id_diagnostico');
    }
}