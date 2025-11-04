<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $result = DB::table("empresa as a")
            ->leftJoin("periodo_curso as b", "a.id_empresa", "b.id_empresa")
            ->select(
                "a.id_empresa",
                "a.razon_social",
                DB::raw("sum(case when b.id_empresa is not null then 1 else 0 end) as cant_cursos")
            )
            ->where("a.estado", "1");

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(d.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->groupBy("a.id_empresa", "a.razon_social");
        $result->orderBy("razon_social", "asc");
        $result->orderBy("id_empresa", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }
}