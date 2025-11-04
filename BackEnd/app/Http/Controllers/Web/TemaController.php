<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemaController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $result = DB::table("periodo_tema as a")
            ->join("periodo_modulo as b", "a.id_periodomodulo", "b.id_periodomodulo")
            ->join("periodo_curso as c", "b.id_periodocurso", DB::raw("c.id_periodocurso and c.estado = '1'"))
            ->join("tipo_categoria as d", "a.id_tipocategoria", "d.id_tipocategoria")
            ->join("curso as e", "c.id_curso", "e.id_curso")
            ->join("empresa as f", "c.id_empresa", "f.id_empresa")
            ->select(
                "a.id_periodotema",
                "a.titulo",
                "a.descripcion",
                // DB::raw("e.url_img as curso_url_img"),
                DB::raw("e.id_archivo as curso_id_archivo"),
                DB::raw("f.razon_social as empresa_razon_social"),
                DB::raw("0 as rating"),
                DB::raw("0 as reviews")
            )
            ->where("c.estado", "1")
            ->where("d.estado", "1")
            ->where("d.tipo", "2")
            ->whereRaw("(c.es_sincrono = '0' || d.visible_web = '1')");

        if(isset($request->id_tipocategoria)) {
            $result->where("d.id_tipocategoria", $request->id_tipocategoria);
        }
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.titulo, a.descripcion)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->orderBy("id_periodotema", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }
}