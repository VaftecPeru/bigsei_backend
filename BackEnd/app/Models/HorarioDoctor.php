<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioDoctor extends Model
{
    use HasFactory;

    protected $table = 'horarios_doctores';

    protected $fillable = [
        'dias_atencion',
        'doctor_id',
        'fecha_registro'
    ];

    protected $casts = [
        'dias_atencion' => 'array', 
        'fecha_registro' => 'datetime'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function scopePorDoctor($query, $doctor_id)
    {
        return $query->where('doctor_id', $doctor_id);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_registro', '>=', now()->subDays($dias));
    }

    public function getDiasAtencionFormateadosAttribute()
    {
        return implode(', ', $this->dias_atencion);
    }
}