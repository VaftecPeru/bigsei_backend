<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Aula;

class AulaController extends Controller
{
    public function activos(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $aulas = DB::table("aula")
            ->select(
                "id_aula",
                "nombre"
            )
            ->where("estado", "1")
            ->where("id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $aulas->whereRaw("upper(concat(nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $aulas = $aulas->orderBy("nombre", "asc")
            ->orderBy("id_aula", "asc")
            ->paginate($per_page);

        return response()->json($aulas);
    }

    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $aulas = DB::table("aula")
            ->select(
                "id_aula",
                "nombre",
                "estado",
                DB::raw("case when estado = '1' then 'Activo' else 'Desactivo' end as estado_descripcion")
            )
            ->where("id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $aulas->whereRaw("upper(concat(nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $aulas = $aulas->orderBy("nombre", "asc")
            ->orderBy("id_aula", "asc")
            ->paginate($per_page);

        return response()->json($aulas);
    }

    public function show(Request $request, $id_aula)
    {
        $user = $request->sessionUser;

        $aula = DB::table("aula")
            ->select(
                "id_aula",
                "nombre",
                "estado",
                DB::raw("case when estado = '1' then 'Activo' else 'Desactivo' end as estado_descripcion")
            )
            ->where("id_empresa", $user->id_empresa)
            ->where("id_aula", $id_aula)
            ->first();

        return response()->json($aula);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|max:100",
            "estado" => "required|max:1",
        ], [
            "nombre.required" => "El nombre es requerido",
            "estado.required" => "El estado de inicio es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $aula = [];
        $aula["id_empresa"] = $user->id_empresa;
        $aula["nombre"] = $request->nombre;
        $aula["estado"] = $request->estado;
        $aula = Aula::create($aula);

        return response()->json($aula);
    }

    public function update(Request $request, $id_aula)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|max:100",
            "estado" => "required|max:1",
        ], [
            "nombre.required" => "El nombre es requerido",
            "estado.required" => "El estado de inicio es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $aula = Aula::find($id_aula);
        if(!$aula) {
            return response()->json("¡Atención! Aula no encontrado.", 400);
        }

        $aulaEdit = [];
        $aulaEdit["nombre"] = $request->nombre;
        $aulaEdit["estado"] = $request->estado;
        $aula->update($aulaEdit);

        return response()->json($aula);
    }

    public function destroy($id_aula)
    {
        $aula = Aula::find($id_aula);
        if(!$aula) {
            return response()->json("¡Atención! Aula no encontrado.", 400);
        }

        $aula->delete();

        return response()->json([]);
    }
}