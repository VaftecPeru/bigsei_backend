<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\Request;

class CarreraController extends Controller
{
    // Funcion para listar carreras
    public function listarCarreras()
    {
        $carreras = Carrera::all();
        return response()->json($carreras);
    }
}
