<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';
    protected $primaryKey = 'id_cita';
    public $timestamps = true; // created_at y updated_at existen

    protected $fillable = [
        'id_paciente',
        'id_doctor',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'motivo',
        'estado'
    ];

    // Relación con Paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    // Relación con Doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'id_doctor', 'id_doctor');
    }

    // Diagnóstico de la cita
    public function diagnosticoMedico()
    {
        return $this->hasOne(DiagnosticoMedico::class, 'id_cita', 'id_cita');
    }

    // Receta de la cita
    public function recetaMedica()
    {
        return $this->hasOne(RecetaMedica::class, 'id_cita', 'id_cita');
    }

}