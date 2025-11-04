<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuloController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("modulo as a")
            ->join("rol_modulo as b", "a.id_modulo", "b.id_modulo")
            ->select(
                "a.id_modulo",
                "a.nombre",
                "a.url",
                "a.url_activa",
                "a.icon",
                "a.orden"
            );

        if(isset($request->id_rol)) {
            $paginate->where("b.id_rol", $request->id_rol);
        }
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(a.nombre, a.url)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("orden", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }
}