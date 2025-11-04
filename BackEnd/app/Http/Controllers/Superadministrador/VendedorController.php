<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendedorController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('vendedor as a')
            ->join('persona as b', 'a.id_vendedor', 'b.id_persona')
            ->select(
                'a.id_vendedor',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
            );
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(b.nombre_completo, b.telefono, b.correo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        $result->orderBy("nombre_completo", "asc");
        $result = $result->get();

        return response()->json($result);
    }
}