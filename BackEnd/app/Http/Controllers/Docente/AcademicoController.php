<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\CursoDocentes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PeriodoModulo;
use App\Models\PeriodoTema;
use App\Models\PeriodoTarea;
use App\Models\PeriodoVideo;
use App\Models\PeriodoCuestionario;
use App\Models\PeriodoPregunta;
use App\Models\PeriodoRespuesta;
use App\Models\MensajeriaGrupo;
use App\Http\Controllers\Docente\ArchivoController;

class AcademicoController extends Controller
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
            ->join("periodo_ciclo as b", "a.id_periodo", "b.id_periodo")
            ->join("periodo_curso as c", "b.id_periodociclo", "c.id_periodociclo")
            ->select(
                "a.id_periodo",
                "a.nombre"
            )
            ->where("c.id_docente", $user->id_usuario)
            ->where("c.id_empresa", $user->id_empresa)
            ->groupBy("a.id_periodo", "a.nombre")
            ->orderBy("id_periodo", "desc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function indexPeriodoCiclo(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("periodo_ciclo as a")
            ->join("periodo_curso as b", "a.id_periodociclo", "b.id_periodociclo")
            ->join("ciclo as c", "a.id_ciclo", "c.id_ciclo")
            ->join("carrera as d", "a.id_carrera", "d.id_carrera")
            ->leftJoin("plan_estudio_ciclo as e", "a.id_planestudiociclo", "e.id_planestudiociclo")
            ->leftJoin("plan_estudio as f", "e.id_planestudio", "f.id_planestudio")
            ->select(
                "a.id_periodociclo",
                "a.id_ciclo",
                DB::raw("max(c.nombre) as ciclo_nombre"),
                DB::raw("max(d.nombre) as carrera_nombre"),
                DB::raw("max(f.nombre) as planestudio_nombre"),
                DB::raw("max(year(f.fecha_inicio)) as planestudio_anho")
            )
            ->where("b.id_docente", $user->id_usuario)
            ->where("b.id_empresa", $user->id_empresa)
            ->where("a.id_periodo", $request->id_periodo)
            ->groupBy("a.id_periodociclo", "a.id_ciclo")
            ->orderBy("id_ciclo", "asc")
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

        $periodoCursos = DB::table('periodo_curso as a')
            ->join('persona as b', 'a.id_docente', 'b.id_persona')
            ->join('curso as c', 'a.id_curso', 'c.id_curso')
            ->join('tipo_modalidadestudio as d', 'a.id_tipomodalidadestudio', 'd.id_tipomodalidadestudio')
            ->join("periodo as e", "a.id_periodo", "e.id_periodo")
            ->select(
                "a.id_periodocurso",
                "b.correo",
                DB::raw("b.nombre_completo as docente_nombre"),
                DB::raw("c.nombre as curso_nombre"),
                DB::raw(
                    "(select group_concat(distinct x.nombre order by x.nombre asc separator ', ') from periodo_horario z inner join aula x on
                    z.id_aula = x.id_aula and z.id_periodocurso = a.id_periodocurso) as aula_nombre"
                ),
                DB::raw(
                    "(select group_concat(x.nombre order by x.orden asc separator ', ') from periodo_horario z inner join dia x on
                    z.id_dia = x.id_dia and z.id_periodocurso = a.id_periodocurso) as horario_dias"
                ),
                DB::raw(
                    "(select group_concat(concat(z.hora_inicio, ' a ', z.hora_fin) order by x.orden asc separator ', ') from periodo_horario z inner join dia x on
                    z.id_dia = x.id_dia and z.id_periodocurso = a.id_periodocurso) as horario_horas"
                ),
                DB::raw("e.nombre as periodo_nombre")
            )
            ->where("a.id_docente", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa);

        if (isset($request->id_periodociclo)) {
            $periodoCursos->where("a.id_periodociclo", $request->id_periodociclo);
        }
        if (isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $periodoCursos->whereRaw("upper(concat(c.nombre)) LIKE upper( ? )", ['%' . $texto . '%']);
        }

        $periodoCursos->orderBy("id_periodocurso", "desc");
        $periodoCursos = $periodoCursos->paginate($per_page)
            ->through(fn ($periodoCurso) => [
                "id_periodocurso" => $periodoCurso->id_periodocurso,
                "correo" => $periodoCurso->correo,
                "periodo_nombre" => $periodoCurso->periodo_nombre,
                "docente_nombre" => $periodoCurso->docente_nombre,
                "curso_nombre" => $periodoCurso->curso_nombre,
                "aula_nombre" => $periodoCurso->aula_nombre ?? "(No definido)",
                "horario_dias" => $periodoCurso->horario_dias ?? "(No definido)",
                "horario_horas" => $periodoCurso->horario_horas ?? "(No definido)",
                "mensajeriagrupo_nombre" => ($mensajeriaGrupo = MensajeriaGrupo::where("id_periodocurso", $periodoCurso->id_periodocurso)->first()) ?
                    $mensajeriaGrupo->nombre : "(No definido)",
            ]);

        return response()->json($periodoCursos);
    }

    public function showPeriodoCurso($id_periodocurso)
    {
        $result = DB::table("periodo_curso as a")
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
                DB::raw("b.nombre_completo as docente_nombre"),
                DB::raw("b.correo as docente_correo"),
                DB::raw("b.id_archivo_foto as docente_id_archivo_foto"),
                DB::raw("c.nombre as curso_nombre"),
                DB::raw("c.codigo as curso_codigo"),
                DB::raw("c.id_archivo as curso_id_archivo"),
                DB::raw(
                    "(select group_concat(distinct x.nombre order by x.nombre asc separator ', ') from periodo_horario z inner join aula x on
                    z.id_aula = x.id_aula and z.id_periodocurso = a.id_periodocurso) as aula_nombre"
                ),
                DB::raw(
                    "(select group_concat(x.nombre order by x.orden asc separator ', ') from periodo_horario z inner join dia x on
                    z.id_dia = x.id_dia and z.id_periodocurso = a.id_periodocurso) as horario_dias"
                ),
                DB::raw(
                    "(select group_concat(concat(z.hora_inicio, ' a ', z.hora_fin) order by x.orden asc separator ', ') from periodo_horario z inner join dia x on
                    z.id_dia = x.id_dia and z.id_periodocurso = a.id_periodocurso) as horario_horas"
                ),
                DB::raw("e.nombre as ciclo_nombre"),
                DB::raw("f.nombre as periodo_nombre"),
                DB::raw("g.nombre as carrera_nombre"),
                DB::raw("i.nombre as planestudio_nombre"),
                DB::raw("year(i.fecha_inicio) as planestudio_anho_ff")
            )
            ->where("a.id_periodocurso", $id_periodocurso)
            ->first();

        return response()->json($result);
    }

    public function listarCursosPorDocente($idUsuario)
    {
        $cursos = CursoDocentes::with([
            'curso:id_curso,nombre',
            'cursoHorario:idCursoDocente,aula,dia,hora_ini,hora_fin'
        ])
            ->where('idUsuario', $idUsuario)
            ->get()
            ->map(function ($cursoDocente) {
                return [
                    'idCurso' => $cursoDocente->curso->id_curso,
                    'nombreCurso' => $cursoDocente->curso->nombre,
                    'horarios' => $cursoDocente->cursoHorario->map(function ($horario) {
                        return [
                            'aula' => $horario->aula,
                            'dia' => $horario->dia,
                            'hora_ini' => $horario->hora_ini,
                            'hora_fin' => $horario->hora_fin
                        ];
                    })
                ];
            });

        return response()->json([
            'cursos' => $cursos
        ]);
    }
    // pie: listarCursosPorDocente

    public function indexPeriodoModulo(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;
        $result = DB::table('periodo_modulo as a')
            ->join('periodo_curso as b', 'a.id_periodocurso', 'b.id_periodocurso')
            ->select(
                "a.id_periodomodulo",
                "a.titulo",
                "a.descripcion"
            )
            ->where("b.id_docente", $user->id_usuario)
            ->where("b.id_empresa", $user->id_empresa);

        if (isset($request->id_periodocurso)) {
            $result->where("a.id_periodocurso", $request->id_periodocurso);
        }
        $result->orderBy("id_periodomodulo", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }

    public function storePeriodoModulo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocurso" => "required",
            "titulo" => "required|max:100",
            "descripcion" => "required|max:255",
            "fecha_inicio" => "required",
            "fecha_fin" => "required",
        ], [
            "id_periodocurso.required" => "El curso es requerido",
            "titulo.required" => "El título es requerido",
            'titulo.max' => 'El título tiene un máximo 100 caracteres',
            "descripcion.required" => "La descripción es requerida",
            'descripcion.max' => 'La descripción tiene un máximo 255 caracteres',
            "fecha_inicio.required" => "La fecha inicio es requerida",
            "fecha_fin.required" => "La fecha fin es requerida",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodoModulo = [];
        $periodoModulo["id_empresa"] = $user->id_empresa;
        $periodoModulo["id_usuarioreg"] = $user->id_usuario;
        $periodoModulo["fechareg"] = now();
        $periodoModulo["id_periodocurso"] = $request->id_periodocurso;
        $periodoModulo["titulo"] = $request->titulo;
        $periodoModulo["descripcion"] = $request->descripcion;
        $periodoModulo["fecha_inicio"] = $request->fecha_inicio;
        $periodoModulo["fecha_fin"] = $request->fecha_fin;
        $periodoModulo["orden"] = 1;
        $periodoModulo = PeriodoModulo::create($periodoModulo);

        $periodoModulo = PeriodoModulo::find($periodoModulo->id_periodomodulo);

        return response()->json($periodoModulo);
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
            ->select(
                "a.id_periodotema",
                "a.titulo",
                "a.descripcion",
                "a.fecha",
                "a.id_tipocategoria",
                DB::raw("a.fecha as dia_nombre")
            )
            ->where("c.id_docente", $user->id_usuario)
            ->where("c.id_empresa", $user->id_empresa);

        if (isset($request->id_periodomodulo)) {
            $result->where("a.id_periodomodulo", $request->id_periodomodulo);
        }
        $result->orderBy("fecha", "asc");
        $result->orderBy("id_periodotema", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }

    public function storePeriodoTema(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodomodulo" => "required",
            "titulo" => "required|max:255",
            "descripcion" => "required|max:1000",
            "fecha" => "required",
            "id_tipocategoria" => "required",
        ], [
            "id_periodomodulo.required" => "El módulo es requerido",
            "titulo.required" => "El título es requerido",
            "titulo.max" => "El título tiene un máximo 255 caracteres",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 1000 caracteres",
            "fecha.required" => "La fecha es requerida",
            "id_tipocategoria.required" => "La categoría es requerida",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodoTema = [];
        $periodoTema["id_empresa"] = $user->id_empresa;
        $periodoTema["id_periodomodulo"] = $request->id_periodomodulo;
        $periodoTema["titulo"] = $request->titulo;
        $periodoTema["descripcion"] = $request->descripcion;
        $periodoTema["fecha"] = $request->fecha;
        $periodoTema["id_usuarioreg"] = $user->id_usuario;
        $periodoTema["fechareg"] = now();
        $periodoTema["id_tipocategoria"] = $request->id_tipocategoria;
        $periodoTema = PeriodoTema::create($periodoTema);

        $periodoTema = PeriodoTema::find($periodoTema->id_periodotema);

        return response()->json($periodoTema);
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

    public function showPeriodoTarea($id_periodotarea)
    {
        $periodoTarea = DB::table("periodo_tarea as a")
            ->join("periodo_tema as b", "a.id_periodotema", "b.id_periodotema")
            ->select(
                "a.id_periodotarea",
                "a.id_periodotema",
                "a.titulo",
                "a.instruccion",
                "a.fecha_entrega",
                "a.hora_entrega",
                "a.numero_intentos",
                "a.calificacion_maxima",
                "a.fecha_mostrar_desde",
                "a.fecha_mostrar_hasta",
                DB::raw("b.fecha as tema_fecha")
            )
            ->where("a.id_periodotarea", $id_periodotarea)
            ->first();

        return response()->json($periodoTarea);
    }

    public function storePeriodoTarea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodotema" => "required",
            "titulo" => "required|max:100",
            "instruccion" => "required|max:255",
        ], [
            "id_periodotema.required" => "El tema es requerido",
            "titulo.required" => "El título es requerido",
            "titulo.max" => "El título tiene un máximo 100 caracteres",
            "instruccion.required" => "La instrucción es requerida",
            "instruccion.max" => "La instrucción tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodoTarea = [];
        $periodoTarea["id_empresa"] = $user->id_empresa;
        $periodoTarea["id_periodotema"] = $request->id_periodotema;
        $periodoTarea["titulo"] = $request->titulo;
        $periodoTarea["instruccion"] = $request->instruccion;
        $periodoTarea["id_usuarioreg"] = $user->id_usuario;
        $periodoTarea["fechareg"] = now();
        $periodoTarea = PeriodoTarea::create($periodoTarea);

        $periodoTarea = PeriodoTarea::find($periodoTarea->id_periodotarea);

        return response()->json($periodoTarea);
    }

    public function updatePeriodoTarea(Request $request, $id_periodotarea)
    {
        $validator = Validator::make($request->all(), [
            "id_periodotema" => "required",
            "titulo" => "required|max:100",
            "instruccion" => "required|max:255",
        ], [
            "id_periodotema.required" => "El tema es requerido",
            "titulo.required" => "El título es requerido",
            "titulo.max" => "El título tiene un máximo 100 caracteres",
            "instruccion.required" => "La instrucción es requerida",
            "instruccion.max" => "La instrucción tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $periodoTarea = PeriodoTarea::find($id_periodotarea);

        if(!$periodoTarea) {
            return response()->json("¡Atención! La tarea no existe.", 400);
        }

        $periodoTareaEdit = [];
        $periodoTareaEdit["id_periodotema"] = $request->id_periodotema;
        $periodoTareaEdit["titulo"] = $request->titulo;
        $periodoTareaEdit["instruccion"] = $request->instruccion;
        $periodoTarea->update($periodoTareaEdit);

        $periodoTarea = PeriodoTarea::find($id_periodotarea);

        return response()->json($periodoTarea);
    }

    public function patchPeriodoTarea(Request $request, $id_periodotarea)
    {
        $validator = Validator::make($request->all(), [
            "fecha_entrega" => "required|date",
            "hora_entrega" => "required",
            "numero_intentos" => "required|integer",
            "calificacion_maxima" => "required|integer",
            "fecha_mostrar_desde" => "required",
            "fecha_mostrar_hasta" => "required",
        ], [
            "fecha_entrega.required" => "La fecha entrega es requerida",
            "fecha_entrega.date" => "La fecha entrega no tiene el formato",
            "hora_entrega.required" => "La hora entrega es requerida",
            "numero_intentos.required" => "La número de intentos es requerida",
            "calificacion_maxima.required" => "La calificación maxima es requerida",
            "fecha_mostrar_desde.required" => "La fecha desde es requerida",
            "fecha_mostrar_desde.date" => "La fecha desde no tiene el formato",
            "fecha_mostrar_hasta.required" => "La fecha hasta es requerida",
            "fecha_mostrar_hasta.date" => "La fecha hasta no tiene el formato",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $periodoTarea = PeriodoTarea::find($id_periodotarea);

        if(!$periodoTarea) {
            return response()->json("¡Atención! La tarea no existe.", 400);
        }

        $periodoTareaEdit = [];
        $periodoTareaEdit["fecha_entrega"] = $request->fecha_entrega;
        $periodoTareaEdit["hora_entrega"] = $request->hora_entrega;
        $periodoTareaEdit["numero_intentos"] = $request->numero_intentos;
        $periodoTareaEdit["calificacion_maxima"] = $request->calificacion_maxima;
        $periodoTareaEdit["fecha_mostrar_desde"] = $request->fecha_mostrar_desde;
        $periodoTareaEdit["fecha_mostrar_hasta"] = $request->fecha_mostrar_hasta;
        $periodoTarea->update($periodoTareaEdit);

        $periodoTarea = PeriodoTarea::find($id_periodotarea);

        return response()->json($periodoTarea);
    }

    public function indexPeriodoVideo(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $periodoTareas = DB::table("periodo_video")
            ->select(
                "id_periodovideo",
                "id_periodotema",
                "nombre",
                "url",
                "descripcion",
                "tipo",
                "tiene_contenido"
            )
            ->where("id_periodotema", $request->id_periodotema);
        if(isset($request->tipo)) {
            $periodoTareas->where("tipo", $request->tipo);
        }
        $periodoTareas->orderBy("id_periodovideo", "asc");
        $periodoTareas = $periodoTareas->paginate($per_page);

        return response()->json($periodoTareas);
    }

    public function storePeriodoVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodotema" => "required",
            "nombre" => "required|max:100",
            "url" => "nullable|max:800",
            "descripcion" => "required|max:255",
            "tipo" => "required|max:1",
            "tiene_contenido" => "required|max:1",
            "file" => "nullable|mimes:jpeg,bmp,png|size:16000", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "id_periodotema.required" => "El tema es requerido",
            "nombre.required" => "El nombre es requerido",
            "nombre.max" => "El nombre tiene un máximo 100 caracteres",
            "url.required" => "La url es requerida",
            "url.max" => "La url tiene un máximo 800 caracteres",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 255 caracteres",
            "tipo.required" => "El tipo es requerido",
            "tipo.max" => "El tipo tiene un máximo 1 caracteres",
            "tiene_contenido.required" => "Tiene contenido es requerido",
            "tiene_contenido.max" => "Tiene contenido tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        if ($request->tipo == "2" && $request->tiene_contenido == "1" && !$request->hasFile("file")) {
            return response()->json("¡Atención! Archivo no encontrado.", 400);
        }

        $user = $request->sessionUser;

        $periodoVideo = [];
        $periodoVideo["id_empresa"] = $user->id_empresa;
        $periodoVideo["id_periodotema"] = $request->id_periodotema;
        $periodoVideo["nombre"] = $request->nombre;
        $periodoVideo["url"] = $request->url;
        $periodoVideo["descripcion"] = $request->descripcion;
        $periodoVideo["tipo"] = $request->tipo;
        $periodoVideo["tiene_contenido"] = $request->tiene_contenido;
        $periodoVideo["id_usuarioreg"] = $user->id_usuario;
        $periodoVideo["fechareg"] = now();
        $periodoVideo = PeriodoVideo::create($periodoVideo);

        if ($request->tipo == "2" && $request->tiene_contenido == "1") {
            $archivo = ArchivoController::registrarVideo($request->file, $user->id_usuario, $request->tipo, null, $request->id_periodotema);
            $periodoVideo->update(["id_archivo" => $archivo->id_archivo]);
        }

        $periodoVideo = PeriodoVideo::find($periodoVideo->id_periodovideo);

        return response()->json($periodoVideo);
    }

    public function updatePeriodoVideo(Request $request, $id_periodovideo)
    {
        $validator = Validator::make($request->all(), [
            "id_periodotema" => "required",
            "nombre" => "required|max:100",
            "url" => "nullable|max:800",
            "descripcion" => "required|max:255",
            "tipo" => "required|max:1",
            "tiene_contenido" => "required|max:1",
            "file" => "nullable|mimes:jpeg,bmp,png|size:16000", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "id_periodotema.required" => "El tema es requerido",
            "nombre.required" => "El nombre es requerido",
            "nombre.max" => "El nombre tiene un máximo 100 caracteres",
            "url.required" => "La url es requerida",
            "url.max" => "La url tiene un máximo 800 caracteres",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 255 caracteres",
            "tipo.required" => "El tipo es requerido",
            "tipo.max" => "El tipo tiene un máximo 1 caracteres",
            "tiene_contenido.required" => "Tiene contenido es requerido",
            "tiene_contenido.max" => "Tiene contenido tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        if ($request->tipo == "2" && $request->tiene_contenido == "1" && !$request->hasFile("file")) {
            return response()->json("¡Atención! Archivo no encontrado.", 400);
        }

        $periodoVideo = PeriodoVideo::find($id_periodovideo);
        if (!$periodoVideo) {
            return response()->json("¡Atención! Video no encontrado.", 400);
        }

        $periodoVideoEdit = [];
        $periodoVideoEdit["id_periodotema"] = $request->id_periodotema;
        $periodoVideoEdit["nombre"] = $request->nombre;
        $periodoVideoEdit["url"] = $request->url;
        $periodoVideoEdit["descripcion"] = $request->descripcion;
        $periodoVideoEdit["tipo"] = $request->tipo;
        $periodoVideoEdit["tiene_contenido"] = $request->tiene_contenido;
        $periodoVideo->update($periodoVideoEdit);

        // if ($request->tipo == "2" && $request->tiene_contenido == "1") {
        //     $archivo = ArchivoController::registrarVideo($request->file, $user->id_usuario, $request->tipo, null, $request->id_periodotema);
        //     $periodoVideo->update(["id_archivo" => $archivo->id_archivo]);
        // }

        $periodoVideo = PeriodoVideo::find($periodoVideo->id_periodovideo);

        return response()->json($periodoVideo);
    }

    public function indexPeriodoCuestionario(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $periodoCuestionarios = DB::table("periodo_cuestionario")
            ->select(
                "id_periodocuestionario",
                "id_periodotema",
                "titulo",
                "instruccion"
            )
            ->where("id_periodotema", $request->id_periodotema);

        $periodoCuestionarios->orderBy("id_periodocuestionario", "asc");
        $periodoCuestionarios = $periodoCuestionarios->paginate($per_page);

        return response()->json($periodoCuestionarios);
    }

    public function showPeriodoCuestionario($id_periodocuestionario)
    {
        $periodoCuestionario = DB::table("periodo_cuestionario")
            ->select(
                "id_periodocuestionario",
                "id_periodotema",
                "titulo",
                "instruccion"
            )
            ->where("id_periodocuestionario", $id_periodocuestionario)
            ->first();

        return response()->json($periodoCuestionario);
    }

    public function storePeriodoCuestionario(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodotema" => "required",
            "titulo" => "required|max:100",
            "instruccion" => "required|max:255",
        ], [
            "id_periodotema.required" => "El tema es requerido",
            "titulo.required" => "El título es requerido",
            "titulo.max" => "El título tiene un máximo 100 caracteres",
            "instruccion.required" => "La instrucción es requerida",
            "instruccion.max" => "La instrucción tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodoCuestionario = [];
        $periodoCuestionario["id_empresa"] = $user->id_empresa;
        $periodoCuestionario["id_periodotema"] = $request->id_periodotema;
        $periodoCuestionario["titulo"] = $request->titulo;
        $periodoCuestionario["instruccion"] = $request->instruccion;
        $periodoCuestionario["id_usuarioreg"] = $user->id_usuario;
        $periodoCuestionario["fechareg"] = now();
        $periodoCuestionario = PeriodoCuestionario::create($periodoCuestionario);

        $periodoCuestionario = PeriodoCuestionario::find($periodoCuestionario->id_periodocuestionario);

        return response()->json($periodoCuestionario);
    }

    public function updatePeriodoCuestionario(Request $request, $id_periodocuestionario)
    {
        $validator = Validator::make($request->all(), [
            "id_periodotema" => "required",
            "titulo" => "required|max:100",
            "instruccion" => "required|max:255",
        ], [
            "id_periodotema.required" => "El tema es requerido",
            "titulo.required" => "El título es requerido",
            "titulo.max" => "El título tiene un máximo 100 caracteres",
            "instruccion.required" => "La instrucción es requerida",
            "instruccion.max" => "La instrucción tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $periodoCuestionario = PeriodoCuestionario::find($id_periodocuestionario);

        if(!$periodoCuestionario) {
            return response()->json("¡Atención! El cuestionario no existe.", 400);
        }

        $periodoCuestionarioEdit = [];
        $periodoCuestionarioEdit["id_periodotema"] = $request->id_periodotema;
        $periodoCuestionarioEdit["titulo"] = $request->titulo;
        $periodoCuestionarioEdit["instruccion"] = $request->instruccion;
        $periodoCuestionario->update($periodoCuestionarioEdit);

        $periodoCuestionario = PeriodoCuestionario::find($id_periodocuestionario);

        return response()->json($periodoCuestionario);
    }

    public function indexPeriodoPregunta(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $periodoPreguntas = DB::table("periodo_pregunta as a")
            ->join("tipo_pregunta as b", "a.id_tipopregunta", "b.id_tipopregunta")
            ->select(
                "a.id_periodopregunta",
                "a.id_periodocuestionario",
                "a.descripcion",
                "a.orden",
                "a.es_requerida",
                "a.id_tipopregunta",
                DB::raw("b.codigo as tipopregunta_codigo")
            )
            ->where("a.id_periodocuestionario", $request->id_periodocuestionario);

        $periodoPreguntas->orderBy("orden", "asc");
        $periodoPreguntas->orderBy("id_periodopregunta", "asc");
        $periodoPreguntas = $periodoPreguntas->paginate($per_page);

        return response()->json($periodoPreguntas);
    }

    public function storePeriodoPregunta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocuestionario" => "required",
            "descripcion" => "nullable|max:255",
            // "orden" => "required",
            "es_requerida" => "required|max:1",
            "id_tipopregunta" => "required",
        ], [
            "id_periodocuestionario.required" => "El cuestionario es requerido",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 255 caracteres",
            "es_requerida.required" => "Requerida es requerida",
            "es_requerida.max" => "Requerida tiene un máximo 1 caracteres",
            "id_tipopregunta.required" => "El tipo pregunta es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;
        $ordenUltimo = PeriodoPregunta::where("id_periodocuestionario", $request->id_periodocuestionario)
            ->max("orden");
        $ordenSiguiente = $ordenUltimo + 1;

        $periodoPregunta = [];
        $periodoPregunta["id_periodocuestionario"] = $request->id_periodocuestionario;
        $periodoPregunta["descripcion"] = $request->descripcion;
        $periodoPregunta["orden"] = $ordenSiguiente;
        $periodoPregunta["es_requerida"] = $request->es_requerida;
        $periodoPregunta["id_tipopregunta"] = $request->id_tipopregunta;
        $periodoPregunta["id_usuarioreg"] = $user->id_usuario;
        $periodoPregunta["fechareg"] = now();
        $periodoPregunta = PeriodoPregunta::create($periodoPregunta);

        $ordenUltimo = PeriodoRespuesta::where("id_periodopregunta", $periodoPregunta->id_periodopregunta)
            ->max("orden");
        $ordenSiguiente = $ordenUltimo + 1;
        $periodoRespuesta = [];
        $periodoRespuesta["id_periodopregunta"] = $periodoPregunta->id_periodopregunta;
        $periodoRespuesta["descripcion"] = "";
        $periodoRespuesta["orden"] = $ordenSiguiente;
        $periodoRespuesta["es_valida"] = "1";
        $periodoRespuesta["id_usuarioreg"] = $user->id_usuario;
        $periodoRespuesta["fechareg"] = now();
        $periodoRespuesta = PeriodoRespuesta::create($periodoRespuesta);

        $periodoPregunta = PeriodoPregunta::find($periodoPregunta->id_periodopregunta);

        return response()->json($periodoPregunta);
    }

    public function updatePeriodoPregunta(Request $request, $id_periodopregunta)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocuestionario" => "required",
            "descripcion" => "required|max:255",
            // "orden" => "required",
            "es_requerida" => "required|max:1",
            "id_tipopregunta" => "required",
        ], [
            "id_periodocuestionario.required" => "El cuestionario es requerido",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 255 caracteres",
            "es_requerida.required" => "Requerida es requerida",
            "es_requerida.max" => "Requerida tiene un máximo 1 caracteres",
            "id_tipopregunta.required" => "El tipo pregunta es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $periodoPregunta = PeriodoPregunta::find($id_periodopregunta);

        if(!$periodoPregunta) {
            return response()->json("¡Atención! La pregunta no existe.", 400);
        }

        if($request->id_tipopregunta != $periodoPregunta->id_tipopregunta) {
            $respuestas = DB::table("periodo_respuesta as a")
                ->select(
                    "a.id_periodopregunta",
                    DB::raw("count(a.id_periodopregunta) as cantidad"),
                    DB::raw("min(a.id_periodorespuesta) as id_periodorespuesta")
                )
                ->where("a.id_periodopregunta", $periodoPregunta->id_periodopregunta)
                ->groupBy("a.id_periodopregunta")
                ->get();

            if(count($respuestas) > 0 ) {
                if($respuestas[0]->cantidad > 1 ) {
                    PeriodoRespuesta::where("id_periodopregunta", $periodoPregunta->id_periodopregunta)
                        ->where("id_periodorespuesta", ">", $respuestas[0]->id_periodorespuesta)
                        ->delete();
                }
            }
        }

        $periodoPreguntaEdit = [];
        $periodoPreguntaEdit["id_periodocuestionario"] = $request->id_periodocuestionario;
        $periodoPreguntaEdit["descripcion"] = $request->descripcion;
        $periodoPreguntaEdit["es_requerida"] = $request->es_requerida;
        $periodoPreguntaEdit["id_tipopregunta"] = $request->id_tipopregunta;
        $periodoPregunta->update($periodoPreguntaEdit);

        $periodoPregunta = PeriodoPregunta::find($id_periodopregunta);

        return response()->json($periodoPregunta);
    }

    public function destroyPeriodoPregunta($id_periodopregunta)
    {
        $periodoPregunta = PeriodoPregunta::find($id_periodopregunta);
        if(!$periodoPregunta) {
            return response()->json("Pregunta no encontrado.", 400);
        }

        PeriodoRespuesta::where("id_periodopregunta", $id_periodopregunta)->delete();
        $periodoPregunta->delete();

        return response()->json([]);
    }

    public function indexPeriodoRespuesta(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $periodoRespuestas = DB::table("periodo_respuesta as a")
            ->join("periodo_pregunta as b", "a.id_periodopregunta", "b.id_periodopregunta")
            ->join("tipo_pregunta as c", "b.id_tipopregunta", "c.id_tipopregunta")
            ->select(
                "a.id_periodorespuesta",
                "a.id_periodopregunta",
                "a.descripcion",
                "a.orden",
                "a.es_valida",
                DB::raw("c.codigo as pregunta_tipocodigo")
            )
            ->where("a.id_periodopregunta", $request->id_periodopregunta);

        $periodoRespuestas->orderBy("orden", "asc");
        $periodoRespuestas->orderBy("id_periodorespuesta", "asc");
        $periodoRespuestas = $periodoRespuestas->paginate($per_page);

        return response()->json($periodoRespuestas);
    }

    public function storePeriodoRespuesta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodopregunta" => "required",
            "descripcion" => "nullable|max:255",
            // "orden" => "required",
            "es_valida" => "required|max:1",
        ], [
            "id_periodopregunta.required" => "El cuestionario es requerido",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 255 caracteres",
            "es_valida.required" => "Valida es requerida",
            "es_valida.max" => "Valida tiene un máximo 1 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $ordenUltimo = PeriodoRespuesta::where("id_periodopregunta", $request->id_periodopregunta)
            ->max("orden");
        $ordenSiguiente = $ordenUltimo + 1;

        $periodoRespuesta = [];
        $periodoRespuesta["id_periodopregunta"] = $request->id_periodopregunta;
        $periodoRespuesta["descripcion"] = $request->descripcion;
        $periodoRespuesta["orden"] = $ordenSiguiente;
        $periodoRespuesta["es_valida"] = $request->es_valida;
        $periodoRespuesta = PeriodoRespuesta::create($periodoRespuesta);

        $periodoRespuesta = PeriodoRespuesta::find($periodoRespuesta->id_periodorespuesta);

        return response()->json($periodoRespuesta);
    }

    public function updatePeriodoRespuesta(Request $request, $id_periodorespuesta)
    {
        $validator = Validator::make($request->all(), [
            "id_periodopregunta" => "required",
            "descripcion" => "required|max:255",
            // "orden" => "required",
            "es_valida" => "required|max:1",
        ], [
            "id_periodopregunta.required" => "El cuestionario es requerido",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 255 caracteres",
            "es_valida.required" => "Valida es requerida",
            "es_valida.max" => "Valida tiene un máximo 1 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $periodoRespuesta = PeriodoRespuesta::find($id_periodorespuesta);

        if(!$periodoRespuesta) {
            return response()->json("¡Atención! La respuesta no existe.", 400);
        }

        $periodoRespuestaEdit = [];
        $periodoRespuestaEdit["id_periodopregunta"] = $request->id_periodopregunta;
        $periodoRespuestaEdit["descripcion"] = $request->descripcion;
        $periodoRespuestaEdit["es_valida"] = $request->es_valida;
        $periodoRespuesta->update($periodoRespuestaEdit);

        $periodoRespuesta = PeriodoRespuesta::find($id_periodorespuesta);

        return response()->json($periodoRespuesta);
    }

    public function indexPeriodoHorario(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("periodo as a")
            ->join("periodo_ciclo as b", "a.id_periodo", "b.id_periodo")
            ->join("periodo_curso as c", "b.id_periodociclo", "c.id_periodociclo")
            ->join("curso as z", "c.id_curso", "z.id_curso")
            ->join("periodo_horario as d", "c.id_periodocurso", "d.id_periodocurso")
            ->select(
                "d.id_periodohorario",
                "d.id_dia",
                "d.hora_inicio",
                "d.hora_fin",
                DB::raw("z.nombre as curso_nombre")
            )
            ->where("c.id_docente", $user->id_usuario)
            ->where("c.id_empresa", $user->id_empresa)
            ->where("a.id_periodo", $request->id_periodo)
            ->orderBy("id_periodohorario", "asc")
            ->paginate($per_page);

        return response()->json($paginate);
    }
}