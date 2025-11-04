<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MisCursosController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->sessionUser;
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 2;
        }
        $result = DB::table('periodo_curso as a')
            ->join('periodo as b', 'a.id_periodo', 'b.id_periodo')
            ->join('empresa as d', 'a.id_empresa', 'd.id_empresa')
            ->join('curso as e', 'a.id_curso', 'e.id_curso')
            ->join('tipo_categoria as f', 'a.id_tipocategoria', "f.id_tipocategoria")
            ->join('matricula_curso as g', 'a.id_periodocurso', "g.id_periodocurso")
            ->join('matricula as h', 'g.id_matricula', "h.id_matricula")
            ->select(
                'a.id_periodocurso',
                'a.detalle',
                'e.url_img',
                DB::raw("d.razon_social as empresa_razon_social"),
                DB::raw("e.nombre as curso_nombre")
            )
            ->where("h.id_estudiante", $user->id_usuario)
            ->where("a.estado", "1")
            ->where("b.estado", "1")
            ->whereRaw("(a.es_sincrono = '0' || f.visible_web = '1')");

        $result->groupBy("a.id_periodocurso", "a.detalle", "e.url_img", "d.razon_social", "e.nombre");
        $result->orderBy("empresa_razon_social", "asc");
        $result->orderBy("curso_nombre", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }
}