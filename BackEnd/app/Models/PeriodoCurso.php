<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoCurso extends Model
{
    protected $table = 'periodo_curso';
    protected $primaryKey = 'id_periodocurso';
    public $timestamps = false;

    protected $fillable = [
        'id_periodocurso',
        'id_empresa',
        'id_periodo',
        'id_periodociclo',
        'id_usuarioreg',
        'fechareg',
        'estado',
        'id_curso',
        'id_docente',
        'vacantes',
        'id_tipomodalidadestudio',
        'curso_libre',
        'detalle',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'id_seccion',
        'id_tipocategoria',
        'es_sincrono',
        'url_zoom',
        'id_planestudiocurso',
        'creditos',
        'horas_semanal'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function periodo()
    {
        return $this->belongsTo(Empresa::class, 'id_periodo');
    }

    public function periodoCiclo()
    {
        return $this->belongsTo(Empresa::class, 'id_periodociclo');
    }

    public function usuarioRegistra()
    {
        return $this->belongsTo(Usuario::class, 'id_usuarioreg', 'id_usuario');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }
}
