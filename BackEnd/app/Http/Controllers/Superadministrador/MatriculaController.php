<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculaController extends Controller
{
    public function estudiantesActivos(Request $request)
    {
        $result = DB::table('matricula as a')
            ->join("persona as b", "a.id_estudiante", "b.id_persona")
            ->select(
                'a.id_estudiante',
                DB::raw("b.nombre_completo as estudiante_nombre"),
                'b.numero_documento'
            )
            ->where("a.estado", "1");
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(b.nombre_completo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        $result->groupBy("a.id_estudiante", "b.numero_documento", "b.nombre_completo");
        $result->orderBy("estudiante_nombre", "asc");
        $result = $result->get();

        return response()->json($result);
    }

    public function cursosActivos(Request $request)
    {
        $result = DB::table('matricula_curso as a')
            ->join("curso as b", "a.id_curso", "b.id_curso")
            ->join("matricula as c", "a.id_matricula", "c.id_matricula")
            ->select(
                'a.id_matriculacurso',
                'a.id_periodocurso',
                DB::raw("b.nombre as curso_nombre")
            )
            ->where("c.estado", "1");
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(b.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        if(isset($request->id_estudiante)) {
            $result->where("c.id_estudiante", $request->id_estudiante);
        }
        $result->orderBy("curso_nombre", "asc");
        $result = $result->get();

        return response()->json($result);
    }
}