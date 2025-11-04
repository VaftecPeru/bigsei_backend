<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoCategoriaController extends Controller
{
    public function porTemas(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $result = DB::table("periodo_tema as a")
            ->join("periodo_modulo as b", "a.id_periodomodulo", "b.id_periodomodulo")
            ->join("periodo_curso as c", "b.id_periodocurso", DB::raw("c.id_periodocurso and c.estado = '1'"))
            ->rightJoin("tipo_categoria as d", "a.id_tipocategoria", "d.id_tipocategoria")
            ->select(
                "d.id_tipocategoria",
                "d.nombre",
                "d.orden",
                DB::raw("sum(case when a.id_periodomodulo is not null then 1 else 0 end) as cant_tipos")
            )
            ->where("d.estado", "1")
            ->where("d.tipo", "2");

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(d.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->groupBy("d.id_tipocategoria", "d.nombre", "d.orden");
        $result->orderBy("orden", "asc");
        $result->orderBy("id_tipocategoria", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }
}