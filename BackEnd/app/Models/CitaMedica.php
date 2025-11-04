<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaMedica extends Model
{
    protected $table = 'cita_medica';
    protected $primaryKey = 'id_cita';

    protected $fillable = [
        'id_paciente',
        'id_doctor',
        'fecha',
        'hora',
        'estado',
        'motivo',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'id_paciente');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'id_doctor');
    }

    public function diagnostico()
    {
        return $this->hasOne(DiagnosticoMedico::class, 'id_cita');
    }
}