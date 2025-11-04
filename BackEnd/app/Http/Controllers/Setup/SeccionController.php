<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeccionController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('seccion')
            ->select(
                'id_seccion',
                'nombre'
            )
            ->where("estado", "1");

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->orderBy("nombre", "asc");
        $result = $result->get();

        return response()->json($result);
    }
}