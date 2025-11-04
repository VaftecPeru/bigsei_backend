<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tramite extends Model
{
    use HasFactory;

    protected $table = 'tramites';

    protected $fillable = [
        'nombre',
        'matricula',
        'tipo_tramite',
        'fecha_solicitud',
        'estado',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime:Y-m-d', 
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'matricula', 'matricula');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_tramite', $tipo);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_solicitud', '>=', now()->subDays($dias));
    }
}