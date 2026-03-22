<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidad';
    protected $primaryKey = 'id_especialidad';
    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    // Laravel maneja automáticamente created_at y updated_at
    public $timestamps = true;
}