<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoPreguntaController extends Controller
{
    public function index()
    {
        $tipoPreguntas = DB::table('tipo_pregunta')
            ->select(
                "id_tipopregunta",
                "nombre",
                "orden",
                "codigo"
            )
            ->orderBy('orden', 'asc')
            ->get();

        return response()->json($tipoPreguntas);
    }
}
