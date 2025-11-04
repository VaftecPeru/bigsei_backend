<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Ciclo;

class CicloController extends Controller
{
    public function activos(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("ciclo")
            ->select(
                "id_ciclo",
                "nombre",
                "orden"
            )
            ->where("estado", "1");

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("orden", "asc");
        $paginate->orderBy("id_ciclo", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("ciclo")
            ->select(
                "id_ciclo",
                "nombre",
                "orden",
                "estado",
                DB::raw("case when estado = '1' then 'Activo' else 'Desactivo' end as estado_descripcion")
            );

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("orden", "asc");
        $paginate->orderBy("id_ciclo", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function show($id_ciclo)
    {
        $ciclo = DB::table("ciclo")
            ->select(
                "id_ciclo",
                "nombre",
                "orden",
                "estado"
            )
            ->where("id_ciclo", $id_ciclo)
            ->first();

        return response()->json($ciclo);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|max:100",
            "orden" => "required|numeric|not_in:0",
            "estado" => "required|max:1",
        ], [
            "nombre.required" => "El nombre es requerido",
            "orden.required" => "El orden es requerido",
            "estado.required" => "El estado de inicio es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $ciclo = [];
        $ciclo["nombre"] = $request->nombre;
        $ciclo["orden"] = $request->orden;
        $ciclo["estado"] = $request->estado;
        $ciclo = Ciclo::create($ciclo);

        return response()->json($ciclo);
    }

    public function update(Request $request, $id_ciclo)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|max:100",
            "orden" => "required|numeric|not_in:0",
            "estado" => "required|max:1",
        ], [
            "nombre.required" => "El nombre es requerido",
            "orden.required" => "El orden es requerido",
            "estado.required" => "El estado de inicio es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $ciclo = Ciclo::find($id_ciclo);
        if(!$ciclo) {
            return response()->json("¡Atención! Ciclo no encontrado.", 400);
        }

        $cicloEdit = [];
        $cicloEdit["nombre"] = $request->nombre;
        $cicloEdit["orden"] = $request->orden;
        $cicloEdit["estado"] = $request->estado;
        $ciclo->update($cicloEdit);

        return response()->json($ciclo);
    }

    public function destroy($id_ciclo)
    {
        $ciclo = Ciclo::find($id_ciclo);
        if(!$ciclo) {
            return response()->json("¡Atención! Ciclo no encontrado.", 400);
        }

        $ciclo->delete();

        return response()->json([]);
    }
}