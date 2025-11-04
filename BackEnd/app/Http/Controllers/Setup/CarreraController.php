<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Carrera;

class CarreraController extends Controller
{
    public function activos(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table('carrera as a')
            ->select(
                'a.id_carrera',
                'a.nombre',
                "a.fecha_inicio"
            )
            ->where("a.estado", "1")
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(a.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("nombre", "asc");
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
        $user = $request->sessionUser;

        $paginate = DB::table('carrera as a')
            ->select(
                'a.id_carrera',
                'a.nombre',
                "a.fecha_inicio",
                "a.estado",
                DB::raw("case when a.estado = '1' then 'Activo' else 'Desactivo' end as estado_descripcion")
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(a.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("nombre", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function show(Request $request, $id_carrera)
    {
        $user = $request->sessionUser;

        $carrera = DB::table('carrera as a')
            ->select(
                'a.id_carrera',
                'a.nombre',
                "a.fecha_inicio",
                "a.estado",
                DB::raw("case when a.estado = '1' then 'Activo' else 'Desactivo' end as estado_descripcion")
            )
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_carrera", $id_carrera)
            ->first();

        return response()->json($carrera);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "fecha_inicio" => "required|date",
            "nombre" => "required|max:100",
            "estado" => "required|max:1",
        ], [
            "fecha_inicio.required" => "La fecha inicio es requerida",
            "nombre.required" => "El nombre es requerido",
            "estado.required" => "El estado de inicio es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $carrera = [];
        $carrera["id_empresa"] = $user->id_empresa;
        $carrera["fecha_inicio"] = $request->fecha_inicio;
        $carrera["nombre"] = $request->nombre;
        $carrera["estado"] = $request->estado;
        $carrera["id_usuarioreg"] = $user->id_usuario;
        $carrera["fechareg"] = now();
        $carrera = Carrera::create($carrera);

        return response()->json($carrera);
    }

    public function update(Request $request, $id_carrera)
    {
        $validator = Validator::make($request->all(), [
            "fecha_inicio" => "required|date",
            "nombre" => "required|max:100",
            "estado" => "required|max:1",
        ], [
            "fecha_inicio.required" => "La fecha inicio es requerida",
            "nombre.required" => "El nombre es requerido",
            "estado.required" => "El estado de inicio es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $carrera = Carrera::find($id_carrera);
        if(!$carrera) {
            return response()->json("¡Atención! Carrera no encontrado.", 400);
        }

        $carreraEdit = [];
        $carreraEdit["fecha_inicio"] = $request->fecha_inicio;
        $carreraEdit["nombre"] = $request->nombre;
        $carreraEdit["estado"] = $request->estado;
        $carrera->update($carreraEdit);

        return response()->json($carrera);
    }

    public function destroy($id_carrera)
    {
        $carrera = Carrera::find($id_carrera);
        if(!$carrera) {
            return response()->json("¡Atención! Carrera no encontrado.", 400);
        }

        $carrera->delete();

        return response()->json([]);
    }
}