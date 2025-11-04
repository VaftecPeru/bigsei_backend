<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'paciente';
    protected $primaryKey = 'id_paciente';
    public $incrementing = false; 
    protected $keyType = 'int';

    protected $fillable = [
        'id_paciente',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_paciente', 'id_usuario');
    }

    public function citas()
    {
        return $this->hasMany(CitaMedica::class, 'id_paciente');
    }
}