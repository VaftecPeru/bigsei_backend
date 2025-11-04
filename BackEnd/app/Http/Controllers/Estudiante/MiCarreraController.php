<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MiCarreraController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("estudiante_carrera as a")
            ->join("carrera as b", "a.id_carrera", "b.id_carrera")
            ->select(
                "b.id_carrera",
                "b.nombre"
            )
            ->where("a.id_estudiante", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa)
            ->orderBy("nombre", "asc")
            ->paginate($per_page);

        return response()->json($paginate);
    }
}