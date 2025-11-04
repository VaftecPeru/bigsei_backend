<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaMedica extends Model
{
    protected $table = 'receta_medica';
    protected $primaryKey = 'id_receta';

    protected $fillable = [
        'id_diagnostico',
        'medicamento',
        'indicaciones',
    ];

    public function diagnostico()
    {
        return $this->belongsTo(DiagnosticoMedico::class, 'id_diagnostico');
    }
}