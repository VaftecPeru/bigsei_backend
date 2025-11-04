<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticoMedico extends Model
{
    protected $table = 'diagnostico_medico';
    protected $primaryKey = 'id_diagnostico';

    protected $fillable = [
        'id_cita',
        'descripcion',
        'observaciones',
        'fecha',
    ];

    public function cita()
    {
        return $this->belongsTo(CitaMedica::class, 'id_cita');
    }

    public function receta()
    {
        return $this->hasOne(RecetaMedica::class, 'id_diagnostico');
    }
}
