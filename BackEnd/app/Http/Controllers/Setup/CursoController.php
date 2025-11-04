<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Archivo;
use App\Models\Curso;
use App\Models\PlanEstudioCurso;
use App\Http\Controllers\Setup\ArchivoController;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("curso as a")
            ->select(
                "a.id_curso",
                "a.nombre",
                "a.codigo",
                "a.id_archivo"
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(a.nombre, a.codigo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("nombre", "asc");
        $paginate->orderBy("id_curso", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function show($id_curso)
    {
        $curso = DB::table("curso as a")
            ->leftJoin("archivo as b", "a.id_archivo", "b.id_archivo")
            ->select(
                "a.id_curso",
                "a.nombre",
                "a.codigo",
                DB::raw("b.url as archivo_url")
            )
            ->where("a.id_curso", $id_curso)
            ->first();

        return response()->json($curso);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|max:255",
            "codigo" => "required|max:50",
        ], [
            "nombre.required" => "El nombre es requerido",
            "codigo.required" => "El código es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $archivo = null;
        if ($request->hasFile("file")) {
            $archivo = ArchivoController::registrarArchivo($request->file, $user->id_usuario, "5", null, null, null);
        }

        $curso = [];
        $curso["id_empresa"] = $user->id_empresa;
        $curso["nombre"] = $request->nombre;
        $curso["codigo"] = $request->codigo;
        if($archivo) {
            $curso["id_archivo"] = $archivo->id_archivo;
        }
        $curso = Curso::create($curso);

        return response()->json($curso);
    }

    public function update(Request $request, $id_curso)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|max:255",
            "codigo" => "required|max:50",
        ], [
            "nombre.required" => "El nombre es requerido",
            "codigo.required" => "El código es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $curso = Curso::find($id_curso);
        if(!$curso) {
            return response()->json("¡Atención! Curso no encontrado.", 400);
        }

        $user = $request->sessionUser;

        $archivo = null;
        if($request->hasFile("file")) {
            if($curso->id_archivo) {
                $archivo = ArchivoController::editarArchivo($request->file, $curso->id_archivo);
            } else {
                $archivo = ArchivoController::registrarArchivo($request->file, $user->id_usuario, "5", null, null, null);
            }
        }

        $cursoEdit = [];
        $cursoEdit["nombre"] = $request->nombre;
        $cursoEdit["codigo"] = $request->codigo;
        if($archivo) {
            $cursoEdit["id_archivo"] = $archivo->id_archivo;
        }
        $curso->update($cursoEdit);

        return response()->json($curso);
    }

    public function destroy(Request $request, $id_curso)
    {
        $user = $request->sessionUser;

        $curso = Curso::where("id_empresa", $user->id_empresa)
            ->where("id_curso", $id_curso)
            ->first();
        if(!$curso) {
            return response()->json("¡Atención! Curso no encontrado.", 400);
        }

        $planEstudioCurso = PlanEstudioCurso::where("id_curso", $id_curso)->first();
        if($planEstudioCurso) {
            return response()->json("¡Atención! Plan estudio depende del curso.", 400);
        }

        $archivo = null;
        if($curso->id_archivo) {
            $archivo = Archivo::find($curso->id_archivo);
        }

        $curso->delete();
        if($archivo) {
            $archivo->delete();
        }

        return response()->json([]);
    }
}