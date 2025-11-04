<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursoController extends Controller
{
    public function destacados(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 4;
        }

        $result = DB::table("periodo_curso as a")
            ->join("periodo as b", "a.id_periodo", "b.id_periodo")
            ->join("empresa as d", "a.id_empresa", "d.id_empresa")
            ->join("curso as e", "a.id_curso", "e.id_curso")
            ->join("tipo_categoria as f", "a.id_tipocategoria", "f.id_tipocategoria")
            ->leftJoin("resena as i", "a.id_periodocurso", "i.id_periodocurso")
            ->select(
                "a.id_periodocurso",
                "a.detalle",
                "e.url_img",
                "e.id_archivo",
                DB::raw("d.razon_social as empresa_razon_social"),
                DB::raw("e.nombre as curso_nombre"),
                DB::raw("round(
                    sum(case when i.id_resena is not null then i.rating else 0 end)
                    /
                    if(
                        sum(case when i.id_resena is not null then 1 else 0 end) = 0,
                        1,
                        sum(case when i.id_resena is not null then 1 else 0 end)
                    )
                , 1) as rating"),
                DB::raw("sum(case when i.id_resena is not null then 1 else 0 end) as reviews")
            )
            ->where("a.estado", "1")
            ->where("b.esta_abierto", "1")
            ->whereRaw("(a.es_sincrono = '0' || f.visible_web = '1')");

        $result->groupBy("a.id_periodocurso", "a.detalle", "e.url_img", "e.id_archivo", "d.razon_social", "e.nombre");
        $result->orderBy("rating", "desc");
        $result->orderBy("empresa_razon_social", "asc");
        $result->orderBy("curso_nombre", "asc");
        $result->orderBy("id_periodocurso", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }

    public function visiblesWeb(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 2;
        }
        $result = DB::table("periodo_curso as a")
            ->join("periodo as b", "a.id_periodo", "b.id_periodo")
            ->join("empresa as d", "a.id_empresa", "d.id_empresa")
            ->join("curso as e", "a.id_curso", "e.id_curso")
            ->join("tipo_categoria as f", "a.id_tipocategoria", "f.id_tipocategoria")
            ->leftJoin("resena as i", "a.id_periodocurso", "i.id_periodocurso")
            ->select(
                "a.id_periodocurso",
                "a.detalle",
                DB::raw("d.razon_social as empresa_razon_social"),
                DB::raw("d.id_archivo as empresa_id_archivo"),
                DB::raw("e.nombre as curso_nombre"),
                DB::raw("e.id_archivo as curso_id_archivo"),
                DB::raw("round(
                    sum(case when i.id_resena is not null then i.rating else 0 end)
                    /
                    if(
                        sum(case when i.id_resena is not null then 1 else 0 end) = 0,
                        1,
                        sum(case when i.id_resena is not null then 1 else 0 end)
                    )
                , 1) as rating"),
                DB::raw("sum(case when i.id_resena is not null then 1 else 0 end) as reviews")
            )
            ->where("a.estado", "1")
            ->where("b.estado", "1")
            ->where("f.visible_web", "1");

        if(isset($request->id_tipocategoria)) {
            $result->where("a.id_tipocategoria", $request->id_tipocategoria);
        }
        if(isset($request->id_empresa)) {
            $result->where("a.id_empresa", $request->id_empresa);
        }

        $result->groupBy("a.id_periodocurso", "a.detalle", "e.id_archivo", "d.razon_social", "d.id_archivo", "e.nombre");
        $result->orderBy("rating", "desc");
        $result->orderBy("empresa_razon_social", "asc");
        $result->orderBy("curso_nombre", "asc");
        $result->orderBy("id_periodocurso", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }
}