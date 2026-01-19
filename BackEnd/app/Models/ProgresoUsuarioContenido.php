<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgresoUsuarioContenido extends Model
{
    protected $table = 'progreso_usuario_contenido';
    protected $primaryKey = 'id_progreso';
    public $timestamps = false;

    protected $fillable = [
        'id_progreso',
        'id_usuario',
        'id_periodocurso',
        'tipo_contenido',
        'id_contenido',
        'completado',
        'fecha_completado',
        'fechareg'
    ];

    protected $casts = [
        'completado' => 'boolean',
        'fecha_completado' => 'datetime',
        'fechareg' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Relación con el periodo curso
     */
    public function periodoCurso()
    {
        return $this->belongsTo(PeriodoCurso::class, 'id_periodocurso', 'id_periodocurso');
    }
}
