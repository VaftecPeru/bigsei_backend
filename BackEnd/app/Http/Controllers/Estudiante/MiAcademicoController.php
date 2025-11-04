<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PeriodoEntregaTarea;
use App\Models\PeriodoEntregaRespuesta;
use App\Models\PeriodoRespuesta;
use App\Http\Controllers\Setup\ArchivoController;

class MiAcademicoController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("periodo as a")
            ->join("matricula as b", "a.id_periodo", "b.id_periodo")
            ->select(
                "a.id_periodo",
                "a.nombre"
            )
            ->where("b.id_estudiante", $user->id_usuario)
            ->where("b.id_empresa", $user->id_empresa)
            ->groupBy("a.id_periodo", "a.nombre")
            ->orderBy("id_periodo", "desc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function indexPeriodoCurso(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $result = DB::table("matricula as z")
            ->join("matricula_curso as y", "z.id_matricula", "y.id_matricula")
            ->join("periodo as x", "z.id_periodo", "x.id_periodo")
            ->join("periodo_curso as a", "y.id_periodocurso", "a.id_periodocurso")
            ->join("persona as b", "a.id_docente", "b.id_persona")
            ->join("curso as c", "a.id_curso", "c.id_curso")
            ->join("tipo_modalidadestudio as d", "a.id_tipomodalidadestudio", "d.id_tipomodalidadestudio")
            ->select(
                "a.id_periodocurso",
                "b.correo",
                DB::raw("b.nombre_completo as docente_nombre"),
                DB::raw("c.nombre as curso_nombre"),
                DB::raw(
                    "(select max(x.nombre) from periodo_horario z inner join aula x on
                    z.id_aula = x.id_aula and z.id_periodocurso = a.id_periodocurso) as aula_nombre"
                ),
                DB::raw(
                    "(select max(concat(x.nombre, ': ', z.hora_inicio, ' a ', z.hora_fin)) from periodo_horario z inner join dia x on
                    z.id_dia = x.id_dia and z.id_periodocurso = a.id_periodocurso) as horario_descripcion"
                ),
                DB::raw("x.nombre as periodo_nombre")
            )
            ->where("z.id_estudiante", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa);

        if (isset($request->id_periodociclo)) {
            $result->where("a.id_periodociclo", $request->id_periodociclo);
        }
        if (isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(c.nombre)) LIKE upper( ? )", ['%' . $texto . '%']);
        }

        $result->orderBy("id_periodocurso", "desc");
        $result = $result->paginate($per_page)
            ->through(fn ($licenciaTipo) => [
                "id_periodocurso" => $licenciaTipo->id_periodocurso,
                "correo" => $licenciaTipo->correo,
                "periodo_nombre" => $licenciaTipo->periodo_nombre,
                "docente_nombre" => $licenciaTipo->docente_nombre,
                "curso_nombre" => $licenciaTipo->curso_nombre,
                "aula_nombre" => $licenciaTipo->aula_nombre ?? "(No definido)",
                "horario_dias" => $licenciaTipo->horario_descripcion ?? "(No definido)",
                "horario_horas" => $licenciaTipo->horario_descripcion ?? "(No definido)",
            ]);

        return response()->json($result);
    }

    public function showPeriodoCurso(Request $request, $id_periodocurso)
    {
        $user = $request->sessionUser;
        $result = DB::table("matricula as z")
            ->join("matricula_curso as y", "z.id_matricula", "y.id_matricula")
            ->join("periodo_curso as a", "y.id_periodocurso", "a.id_periodocurso")
            ->join("persona as b", "a.id_docente", "b.id_persona")
            ->join("curso as c", "a.id_curso", "c.id_curso")
            ->join("periodo_ciclo as d", "a.id_periodociclo", "d.id_periodociclo")
            ->join("ciclo as e", "d.id_ciclo", "e.id_ciclo")
            ->join("periodo as f", "d.id_periodo", "f.id_periodo")
            ->join("carrera as g", "d.id_carrera", "g.id_carrera")
            ->join("plan_estudio_ciclo as h", "d.id_planestudiociclo", "h.id_planestudiociclo")
            ->join("plan_estudio as i", "h.id_planestudio", "i.id_planestudio")
            ->select(
                "a.id_periodocurso",
                "a.vacantes",
                "a.url_zoom",
                "b.correo",
                "c.url_img",
                DB::raw("b.nombre_completo as docente_nombre"),
                DB::raw("b.correo as docente_correo"),
                DB::raw("b.id_archivo_foto as docente_id_archivo_foto"),
                DB::raw("c.nombre as curso_nombre"),
                DB::raw("c.codigo as curso_codigo"),
                DB::raw(
                    "(select max(concat(x.nombre, ': ', z.hora_inicio, ' a ', z.hora_fin)) from periodo_horario z inner join dia x on
                    z.id_dia = x.id_dia and z.id_periodocurso = a.id_periodocurso) as horario_descripcion"
                ),
                DB::raw("e.nombre as ciclo_nombre"),
                DB::raw("f.nombre as periodo_nombre"),
                DB::raw("g.nombre as carrera_nombre"),
                DB::raw("i.nombre as planestudio_nombre"),
                DB::raw("year(i.fecha_inicio) as planestudio_anho_ff")
            )
            ->where("z.id_estudiante", $user->id_usuario)
            ->where("a.id_periodocurso", $id_periodocurso)
            ->first();

        if($result) {
            $result->horario_dias = $result->horario_descripcion ?? "(No definido)";
            $result->horario_horas = $result->horario_descripcion ?? "(No definido)";
        }

        return response()->json($result);
    }

    public function indexPeriodoModulo(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $result = DB::table("periodo_modulo as a")
            ->join("periodo_curso as b", "a.id_periodocurso", "b.id_periodocurso")
            ->join("matricula_curso as c", "b.id_periodocurso", "c.id_periodocurso")
            ->join("matricula as d", "c.id_matricula", "d.id_matricula")
            ->select(
                "a.id_periodomodulo",
                "a.titulo",
                "a.descripcion"
            )
            ->where("d.id_estudiante", $user->id_usuario)
            ->where("b.id_empresa", $user->id_empresa);

        if (isset($request->id_periodocurso)) {
            $result->where("a.id_periodocurso", $request->id_periodocurso);
        }

        $result->orderBy("id_periodomodulo", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }

    public function indexPeriodoTema(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $result = DB::table('periodo_tema as a')
            ->join('periodo_modulo as b', 'a.id_periodomodulo', 'b.id_periodomodulo')
            ->join('periodo_curso as c', 'b.id_periodocurso', 'c.id_periodocurso')
            ->join("matricula_curso as d", "c.id_periodocurso", "d.id_periodocurso")
            ->join("matricula as e", "d.id_matricula", "e.id_matricula")
            ->select(
                "a.id_periodotema",
                "a.titulo",
                "a.descripcion",
                "a.fecha",
                "a.id_tipocategoria",
                DB::raw("a.fecha as dia_nombre")
            )
            ->where("e.id_estudiante", $user->id_usuario)
            ->where("c.id_empresa", $user->id_empresa);

        if (isset($request->id_periodomodulo)) {
            $result->where("a.id_periodomodulo", $request->id_periodomodulo);
        }

        $result->orderBy("fecha", "asc");
        $result->orderBy("id_periodotema", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }

    public function indexPeriodoTarea(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $periodoTareas = DB::table("periodo_tarea")
            ->select(
                "id_periodotarea",
                "id_periodotema",
                "titulo",
                "instruccion",
                "fecha_entrega",
                "hora_entrega",
                "numero_intentos",
                "calificacion_maxima",
                "fecha_mostrar_desde",
                "fecha_mostrar_hasta",
                DB::raw("date_format(fecha_entrega, '%d/%m/%y') as fecha_entrega_ff"),
                DB::raw("date_format(hora_entrega, '%h:%i %p') as hora_entrega_ff"),
                DB::raw("date_format(fecha_mostrar_desde, '%d/%m/%y %h:%i %p') as fecha_mostrar_desde_ff"),
                DB::raw("date_format(fecha_mostrar_hasta, '%d/%m/%y %h:%i %p') as fecha_mostrar_hasta_ff")
            )
            ->where("id_periodotema", $request->id_periodotema)
            ->orderBy("id_periodotarea", "asc")
            ->paginate($per_page);

        return response()->json($periodoTareas);
    }

    public function indexEntregaTarea(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $periodoTareas = DB::table("periodo_entrega_tarea as a")
            ->join("archivo as b", "a.id_archivo", "b.id_archivo")
            ->select(
                "a.id_periodoentregatarea",
                "a.id_archivo",
                "a.comentario",
                DB::raw("b.nombre as archivo_nombre"),
                DB::raw("b.tamanho as archivo_tamanho"),
                DB::raw("date_format(b.fechareg, '%d/%m/%Y %h:%i %p') as archivo_fecha")
            )
            ->where("a.id_estudiante", $user->id_usuario)
            ->where("a.id_periodotarea", $request->id_periodotarea)
            ->orderBy("id_periodoentregatarea", "asc")
            ->paginate($per_page);

        return response()->json($periodoTareas);
    }

    public function storeEntregaTarea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodotarea" => "required",
            "comentario" => "required|max:255",
            // "file" => "required|mimes:jpeg,bmp,png|size:16000", // 16 MB (16000 kB). maximo 16 MegaBytes
            "file" => "required|mimes:jpeg,bmp,png", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "id_periodotarea.required" => "La tarea es requerida",
            "comentario.required" => "El comentario es requerido",
            "comentario.max" => "El comentario tiene un máximo 255 caracteres",
            "file.required" => "El archivo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        if (!$request->hasFile("file")) {
            return response()->json("¡Atención! Archivo no encontrado.", 400);
        }

        $periodoEntregaTarea = PeriodoEntregaTarea::where("id_periodotarea", $request->id_periodotarea)->first();
        if ($periodoEntregaTarea) {
            return response()->json("¡Atención! Tarea entregada.", 400);
        }

        $user = $request->sessionUser;

        $archivo = ArchivoController::registrarArchivo($request->file('file'), $user->id_usuario, "5", $id_periodotarea = null, $id_periodotema = null, $id_persona = null);

        $periodoEntregaTarea = [];
        $periodoEntregaTarea["id_empresa"] = $user->id_empresa;
        $periodoEntregaTarea["id_estudiante"] = $user->id_usuario;
        $periodoEntregaTarea["id_periodotarea"] = $request->id_periodotarea;
        $periodoEntregaTarea["id_archivo"] = $archivo->id_archivo;
        $periodoEntregaTarea["comentario"] = $request->comentario;
        $periodoEntregaTarea["id_usuarioreg"] = $user->id_usuario;
        $periodoEntregaTarea["fechareg"] = now();
        $periodoEntregaTarea = PeriodoEntregaTarea::create($periodoEntregaTarea);

        $periodoEntregaTarea = PeriodoEntregaTarea::find($periodoEntregaTarea->id_periodoentregatarea);

        return response()->json($periodoEntregaTarea);
    }

    public function indexPeriodoVideo(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        DB::statement("SET lc_time_names = 'es_ES'");
        $periodoTareas = DB::table("periodo_video")
            ->select(
                "id_periodovideo",
                "nombre",
                "url",
                "descripcion",
                "tipo",
                "tiene_contenido",
                DB::raw("concat(dayname(fechareg), ' ', date_format(fechareg, '%d de %M, %Y')) as fecha_full")
            )
            ->where("id_periodotema", $request->id_periodotema)
            ->orderBy("id_periodovideo", "asc")
            ->paginate($per_page);

        return response()->json($periodoTareas);
    }

    public function indexPeriodoCuestionario(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        DB::statement("SET lc_time_names = 'es_ES'");
        $periodoCuestionarios = DB::table("periodo_cuestionario as a")
            ->join("periodo_pregunta as b", "a.id_periodocuestionario", "b.id_periodocuestionario")
            ->leftJoin("periodo_entrega_respuesta as c", "b.id_periodopregunta", DB::raw("c.id_periodopregunta and c.id_estudiante = ".$user->id_usuario))
            ->select(
                "a.id_periodocuestionario",
                "a.id_periodotema",
                "a.titulo",
                "a.instruccion",
                DB::raw("concat(dayname(a.fechareg), ' ', date_format(a.fechareg, '%d de %M, %Y')) as fecha_full"),
                DB::raw("count(1) as preguntas_total"),
                DB::raw("sum(case when c.es_correcto = '1' then 1 else 0 end) as preguntas_correcto"),
                DB::raw("sum(case when c.es_correcto = '0' then 1 else 0 end) as preguntas_incorrecto"),
                DB::raw("sum(case when c.es_correcto is null then 1 else 0 end) as preguntas_nulo")
            )
            ->where("a.id_periodotema", $request->id_periodotema)
            ->groupBy("a.id_periodocuestionario", "a.id_periodotema", "a.titulo", "a.instruccion", "a.fechareg")
            ->orderBy("id_periodocuestionario", "asc")
            ->paginate($per_page);

        return response()->json($periodoCuestionarios);
    }

    public function showPeriodoCuestionario(Request $request, $id_periodocuestionario)
    {
        $user = $request->sessionUser;
        DB::statement("SET lc_time_names = 'es_ES'");
        $periodoCuestionario = DB::table("periodo_cuestionario as a")
            ->join("periodo_pregunta as b", "a.id_periodocuestionario", "b.id_periodocuestionario")
            ->leftJoin("periodo_entrega_respuesta as c", "b.id_periodopregunta", DB::raw("c.id_periodopregunta and c.id_estudiante = ".$user->id_usuario))
            ->select(
                "a.id_periodocuestionario",
                "a.id_periodotema",
                "a.titulo",
                "a.instruccion",
                DB::raw("concat(dayname(a.fechareg), ' ', date_format(a.fechareg, '%d de %M, %Y')) as fecha_full"),
                DB::raw("count(1) as preguntas_total"),
                DB::raw("sum(case when c.es_correcto = '1' then 1 else 0 end) as preguntas_correcto"),
                DB::raw("sum(case when c.es_correcto = '0' then 1 else 0 end) as preguntas_incorrecto"),
                DB::raw("sum(case when c.es_correcto is null then 1 else 0 end) as preguntas_nulo")
            )
            ->where("a.id_periodocuestionario", $id_periodocuestionario)
            ->groupBy("a.id_periodocuestionario", "a.id_periodotema", "a.titulo", "a.instruccion", "a.fechareg")
            ->first();

        return response()->json($periodoCuestionario);
    }

    public function indexPeriodoPregunta(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $periodoPreguntas = DB::table("periodo_pregunta as a")
            ->join("tipo_pregunta as b", "a.id_tipopregunta", "b.id_tipopregunta")
            // ->leftJoin("periodo_ as b", "a.id_tipopregunta", "b.id_tipopregunta")
            ->leftJoin("periodo_entrega_respuesta as c", "a.id_periodopregunta", DB::raw("c.id_periodopregunta and c.id_estudiante = ".$user->id_usuario))
            ->select(
                "a.id_periodopregunta",
                "a.id_periodocuestionario",
                "a.descripcion",
                "a.orden",
                "a.es_requerida",
                "a.id_tipopregunta",
                DB::raw("b.codigo as tipopregunta_codigo"),
                DB::raw("5 as puntos"),
                "c.id_periodorespuesta"
            )
            ->where("a.id_periodocuestionario", $request->id_periodocuestionario)
            ->orderBy("orden", "asc")
            ->orderBy("id_periodopregunta", "asc")
            ->paginate($per_page)
            ->through(fn ($pregunta) => [
                "id_periodopregunta" => $pregunta->id_periodopregunta,
                "id_periodocuestionario" => $pregunta->id_periodocuestionario,
                "descripcion" => $pregunta->descripcion,
                "orden" => $pregunta->orden,
                "es_requerida" => $pregunta->es_requerida,
                "id_tipopregunta" => $pregunta->id_tipopregunta,
                "tipopregunta_codigo" => $pregunta->tipopregunta_codigo,
                "puntos" => $pregunta->puntos,
                "id_periodorespuesta" => $pregunta->id_periodorespuesta,
                "respuestas" => DB::table("periodo_respuesta as a")
                    ->select(
                        "a.id_periodorespuesta",
                        "a.descripcion",
                        "a.orden"
                    )
                    ->where("a.id_periodopregunta", $pregunta->id_periodopregunta)
                    ->orderBy("orden", "asc")
                    ->get(),
            ]);

        return response()->json($periodoPreguntas);
    }

    public function storeEntregaRespuesta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodopregunta" => "required",
            "id_periodorespuesta" => "required",
        ], [
            "id_periodopregunta.required" => "La pregunta es requerida",
            "id_periodorespuesta.required" => "La respuesta es requerida",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;
        $periodoRespuesta = PeriodoRespuesta::find($request->id_periodorespuesta);

        $periodoEntregaRespuesta = PeriodoEntregaRespuesta::where("id_periodopregunta", $request->id_periodopregunta)
            ->where("id_estudiante", $user->id_usuario)
            ->first();
        if ($periodoEntregaRespuesta) {
            $periodoEntregaRespuestaEdit = [];
            $periodoEntregaRespuestaEdit["id_periodorespuesta"] = $request->id_periodorespuesta;
            $periodoEntregaRespuestaEdit["es_correcto"] = $periodoRespuesta->es_valida;
            $periodoEntregaRespuesta->update($periodoEntregaRespuestaEdit);
        } else {
            $periodoEntregaRespuesta = [];
            $periodoEntregaRespuesta["id_empresa"] = $user->id_empresa;
            $periodoEntregaRespuesta["id_periodopregunta"] = $request->id_periodopregunta;
            $periodoEntregaRespuesta["id_periodorespuesta"] = $request->id_periodorespuesta;
            $periodoEntregaRespuesta["id_estudiante"] = $user->id_usuario;
            $periodoEntregaRespuesta["es_correcto"] = $periodoRespuesta->es_valida;
            $periodoEntregaRespuesta["id_usuarioreg"] = $user->id_usuario;
            $periodoEntregaRespuesta["fechareg"] = now();
            $periodoEntregaRespuesta = PeriodoEntregaRespuesta::create($periodoEntregaRespuesta);
        }

        return response()->json([]);
    }
}