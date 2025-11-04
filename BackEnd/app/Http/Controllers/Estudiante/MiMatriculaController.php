<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MiMatriculaController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table('matricula as a')
            ->select(
                "a.id_matricula",
                DB::raw("date_format(a.fechareg, '%d/%m/%Y') as fecha_ff")
            )
            ->where("a.id_estudiante", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->id_periodo)) {
            $paginate->where("a.id_periodo", $request->id_periodo);
        }

        $paginate->orderBy("fecha_ff", "desc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }
}