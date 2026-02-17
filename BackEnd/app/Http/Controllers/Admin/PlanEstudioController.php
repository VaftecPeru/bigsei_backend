<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PlanEstudio;
use App\Models\PlanEstudioCiclo;
use App\Models\PlanEstudioCurso;
use App\Models\Curso;
use App\Http\Controllers\Setup\ArchivoController;

class PlanEstudioController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;
        $result = DB::table("plan_estudio as a")
            ->join("carrera as b", "a.id_carrera", "b.id_carrera")
            ->select(
                "a.id_planestudio",
                "a.id_carrera",
                "a.fecha_inicio",
                "a.nombre",
                "a.estado",
                "a.esta_publicado",
                DB::raw("b.nombre as carrera_nombre"),
                DB::raw("case when a.estado = '1' then 'Activo' else 'Desactivo' end as estado_descripcion")
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->id_carrera)) {
            $result->where("a.id_carrera", $request->id_carrera);
        }
        if(isset($request->esta_publicado)) {
            $result->where("a.esta_publicado", $request->esta_publicado);
        }
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.nombre, b.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->orderBy("id_planestudio", "desc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }

    public function show(Request $request, $id_planestudio)
    {
        $user = $request->sessionUser;
        $planEstudio = DB::table("plan_estudio as a")
            ->join("carrera as b", "a.id_carrera", "b.id_carrera")
            ->select(
                "a.id_planestudio",
                "a.id_carrera",
                "a.fecha_inicio",
                "a.nombre",
                "a.estado",
                "a.esta_publicado",
                DB::raw("b.nombre as carrera_nombre"),
                DB::raw("case when a.estado = '1' then 'Activo' else 'Desactivo' end as estado_descripcion")
            )
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_planestudio", $id_planestudio)
            ->first();

        return response()->json($planEstudio);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_carrera" => "required",
            "fecha_inicio" => "required|date",
            "nombre" => "required|max:100",
            "estado" => "required|max:1",
        ], [
            "id_carrera.required" => "La carrera es requerida",
            "fecha_inicio.required" => "La fecha inicio es requerida",
            "nombre.required" => "El nombre es requerido",
            "estado.required" => "El estado de inicio es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $planEstudio = [];
        $planEstudio["id_empresa"] = $user->id_empresa;
        $planEstudio["id_carrera"] = $request->id_carrera;
        $planEstudio["fecha_inicio"] = $request->fecha_inicio;
        $planEstudio["nombre"] = $request->nombre;
        $planEstudio["estado"] = $request->estado;
        $planEstudio["id_usuarioreg"] = $user->id_usuario;
        $planEstudio["fechareg"] = now();
        $planEstudio = PlanEstudio::create($planEstudio);

        return response()->json($planEstudio);
    }

    public function update(Request $request, $id_planestudio)
    {
        $validator = Validator::make($request->all(), [
            "id_carrera" => "required",
            "fecha_inicio" => "required|date",
            "nombre" => "required|max:100",
            "estado" => "required|max:1",
        ], [
            "id_carrera.required" => "La carrera es requerida",
            "fecha_inicio.required" => "La fecha inicio es requerida",
            "nombre.required" => "El nombre es requerido",
            "estado.required" => "El estado de inicio es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $planEstudio = PlanEstudio::find($id_planestudio);
        if(!$planEstudio) {
            return response()->json("¡Atención! Plan de estudio no encontrado.", 400);
        }

        $planEstudioEdit = [];
        $planEstudioEdit["id_carrera"] = $request->id_carrera;
        $planEstudioEdit["fecha_inicio"] = $request->fecha_inicio;
        $planEstudioEdit["nombre"] = $request->nombre;
        $planEstudioEdit["estado"] = $request->estado;
        $planEstudio->update($planEstudioEdit);

        return response()->json($planEstudio);
    }

    public function destroy($id_planestudio)
    {
        $planEstudio = PlanEstudio::find($id_planestudio);
        if(!$planEstudio) {
            return response()->json("¡Atención! Plan de estudio no encontrado.", 400);
        }
        $planEstudio->delete();

        return response()->json([]);
    }

    public function publicar($id_planestudio)
    {
        $planEstudio = PlanEstudio::find($id_planestudio);
        if(!$planEstudio) {
            return response()->json("¡Atención! Plan de estudio no encontrado.", 400);
        }
        if($planEstudio->esta_publicado == "1") {
            return response()->json("¡Atención! Plan de estudio esta publicado.", 400);
        }

        $planEstudioEdit = [];
        $planEstudioEdit["esta_publicado"] = "1";
        $planEstudio->update($planEstudioEdit);

        return response()->json($planEstudio);
    }

    public function indexCicloCheck(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("ciclo as a")
            ->leftJoin("plan_estudio_ciclo as b", "a.id_ciclo", DB::raw("b.id_ciclo and b.id_empresa = ".$user->id_empresa." and b.id_planestudio = ".$request->id_planestudio))
            ->select(
                "a.id_ciclo",
                "a.nombre",
                "a.orden",
                "b.id_planestudiociclo",
                DB::raw("case when b.id_planestudiociclo is not null then '1' else '0' end as ciclo_esta_habilitado")
            );

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(a.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("orden", "asc");
        $paginate->orderBy("id_ciclo", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function indexCiclo(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("plan_estudio_ciclo as a")
            ->join("ciclo as b", "a.id_ciclo", "b.id_ciclo")
            ->select(
                "a.id_planestudiociclo",
                "b.id_ciclo",
                "b.nombre",
                "b.orden"
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->id_planestudio)) {
            $paginate->where("a.id_planestudio", $request->id_planestudio);
        }

        $paginate->orderBy("orden", "asc");
        $paginate->orderBy("id_planestudiociclo", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function storeCiclo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_planestudio" => "required",
            "id_ciclo" => "required",
        ], [
            "id_planestudio.required" => "El plan de estudio es requerido",
            "id_ciclo.required" => "El ciclo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $planEstudioCiclo = [];
        $planEstudioCiclo["id_empresa"] = $user->id_empresa;
        $planEstudioCiclo["id_planestudio"] = $request->id_planestudio;
        $planEstudioCiclo["id_ciclo"] = $request->id_ciclo;
        $planEstudioCiclo["id_usuarioreg"] = $user->id_usuario;
        $planEstudioCiclo["fechareg"] = now();
        $planEstudioCiclo = PlanEstudioCiclo::create($planEstudioCiclo);

        return response()->json($planEstudioCiclo);
    }

    public function destroyCiclo($id_planestudiociclo)
    {
        $planEstudioCiclo = PlanEstudioCiclo::find($id_planestudiociclo);
        if(!$planEstudioCiclo) {
            return response()->json("¡Atención! Ciclo no encontrado.", 400);
        }
        $planEstudioCiclo->delete();

        return response()->json([]);
    }

    public function indexCurso(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("plan_estudio_curso as a")
            ->join("curso as b", "a.id_curso", "b.id_curso")
            ->select(
                "a.id_planestudiocurso",
                "b.id_curso",
                "b.nombre",
                "b.codigo",
                "a.horas_semanal",
                "a.creditos",
                DB::raw("case when a.tipo = 'O' then 'Obligatorio' when a.tipo = 'E' then 'Electivo' else '' end tipo_descripcion")
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->id_planestudiociclo)) {
            $paginate->where("a.id_planestudiociclo", $request->id_planestudiociclo);
        }

        $paginate->orderBy("id_planestudiocurso", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function showCurso(Request $request, $id_planestudiocurso)
    {
        $user = $request->sessionUser;

        $planEstudioCurso = DB::table("plan_estudio_curso as a")
            ->join("curso as b", "a.id_curso", "b.id_curso")
            ->leftJoin("archivo as c", "b.id_archivo", "c.id_archivo")
            ->select(
                "a.id_planestudiocurso",
                "b.id_curso",
                "b.nombre",
                "b.codigo",
                "a.horas_semanal",
                "a.creditos",
                "a.tipo",
                DB::raw("c.url as archivo_url"),
                DB::raw("case when a.tipo = 'O' then 'Obligatorio' when a.tipo = 'E' then 'Electivo' else '' end tipo_descripcion")
            )
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_planestudiocurso", $id_planestudiocurso)
            ->first();

        return response()->json($planEstudioCurso);
    }

    public function storeCurso(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_planestudiociclo" => "required|max:50",
            "curso_codigo" => "required|max:50",
            "curso_nombre" => "required|max:255",
            "creditos" => "required|numeric|not_in:0",
            "horas_semanal" => "required|numeric|not_in:0",
            "tipo" => "required|max:1",
            "file" => "nullable|mimes:jpeg,bmp,png,webp,avif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "id_planestudiociclo.required" => "El ciclo es requerido",
            "curso_codigo.required" => "El código es requerido",
            "curso_nombre.required" => "El nombre es requerido",
            "creditos.required" => "El créditos es requerido",
            "horas_semanal.required" => "Las horas semanal es requerido",
            "tipo.required" => "El tipo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $planEstudioCiclo = PlanEstudioCiclo::find($request->id_planestudiociclo);
        if(!$planEstudioCiclo) {
            return response()->json("¡Atención! Ciclo no encontrado.", 400);
        }

        $user = $request->sessionUser;

        $archivo = null;
        if ($request->hasFile("file")) {
            $archivo = ArchivoController::registrarArchivo($request->file, $user->id_usuario, "5", null, null, null);
        }

        $curso = [];
        $curso["codigo"] = $request->curso_codigo;
        $curso["id_empresa"] = $user->id_empresa;
        $curso["nombre"] = $request->curso_nombre;
        if($archivo) {
            $curso["id_archivo"] = $archivo->id_archivo;
        }
        $curso = Curso::create($curso);

        $planEstudioCurso = [];
        $planEstudioCurso["id_empresa"] = $user->id_empresa;
        $planEstudioCurso["id_planestudio"] = $planEstudioCiclo->id_planestudio;
        $planEstudioCurso["id_planestudiociclo"] = $request->id_planestudiociclo;
        $planEstudioCurso["id_curso"] = $curso->id_curso;
        $planEstudioCurso["creditos"] = $request->creditos;
        $planEstudioCurso["horas_semanal"] = $request->horas_semanal;
        $planEstudioCurso["tipo"] = $request->tipo;
        $planEstudioCurso["id_usuarioreg"] = $user->id_usuario;
        $planEstudioCurso["fechareg"] = now();
        $planEstudioCurso = PlanEstudioCurso::create($planEstudioCurso);

        return response()->json($planEstudioCurso);
    }

    public function updateCurso(Request $request, $id_planestudiocurso)
    {
        $validator = Validator::make($request->all(), [
            "curso_codigo" => "required|max:50",
            "curso_nombre" => "required|max:255",
            "creditos" => "required|numeric|not_in:0",
            "horas_semanal" => "required|numeric|not_in:0",
            "tipo" => "required|max:1",
            "file" => "nullable|mimes:jpeg,bmp,png,webp,avif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "curso_codigo.required" => "El código es requerido",
            "curso_nombre.required" => "El nombre es requerido",
            "creditos.required" => "El créditos es requerido",
            "horas_semanal.required" => "Las horas semanal es requerido",
            "tipo.required" => "El tipo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $planEstudioCurso = PlanEstudioCurso::find($id_planestudiocurso);
        if(!$planEstudioCurso) {
            return response()->json("¡Atención! Curso no encontrado.", 400);
        }
        $curso = Curso::find($planEstudioCurso->id_curso);

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
        $cursoEdit["codigo"] = $request->curso_codigo;
        $cursoEdit["nombre"] = $request->curso_nombre;
        if($archivo) {
            $cursoEdit["id_archivo"] = $archivo->id_archivo;
        }
        $curso->update($cursoEdit);

        $planEstudioCursoEdit = [];
        $planEstudioCursoEdit["creditos"] = $request->creditos;
        $planEstudioCursoEdit["horas_semanal"] = $request->horas_semanal;
        $planEstudioCursoEdit["tipo"] = $request->tipo;
        $planEstudioCurso->update($planEstudioCursoEdit);

        $planEstudioCurso = PlanEstudioCurso::find($id_planestudiocurso);

        return response()->json($planEstudioCurso);
    }

    public function destroyCurso($id_planestudiocurso)
    {
        $planEstudioCurso = PlanEstudioCurso::find($id_planestudiocurso);
        if(!$planEstudioCurso) {
            return response()->json("¡Atención! Curso no encontrado.", 400);
        }
        $curso = Curso::find($planEstudioCurso->id_curso);

        $planEstudioCurso->delete();
        $curso->delete();

        return response()->json([]);
    }

    public function estadisticas(Request $request)
    {
        $user = $request->sessionUser;

        $first = DB::table("plan_estudio_ciclo as a")
            ->join("plan_estudio_curso as b", "a.id_planestudiociclo", "b.id_planestudiociclo")
            ->select(
                "a.id_planestudio",
                DB::raw("count(1) as total_cursos"),
                DB::raw("sum(b.creditos) as total_creditos"),
                DB::raw("sum(b.horas_semanal) as total_horas_semanal")
            )
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_planestudio", $request->id_planestudio)
            ->groupBy("a.id_planestudio")
            ->first();

        $estadistica = [];
        $estadistica["total_cursos"] = $first ? $first->total_cursos : 0;
        $estadistica["total_creditos"] = $first ? $first->total_creditos : 0;
        $estadistica["total_horas_semanal"] = $first ? $first->total_horas_semanal : 0;

        $total_ciclos = DB::table("plan_estudio_ciclo as a")
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_planestudio", $request->id_planestudio)
            ->count();

        $estadistica["total_ciclos"] = $total_ciclos;

        return response()->json($estadistica);
    }
}