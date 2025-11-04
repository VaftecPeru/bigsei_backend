<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre_especialidad',
        'fecha_registro'
    ];

    protected $casts = [
        'fecha_registro' => 'date'
    ];

    public function scopeOrdenarPorFecha($query, $orden = 'desc')
    {
        return $query->orderBy('fecha_registro', $orden);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_registro', '>=', now()->subDays($dias));
    }

    public function getNombreFormateadoAttribute()
    {
        return ucwords(strtolower($this->nombre_especialidad));
    }
}