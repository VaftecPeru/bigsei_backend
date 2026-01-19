<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $table = 'certificados';
    protected $primaryKey = 'id_certificado';
    public $timestamps = false;

    protected $fillable = [
        'id_certificado',
        'id_usuario',
        'id_periodocurso',
        'codigo_certificado',
        'ruta_archivo',
        'nombre_archivo',
        'fecha_emision',
        'fechareg',
        'estado'
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'fechareg' => 'datetime',
        'estado' => 'boolean',
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

    /**
     * Genera un código único para el certificado
     */
    public static function generarCodigoUnico(): string
    {
        do {
            $codigo = 'CERT-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . date('Y');
        } while (self::where('codigo_certificado', $codigo)->exists());

        return $codigo;
    }
}
