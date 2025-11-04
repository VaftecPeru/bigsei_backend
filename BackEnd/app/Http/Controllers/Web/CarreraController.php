<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarreraController extends Controller
{
    public function activas(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $result = DB::table("carrera as a")
            ->join("periodo_ciclo as b", "a.id_carrera", "b.id_carrera")
            ->join("periodo as c", "b.id_periodo", "c.id_periodo")
            ->join("empresa as d", "c.id_empresa", "d.id_empresa")
            ->join("titulo_academico as e", "b.id_tituloacademico", "e.id_tituloacademico")
            ->join("tipo_titulo_academico as f", "b.id_tipotituloacademico", "f.id_tipotituloacademico")
            ->select(
                "a.id_carrera",
                "a.nombre",
                "b.id_periodociclo",
                DB::raw("d.razon_social as empresa_razon_social"),
                DB::raw("d.url_img as empresa_url_img"),
                DB::raw("e.nombre as tituloacademico_nombre"),
                DB::raw("f.nombre as tituloacademico_tipo")
            )
            ->where("b.estado", "1")
            ->where("c.estado", "1");
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        if(isset($request->id_tipotituloacademico)) {
            $result->where("f.id_tipotituloacademico", $request->id_tipotituloacademico);
        }

        $result->orderBy("empresa_razon_social", "asc");
        $result->orderBy("nombre", "asc");
        $result->orderBy("id_periodociclo", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }

    public function tipoTituloAcademicos(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $result = DB::table("tipo_titulo_academico as a")
            ->leftJoin("periodo_ciclo as b", "a.id_tipotituloacademico", DB::raw("b.id_tipotituloacademico and b.estado = 1"))
            ->select(
                "a.id_tipotituloacademico",
                "a.nombre",
                "a.orden",
                DB::raw("sum(case when b.id_tipotituloacademico is not null then 1 else 0 end) as cant_tipos")
            )
            ->where("a.estado", "1");

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->groupBy("a.id_tipotituloacademico", "a.nombre", "a.orden");
        // $result->orderBy("nombre", "asc");
        $result->orderBy("orden", "asc");
        $result->orderBy("id_tipotituloacademico", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }
}