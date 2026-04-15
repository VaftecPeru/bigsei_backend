<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecetaMedica extends Model
{
    use HasFactory;

    protected $table = 'recetas_medicas';
    protected $primaryKey = 'id_receta';
    public $timestamps = true;

    protected $fillable = [
        'id_cita',
        'indicaciones'
    ];

    // Relación con la cita
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'id_cita', 'id_cita');
    }
}