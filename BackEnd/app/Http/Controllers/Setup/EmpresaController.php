<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('empresa as a')
            ->select(
                'a.id_empresa',
                'a.razon_social',
                'a.direccion',
                DB::raw("'Lunes - Viernes 7:00 - 18:00' as empresa_horario"),
                DB::raw("'' as locacion")
            );

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.razon_social, a.direccion)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->orderBy("razon_social", "asc");
        $result = $result->get();

        return response()->json($result);
    }
}