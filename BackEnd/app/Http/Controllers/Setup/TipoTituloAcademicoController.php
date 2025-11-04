<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoTituloAcademicoController extends Controller
{
    public function activos(Request $request)
    {
        $result = DB::table('tipo_titulo_academico as a')
            ->select(
                'a.id_tipotituloacademico',
                'a.nombre',
                "a.orden"
            )
            ->where("a.estado", "1");

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->orderBy("orden", "asc");
        $result->orderBy("id_tipotituloacademico", "asc");
        $result = $result->get();

        return response()->json($result);
    }
}