<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Periodo;
use App\Models\PeriodoCiclo;
use App\Models\PeriodoCurso;
use App\Models\PeriodoCursoPrecio;
use App\Models\Matricula;
use App\Models\PeriodoModulo;
use App\Models\PeriodoHorario;
use App\Models\PlanEstudioCurso;

class AcademicoController extends Controller
{
    /**
     * Normaliza un texto de búsqueda: quita acentos, reemplaza espacios por %.
     */
    private function normalizeSearch(string $text): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($normalized === false) {
            $normalized = $text;
        }
        return str_replace(' ', '%', $normalized);
    }

    public function indexCarrera(Request $request)
    {
        $user = $request->sessionUser;
        $result = DB::table("carrera as a")
            ->join("periodo_ciclo as b", "a.id_carrera", "b.id_carrera")
            ->select(
                "a.id_carrera",
                "a.nombre"
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = $this->normalizeSearch($request->text_search);
            $result->whereRaw("upper(concat(a.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        if(isset($request->id_periodo)) {
            $result->where("b.id_periodo", $request->id_periodo);
        }

        $result->groupBy("a.id_carrera", "a.nombre");
        $result->orderBy("nombre", "asc");
        $result->orderBy("id_carrera", "asc");
        $result = $result->get();

        return response()->json($result);
    }

    public function carreraEstadisticas(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        // FIX: Se eliminó la concatenación directa (SQL injection) y se usan where() seguros
        $paginate = DB::table("carrera as c")
            ->leftJoin("periodo_ciclo as b", function ($join) use ($user, $request) {
                $join->on("c.id_carrera", "=", "b.id_carrera");
                if (isset($request->id_periodo)) {
                    $join->where("b.id_periodo", "=", $request->id_periodo);
                }
                $join->where("b.id_empresa", "=", $user->id_empresa);
            })
            ->select(
                "c.id_carrera",
                DB::raw("c.nombre as carrera_nombre"),
                DB::raw("sum(case when b.id_periodociclo is not null then 1 else 0 end) as total_ciclos")
            );

        if(isset($request->text_search)) {
            $texto = $this->normalizeSearch($request->text_search);
            $paginate->whereRaw("upper(concat(c.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate = $paginate->groupBy("c.id_carrera", "c.nombre")
            ->orderBy("carrera_nombre", "asc")
            ->orderBy("id_carrera", "asc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function indexPeriodo(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("periodo")
            ->select(
                "id_periodo",
                "nombre",
                "descripcion",
                "fecha_ini",
                "fecha_fin",
                "estado",
                // FIX: Mapeo consistente con el frontend
                DB::raw("case when estado = '1' then 'Activo' else 'Inactivo' end as estado_descripcion")
            )
            ->where("id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = $this->normalizeSearch($request->text_search);
            $paginate->whereRaw("upper(concat(nombre, descripcion)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("id_periodo", "desc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function showPeriodo(Request $request, $id_periodo)
    {
        // FIX: Filtrar por id_empresa para evitar IDOR
        $user = $request->sessionUser;

        $result = DB::table("periodo")
            ->select(
                "id_periodo",
                "nombre",
                "descripcion",
                "fecha_ini",
                "fecha_fin",
                "estado",
                "esta_abierto"
            )
            ->where("id_periodo", $id_periodo)
            ->where("id_empresa", $user->id_empresa)
            ->first();

        if (!$result) {
            return response()->json("¡Atención! Período no encontrado.", 400);
        }

        return response()->json($result);
    }

    public function storePeriodo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|string|max:255",
            "fecha_ini" => "required|date",
            "fecha_fin" => "required|date|after_or_equal:fecha_ini",
            "descripcion" => "required",
        ], [
            "nombre.required" => "El nombre del periodo es requerido",
            "fecha_ini.required" => "La fecha de inicio es requerida",
            "fecha_ini.date" => "La fecha de inicio debe ser una fecha válida",
            "fecha_fin.required" => "La fecha de fin es requerida",
            "fecha_fin.date" => "La fecha de fin debe ser una fecha válida",
            "fecha_fin.after_or_equal" => "La fecha de fin debe ser igual o posterior a la de inicio",
            "descripcion.required" => "La descripción es requerida",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodo = [];
        $periodo["nombre"] = $request->nombre;
        $periodo["descripcion"] = $request->descripcion;
        $periodo["fecha_ini"] = $request->fecha_ini;
        $periodo["fecha_fin"] = $request->fecha_fin;
        $periodo["fechareg"] = now();
        $periodo["id_usuarioreg"] = $user->id_usuario;
        $periodo["id_empresa"] = $user->id_empresa;
        $periodo["estado"] = "0";
        $periodo = Periodo::create($periodo);

        $result = Periodo::find($periodo->id_periodo);

        return response()->json($result);
    }

    public function updatePeriodo(Request $request, $id_periodo)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
            "descripcion" => "required",
        ], [
            'nombre.required' => 'El nombre del período es requerido',
            'fecha_ini.required' => 'La fecha de inicio es requerida',
            'fecha_ini.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_fin.required' => 'La fecha de fin es requerida',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio',
            "descripcion.required" => "La descripción es requerida",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        // FIX: Filtrar por id_empresa para evitar IDOR
        $user = $request->sessionUser;
        $periodo = Periodo::where("id_periodo", $id_periodo)
            ->where("id_empresa", $user->id_empresa)
            ->first();

        if(!$periodo) {
            return response()->json("¡Atención! El periodo no existe.", 400);
        }

        $periodoEdit = [];
        $periodoEdit["nombre"] = $request->nombre;
        $periodoEdit["descripcion"] = $request->descripcion;
        $periodoEdit["fecha_ini"] = $request->fecha_ini;
        $periodoEdit["fecha_fin"] = $request->fecha_fin;
        $periodo->update($periodoEdit);

        $result = Periodo::find($id_periodo);

        return response()->json($result);
    }

    public function destroyPeriodo(Request $request, $id_periodo)
    {
        // FIX: Filtrar por id_empresa para evitar IDOR
        $user = $request->sessionUser;
        $periodo = Periodo::where("id_periodo", $id_periodo)
            ->where("id_empresa", $user->id_empresa)
            ->first();

        if(!$periodo) {
            return response()->json("¡Atención! Periodo no encontrado.", 400);
        }

        $periodoCiclo = PeriodoCiclo::where("id_periodo", $id_periodo)->first();
        if($periodoCiclo) {
            return response()->json("¡Atención! Ciclo depende de este periodo.", 400);
        }

        $matricula = Matricula::where("id_periodo", $id_periodo)->first();
        if($matricula) {
            return response()->json("¡Atención! Matrícula depende de este periodo.", 400);
        }

        $periodo->delete();

        return response()->json([]);
    }

    public function abrir(Request $request, $id_periodo)
    {
        $user = $request->sessionUser;

        $periodo = Periodo::where("id_empresa", $user->id_empresa)
            ->where("id_periodo", $id_periodo)
            ->first();
        if(!$periodo) {
            return response()->json('¡Atención! Período no encontrado.', 400);
        } else if($periodo->estado == "1") {
            return response()->json('¡Atención! Período Registrado.', 400);
        } else if($periodo->esta_abierto == "1") {
            return response()->json('¡Atención! Período esta abierto.', 400);
        }

        $periodoEdit = [];
        $periodoEdit["estado"] = "1";
        $periodoEdit["esta_abierto"] = "1";
        $periodo->update($periodoEdit);
        $periodo = Periodo::find($id_periodo);

        return response()->json($periodo);
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
            ->join("periodo as b", "a.id_periodo", "b.id_periodo")
            ->join("carrera as c", "a.id_carrera", "c.id_carrera")
            ->join("ciclo as d", "a.id_ciclo", "d.id_ciclo")
            ->select(
                "a.id_periodociclo",
                "a.id_periodo",
                "a.descripcion",
                "a.codigo",
                "a.id_carrera",
                "a.id_tituloacademico",
                "a.id_tipotituloacademico",
                "a.estado",
                "a.id_planestudiociclo",
                "d.orden",
                DB::raw("b.nombre as periodo_nombre"),
                DB::raw("c.nombre as carrera_nombre"),
                DB::raw("a.descripcion"),
                DB::raw("d.nombre as ciclo_nombre")
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->id_periodo)) {
            $paginate->where("b.id_periodo", $request->id_periodo);
        }
        if(isset($request->id_carrera)) {
            $paginate->where("a.id_carrera", $request->id_carrera);
        }
        if(isset($request->text_search)) {
            $texto = $this->normalizeSearch($request->text_search);
            $paginate->whereRaw("upper(concat(a.codigo, a.descripcion)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate = $paginate->orderBy("id_periodo", "desc")
            ->orderBy("carrera_nombre", "asc")
            ->orderBy("orden", "asc")
            ->orderBy("id_periodociclo", "desc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function showPeriodoCiclo($id_periodociclo)
    {
        $result = DB::table("periodo_ciclo as a")
            ->join("plan_estudio_ciclo as b", "a.id_planestudiociclo", "b.id_planestudiociclo")
            ->select(
                "a.id_periodociclo",
                "a.id_periodo",
                "a.descripcion",
                "a.codigo",
                "a.id_carrera",
                "a.id_tituloacademico",
                "a.id_tipotituloacademico",
                "a.estado",
                "a.id_ciclo",
                "a.id_planestudiociclo",
                "b.id_planestudio"
            )
            ->where("a.id_periodociclo", $id_periodociclo)
            ->first();

        return response()->json($result);
    }

    public function storePeriodoCiclo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodo" => "required",
            "descripcion" => "nullable|string|max:255",
            "codigo" => "required|string|max:50",
            "id_carrera" => "required",
            "id_tituloacademico" => "required",
            "id_tipotituloacademico" => "required",
            "estado" => "required",
            "id_ciclo" => "required",
            "id_planestudiociclo" => "required",
        ], [
            "id_periodo.required" => "El periodo es requerido",
            "codigo.required" => "El código es requerido",
            "id_carrera.required" => "La carrera es requerida",
            "id_tituloacademico.required" => "El título académico es requerido",
            "id_tipotituloacademico.required" => "El título académico (especificado) es requerido",
            "estado.required" => "El estado es requerido",
            "id_ciclo.required" => "El ciclo es requerido",
            "id_planestudiociclo.required" => "El ciclo de plan es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodoCicloExiste = PeriodoCiclo::where("id_periodo", $request->id_periodo)
            ->where("id_carrera", $request->id_carrera)
            ->where("id_planestudiociclo", $request->id_planestudiociclo)
            ->where("id_empresa", $user->id_empresa)
            ->first();
        if($periodoCicloExiste) {
            return response()->json("¡Atención! El ciclo ya esta registrado.", 400);
        }

        $periodoCiclo = [];
        $periodoCiclo["id_periodo"] = $request->id_periodo;
        $periodoCiclo["descripcion"] = $request->descripcion;
        $periodoCiclo["codigo"] = $request->codigo;
        $periodoCiclo["fechareg"] = now();
        $periodoCiclo["id_carrera"] = $request->id_carrera;
        $periodoCiclo["id_usuarioreg"] = $user->id_usuario;
        $periodoCiclo["id_empresa"] = $user->id_empresa;
        $periodoCiclo["id_tituloacademico"] = $request->id_tituloacademico;
        $periodoCiclo["id_tipotituloacademico"] = $request->id_tipotituloacademico;
        $periodoCiclo["estado"] = $request->estado;
        $periodoCiclo["id_ciclo"] = $request->id_ciclo;
        $periodoCiclo["id_planestudiociclo"] = $request->id_planestudiociclo;
        $periodoCiclo = PeriodoCiclo::create($periodoCiclo);

        $result = PeriodoCiclo::find($periodoCiclo->id_periodociclo);

        return response()->json($result);
    }

    public function updatePeriodoCiclo(Request $request, $id_periodociclo)
    {
        $validator = Validator::make($request->all(), [
            "id_periodo" => "required",
            "descripcion" => "nullable|string|max:255",
            "codigo" => "required|string|max:50",
            "id_carrera" => "required",
            "id_tituloacademico" => "required",
            "id_tipotituloacademico" => "required",
            "estado" => "required",
            "id_ciclo" => "required",
            "id_planestudiociclo" => "required",
        ], [
            "id_periodo.required" => "El periodo es requerido",
            "codigo.required" => "El código es requerido",
            "id_carrera.required" => "La carrera es requerida",
            "id_tituloacademico.required" => "El título académico es requerido",
            "id_tipotituloacademico.required" => "El título académico (especificado) es requerido",
            "estado.required" => "El estado es requerido",
            "id_ciclo.required" => "El ciclo es requerido",
            "id_planestudiociclo.required" => "El ciclo de plan es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        // FIX: Filtrar por id_empresa
        $periodoCiclo = PeriodoCiclo::where("id_periodociclo", $id_periodociclo)
            ->where("id_empresa", $user->id_empresa)
            ->first();

        if(!$periodoCiclo) {
            return response()->json("¡Atención! El periodo y ciclo no existe.", 400);
        }

        $periodoCicloExiste = PeriodoCiclo::where("id_periodo", $request->id_periodo)
            ->where("id_carrera", $request->id_carrera)
            ->where("id_planestudiociclo", $request->id_planestudiociclo)
            ->where("id_empresa", $user->id_empresa)
            ->where("id_periodociclo", "!=", $id_periodociclo)
            ->first();
        if($periodoCicloExiste) {
            return response()->json("¡Atención! El ciclo ya esta registrado.", 400);
        }

        $periodoCicloEdit = [];
        $periodoCicloEdit["id_periodo"] = $request->id_periodo;
        $periodoCicloEdit["descripcion"] = $request->descripcion;
        $periodoCicloEdit["codigo"] = $request->codigo;
        $periodoCicloEdit["id_carrera"] = $request->id_carrera;
        $periodoCicloEdit["id_tituloacademico"] = $request->id_tituloacademico;
        $periodoCicloEdit["id_tipotituloacademico"] = $request->id_tipotituloacademico;
        $periodoCicloEdit["estado"] = $request->estado;
        $periodoCicloEdit["id_ciclo"] = $request->id_ciclo;
        $periodoCicloEdit["id_planestudiociclo"] = $request->id_planestudiociclo;
        $periodoCiclo->update($periodoCicloEdit);

        $result = PeriodoCiclo::find($id_periodociclo);

        return response()->json($result);
    }

    public function destroyPeriodoCiclo(Request $request, $id_periodociclo)
    {
        // FIX: Filtrar por id_empresa para evitar IDOR
        $user = $request->sessionUser;
        $periodoCiclo = PeriodoCiclo::where("id_periodociclo", $id_periodociclo)
            ->where("id_empresa", $user->id_empresa)
            ->first();

        if(!$periodoCiclo) {
            return response()->json("¡Atención! Periodo y ciclo no encontrado.", 400);
        }

        $periodoCurso = PeriodoCurso::where("id_periodociclo", $periodoCiclo->id_periodociclo)->first();
        if($periodoCurso) {
            return response()->json("¡Atención! Existen cursos asociados.", 400);
        }

        $periodoCiclo->delete();

        return response()->json([]);
    }

    public function indexPeriodoCurso(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("periodo_curso as a")
            ->leftJoin("persona as b", "a.id_docente", "b.id_persona")
            ->join("curso as c", "a.id_curso", "c.id_curso")
            ->join("tipo_modalidadestudio as d", "a.id_tipomodalidadestudio", "d.id_tipomodalidadestudio")
            ->join("periodo_ciclo as e", "a.id_periodociclo", "e.id_periodociclo")
            ->join("periodo as f", "e.id_periodo", "f.id_periodo")
            ->join("ciclo as g", "e.id_ciclo", "g.id_ciclo")
            ->join("carrera as h", "e.id_carrera", "h.id_carrera")
            ->select(
                'a.id_periodocurso',
                'a.vacantes',
                'a.id_periodo',
                'a.id_periodociclo',
                'a.id_tipomodalidadestudio',
                'a.id_docente',
                'a.fecha_inicio',
                'a.fecha_fin',
                'a.hora_inicio',
                'a.hora_fin',
                'a.id_seccion',
                'a.estado',
                'a.id_tipocategoria',
                'a.es_sincrono',
                'a.detalle',
                'a.creditos',
                'a.horas_semanal',
                'c.url_img',
                DB::raw("d.nombre as modalidadestudio_nombre"),
                DB::raw("b.nombre_completo as docente_nombre"),
                DB::raw("c.nombre as curso_nombre"),
                DB::raw("c.codigo as curso_codigo"),
                DB::raw("date_format(a.fecha_inicio, '%Y-%m-%d') as fecha_inicio_date"),
                DB::raw("date_format(a.fecha_fin, '%Y-%m-%d') as fecha_fin_date"),
                DB::raw("g.nombre as ciclo_nombre"),
                DB::raw("f.nombre as periodo_nombre"),
                DB::raw("h.nombre as carrera_nombre")
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->id_periodociclo)) {
            $paginate->where("a.id_periodociclo", $request->id_periodociclo);
        }
        if(isset($request->text_search)) {
            $texto = $this->normalizeSearch($request->text_search);
            $paginate->whereRaw("upper(concat(b.nombre_completo, c.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate = $paginate->orderBy("curso_nombre", "asc")
            ->orderBy("id_periodocurso", "desc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function showPeriodoCurso($id_periodocurso)
    {
        $result = DB::table("periodo_curso as a")
            ->join("curso as b", "b.id_curso", "a.id_curso")
            ->join("periodo_ciclo as c", "a.id_periodociclo", "c.id_periodociclo")
            ->select(
                "a.id_periodocurso",
                "a.id_empresa",
                "a.id_periodo",
                "a.id_periodociclo",
                "a.id_usuarioreg",
                "a.fechareg",
                "a.estado",
                "a.id_curso",
                "a.id_docente",
                "a.fecha_inicio",
                "a.fecha_fin",
                "a.hora_inicio",
                "a.hora_fin",
                "a.id_seccion",
                "a.vacantes",
                "a.id_tipomodalidadestudio",
                "a.id_tipocategoria",
                "a.es_sincrono",
                "a.detalle",
                "a.url_zoom",
                "a.id_planestudiocurso",
                "b.url_img",
                "b.nombre",
                "c.id_carrera",
                "c.id_planestudiociclo"
            )
            ->where("id_periodocurso", $id_periodocurso)
            ->first();

        return response()->json($result);
    }

    public function storePeriodoCurso(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_docente" => "nullable",
            "id_tipomodalidadestudio" => "required",
            "vacantes" => "required",
            "id_periodo" => "required",
            "id_seccion" => "required",
            "estado" => "required",
            "fecha_inicio" => "required",
            "fecha_fin" => "required",
            "hora_inicio" => "required",
            "hora_fin" => "required",
            "id_tipocategoria" => "required",
            "id_periodociclo" => "required",
            "es_sincrono" => "required",
            "detalle" => "nullable|max:1000",
            "url_zoom" => "nullable|max:255",
            "id_planestudiocurso" => "required",
            "id_curso" => "required",
        ], [
            "id_tipomodalidadestudio.required" => "La modalidad de estudio es requerido",
            "vacantes.required" => "La vacante es requerido",
            "id_periodo.required" => "El periodo es requerido",
            "id_seccion.required" => "La seccion es requerida",
            "estado.required" => "El estado es requerido",
            "fecha_inicio.required" => "La fecha inicio es requerida",
            "fecha_fin.required" => "La fecha fin es requerida",
            "hora_inicio.required" => "La hora inicio es requerida",
            "hora_fin.required" => "La hora fin es requerida",
            "id_tipocategoria.required" => "La categoria es requerida",
            "id_periodociclo.required" => "El ciclo es requerido",
            "es_sincrono.required" => "El sincrono es requerido",
            "url_zoom.max" => "La URL de zoom tiene un máximo 255 caracteres",
            "id_planestudiocurso.required" => "El plan de curso es requerido",
            "id_curso.required" => "El curso es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodoCursoExiste = PeriodoCurso::where("id_empresa", $user->id_empresa)
            ->where("id_periodociclo", $request->id_periodociclo)
            ->where("id_planestudiocurso", $request->id_planestudiocurso)
            ->first();
        if($periodoCursoExiste) {
            return response()->json("¡Atención! Curso ya esta registrado.", 400);
        }

        $planEstudioCurso = PlanEstudioCurso::find($request->id_planestudiocurso);
        if(!$planEstudioCurso) {
            return response()->json("¡Atención! Plan curso no encontrado.", 400);
        }

        $periodoCurso = [];
        $periodoCurso["id_empresa"] = $user->id_empresa;
        $periodoCurso["id_periodo"] = $request->id_periodo;
        $periodoCurso["id_usuarioreg"] = $user->id_usuario;
        $periodoCurso["fechareg"] = now();
        $periodoCurso["estado"] = $request->estado;
        $periodoCurso["id_curso"] = $request->id_curso;
        $periodoCurso["id_planestudiocurso"] = $request->id_planestudiocurso;
        $periodoCurso["id_docente"] = $request->id_docente;
        $periodoCurso["vacantes"] = $request->vacantes;
        $periodoCurso["id_tipomodalidadestudio"] = $request->id_tipomodalidadestudio;
        $periodoCurso["id_seccion"] = $request->id_seccion;
        $periodoCurso["fecha_inicio"] = $request->fecha_inicio;
        $periodoCurso["fecha_fin"] = $request->fecha_fin;
        $periodoCurso["hora_inicio"] = $request->hora_inicio;
        $periodoCurso["hora_fin"] = $request->hora_fin;
        $periodoCurso["id_tipocategoria"] = $request->id_tipocategoria;
        $periodoCurso["id_periodociclo"] = $request->id_periodociclo;
        $periodoCurso["es_sincrono"] = $request->es_sincrono;
        $periodoCurso["detalle"] = $request->detalle;
        $periodoCurso["url_zoom"] = $request->url_zoom;
        $periodoCurso["creditos"] = $planEstudioCurso->creditos;
        $periodoCurso["horas_semanal"] = $planEstudioCurso->horas_semanal;
        $periodoCurso = PeriodoCurso::create($periodoCurso);

        $result = PeriodoCurso::find($periodoCurso->id_periodocurso);

        return response()->json($result);
    }

    public function updatePeriodoCurso(Request $request, $id_periodocurso)
    {
        $validator = Validator::make($request->all(), [
            "id_docente" => "nullable",
            "id_tipomodalidadestudio" => "required",
            "vacantes" => "required",
            "id_periodo" => "required",
            "id_seccion" => "required",
            "estado" => "required",
            "fecha_inicio" => "required",
            "fecha_fin" => "required",
            "hora_inicio" => "required",
            "hora_fin" => "required",
            "id_tipocategoria" => "required",
            "id_periodociclo" => "required",
            "es_sincrono" => "required",
            "detalle" => "nullable|max:1000",
            "url_zoom" => "nullable|max:255",
            "id_planestudiocurso" => "required",
            "id_curso" => "required",
        ], [
            "id_tipomodalidadestudio.required" => "La modalidad de estudio es requerido",
            "vacantes.required" => "La vacante es requerido",
            "id_periodo.required" => "El periodo es requerido",
            "id_seccion.required" => "La seccion es requerida",
            "estado.required" => "El estado es requerido",
            "fecha_inicio.required" => "La fecha inicio es requerida",
            "fecha_fin.required" => "La fecha fin es requerida",
            "hora_inicio.required" => "La hora inicio es requerida",
            "hora_fin.required" => "La hora fin es requerida",
            "id_tipocategoria.required" => "La categoria es requerida",
            "id_periodociclo.required" => "El ciclo es requerido",
            "es_sincrono.required" => "El sincrono es requerido",
            "url_zoom.max" => "La URL de zoom tiene un máximo 255 caracteres",
            "id_planestudiocurso.required" => "El plan de curso es requerido",
            "id_curso.required" => "El curso es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        // FIX: Filtrar por id_empresa
        $periodoCurso = PeriodoCurso::where("id_periodocurso", $id_periodocurso)
            ->where("id_empresa", $user->id_empresa)
            ->first();
        if(!$periodoCurso) {
            return response()->json("El periodo y curso no existe.", 400);
        }

        $periodoCursoExiste = PeriodoCurso::where("id_empresa", $user->id_empresa)
            ->where("id_periodociclo", $request->id_periodociclo)
            ->where("id_planestudiocurso", $request->id_planestudiocurso)
            ->where("id_periodocurso", "!=", $id_periodocurso)
            ->first();
        if($periodoCursoExiste) {
            return response()->json("¡Atención! Curso ya esta registrado.", 400);
        }

        $planEstudioCurso = PlanEstudioCurso::find($request->id_planestudiocurso);
        if(!$planEstudioCurso) {
            return response()->json("¡Atención! Plan curso no encontrado.", 400);
        }

        $periodoCursoEdit = [];
        $periodoCursoEdit["id_periodo"] = $request->id_periodo;
        $periodoCursoEdit["id_curso"] = $request->id_curso;
        $periodoCursoEdit["id_planestudiocurso"] = $request->id_planestudiocurso;
        $periodoCursoEdit["id_docente"] = $request->id_docente;
        $periodoCursoEdit["vacantes"] = $request->vacantes;
        $periodoCursoEdit["id_tipomodalidadestudio"] = $request->id_tipomodalidadestudio;
        $periodoCursoEdit["id_seccion"] = $request->id_seccion;
        $periodoCursoEdit["fecha_inicio"] = $request->fecha_inicio;
        $periodoCursoEdit["fecha_fin"] = $request->fecha_fin;
        $periodoCursoEdit["hora_inicio"] = $request->hora_inicio;
        $periodoCursoEdit["hora_fin"] = $request->hora_fin;
        $periodoCursoEdit["id_tipocategoria"] = $request->id_tipocategoria;
        $periodoCursoEdit["id_periodociclo"] = $request->id_periodociclo;
        $periodoCursoEdit["es_sincrono"] = $request->es_sincrono;
        $periodoCursoEdit["estado"] = $request->estado;
        $periodoCursoEdit["detalle"] = $request->detalle;
        $periodoCursoEdit["url_zoom"] = $request->url_zoom;
        $periodoCursoEdit["creditos"] = $planEstudioCurso->creditos;
        $periodoCursoEdit["horas_semanal"] = $planEstudioCurso->horas_semanal;
        $periodoCurso->update($periodoCursoEdit);

        $result = PeriodoCurso::find($id_periodocurso);

        return response()->json($result);
    }

    public function destroyPeriodoCurso(Request $request, $id_periodocurso)
    {
        // FIX: Filtrar por id_empresa para evitar IDOR
        $user = $request->sessionUser;
        $periodoCurso = PeriodoCurso::where("id_periodocurso", $id_periodocurso)
            ->where("id_empresa", $user->id_empresa)
            ->first();

        if(!$periodoCurso) {
            return response()->json("¡Atención! Periodo y curso no encontrado.", 400);
        }

        $periodoModulo = PeriodoModulo::where("id_periodocurso", $id_periodocurso)->first();
        if($periodoModulo) {
            return response()->json("¡Atención! Existen modulos asociados.", 400);
        }

        $periodoCurso->delete();

        return response()->json([]);
    }

    public function indexPeriodoCursoPrecio(Request $request)
    {
        $user = $request->sessionUser;
        $result = DB::table('periodo_curso_precio as a')
            ->select(
                'a.id_periodocursoprecio',
                'a.id_periodocurso',
                'a.tipo',
                'a.importe',
                DB::raw("case when a.tipo = '1' then 'Anual' when a.tipo = '2' then 'Mensual' else '-' end as tipo_descripcion"),
            )
            ->where("a.id_empresa", $user->id_empresa);
        if(isset($request->id_periodocurso)) {
            $result->where("a.id_periodocurso", $request->id_periodocurso);
        }
        $result->orderBy("id_periodocursoprecio", "desc");
        $result = $result->get();

        return response()->json($result);
    }

    public function storePeriodoCursoPrecio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_periodocurso' => 'required',
            'importe' => 'required',
            'tipo' => 'required',
        ], [
            'id_periodocurso.required' => 'El curso es requerido',
            'importe.required' => 'El importe es requerido',
            'tipo.required' => 'El tipo de estudio es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodoCursoPrecio = [];
        $periodoCursoPrecio["id_empresa"] = $user->id_empresa;
        $periodoCursoPrecio["id_periodocurso"] = $request->id_periodocurso;
        $periodoCursoPrecio["importe"] = $request->importe;
        $periodoCursoPrecio["estado"] = "1";
        $periodoCursoPrecio["id_usuarioreg"] = $user->id_usuario;
        $periodoCursoPrecio["fechareg"] = now();
        $periodoCursoPrecio["tipo"] = $request->tipo;
        $periodoCursoPrecio = PeriodoCursoPrecio::create($periodoCursoPrecio);

        $result = PeriodoCursoPrecio::find($periodoCursoPrecio->id_periodocursoprecio);

        return response()->json($result);
    }

    public function destroyPeriodoCursoPrecio(Request $request, $id_periodocursoprecio)
    {
        // FIX: Filtrar por id_empresa para evitar IDOR
        $user = $request->sessionUser;
        $periodoCursoPrecio = PeriodoCursoPrecio::where("id_periodocursoprecio", $id_periodocursoprecio)
            ->where("id_empresa", $user->id_empresa)
            ->first();

        if(!$periodoCursoPrecio) {
            return response()->json("Precio no encontrado.", 400);
        }

        $periodoCursoPrecio->delete();

        return response()->json([]);
    }

    public function indexPeriodoHorario(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;
        $fechaActual = date("Y-m-d");
        // FIX: "monday this week" funciona correctamente incluso si hoy es lunes
        $lunes = date("Y-m-d", strtotime("monday this week", strtotime($fechaActual)));

        $paginate = DB::table("periodo as a")
            ->join("periodo_ciclo as b", "a.id_periodo", "b.id_periodo")
            ->join("periodo_curso as c", "b.id_periodociclo", "c.id_periodociclo")
            ->join("curso as z", "c.id_curso", "z.id_curso")
            ->join("periodo_horario as d", "c.id_periodocurso", "d.id_periodocurso")
            ->join("dia as e", "d.id_dia", "e.id_dia")
            ->select(
                "d.id_periodohorario",
                "d.id_dia",
                "d.hora_inicio",
                "d.hora_fin",
                DB::raw("e.orden as dia_orden"),
                DB::raw("z.nombre as curso_nombre")
            )
            ->where("c.id_empresa", $user->id_empresa);

        if(isset($request->id_periodo)) {
            $paginate->where("a.id_periodo", $request->id_periodo);
        }
        if(isset($request->id_periodocurso)) {
            $paginate->where("c.id_periodocurso", $request->id_periodocurso);
        }

        $paginate = $paginate->orderBy("id_periodohorario", "asc")
            ->paginate($per_page)
            ->through(fn ($horario) => [
                "id_periodohorario" => $horario->id_periodohorario,
                "id_dia" => $horario->id_dia,
                "hora_inicio" => $horario->hora_inicio,
                "hora_fin" => $horario->hora_fin,
                "dia_orden" => $horario->dia_orden,
                "curso_nombre" => $horario->curso_nombre,
                "lunes" => $lunes,
                "fecha_inicio_ff" => $horario->dia_orden == 1 ?
                    $lunes."T".$horario->hora_inicio :
                    date("Y-m-d", strtotime($lunes. " + ".($horario->dia_orden-1)." days"))."T".$horario->hora_inicio,
                "fecha_fin_ff" => $horario->dia_orden == 1 ?
                    $lunes."T".$horario->hora_fin :
                    date("Y-m-d", strtotime($lunes. " + ".($horario->dia_orden-1)." days"))."T".$horario->hora_fin,
            ]);

        return response()->json($paginate);
    }

    public function showPeriodoHorario(Request $request, $id_periodohorario)
    {
        $user = $request->sessionUser;

        $horario = DB::table("periodo as a")
            ->join("periodo_ciclo as b", "a.id_periodo", "b.id_periodo")
            ->join("periodo_curso as c", "b.id_periodociclo", "c.id_periodociclo")
            ->join("curso as z", "c.id_curso", "z.id_curso")
            ->join("periodo_horario as d", "c.id_periodocurso", "d.id_periodocurso")
            ->join("dia as e", "d.id_dia", "e.id_dia")
            ->select(
                "d.id_periodohorario",
                "d.id_dia",
                "d.id_aula",
                "d.hora_inicio",
                "d.hora_fin",
                DB::raw("e.orden as dia_orden"),
                DB::raw("z.nombre as curso_nombre")
            )
            ->where("c.id_empresa", $user->id_empresa)
            ->where("d.id_periodohorario", $id_periodohorario)
            ->first();

        return response()->json($horario);
    }

    public function storePeriodoHorario(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocurso" => "required",
            "id_dia" => "required",
            "id_aula" => "required",
            "hora_inicio" => "required",
            "hora_fin" => "required",
        ], [
            "id_periodocurso.required" => "El curso es requerido",
            "id_dia.required" => "El día es requerido",
            "id_aula.required" => "El aula es requerido",
            "hora_inicio.required" => "La hora de inicio es requerida",
            "hora_fin.required" => "La hora fin es requerida",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        // FIX: Verificar que el curso pertenece a la empresa
        $periodoCurso = PeriodoCurso::where("id_periodocurso", $request->id_periodocurso)
            ->where("id_empresa", $user->id_empresa)
            ->first();
        if(!$periodoCurso) {
            return response()->json("¡Atención! Curso no encontrado.", 400);
        }

        $periodoHorario = [];
        $periodoHorario["id_empresa"] = $periodoCurso->id_empresa;
        $periodoHorario["id_periodo"] = $periodoCurso->id_periodo;
        $periodoHorario["id_periodocurso"] = $request->id_periodocurso;
        $periodoHorario["id_dia"] = $request->id_dia;
        $periodoHorario["id_aula"] = $request->id_aula;
        $periodoHorario["hora_inicio"] = $request->hora_inicio;
        $periodoHorario["hora_fin"] = $request->hora_fin;
        $periodoHorario["id_usuarioreg"] = $user->id_usuario;
        $periodoHorario["fechareg"] = now();
        $periodoHorario = PeriodoHorario::create($periodoHorario);

        return response()->json($periodoHorario);
    }

    public function updatePeriodoHorario(Request $request, $id_periodohorario)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocurso" => "required",
            "id_dia" => "required",
            "id_aula" => "required",
            "hora_inicio" => "required",
            "hora_fin" => "required",
        ], [
            "id_periodocurso.required" => "El curso es requerido",
            "id_dia.required" => "El día es requerido",
            "id_aula.required" => "El aula es requerido",
            "hora_inicio.required" => "La hora de inicio es requerida",
            "hora_fin.required" => "La hora fin es requerida",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        // FIX: Filtrar por id_empresa a través del periodoCurso
        $user = $request->sessionUser;
        $periodoHorario = DB::table("periodo_horario as ph")
            ->join("periodo_curso as pc", "ph.id_periodocurso", "pc.id_periodocurso")
            ->where("ph.id_periodohorario", $id_periodohorario)
            ->where("pc.id_empresa", $user->id_empresa)
            ->select("ph.id_periodohorario")
            ->first();

        if(!$periodoHorario) {
            return response()->json("¡Atención! Horario no encontrado.", 400);
        }

        $horario = PeriodoHorario::find($id_periodohorario);
        $periodoHorarioEdit = [];
        $periodoHorarioEdit["id_periodocurso"] = $request->id_periodocurso;
        $periodoHorarioEdit["id_dia"] = $request->id_dia;
        $periodoHorarioEdit["id_aula"] = $request->id_aula;
        $periodoHorarioEdit["hora_inicio"] = $request->hora_inicio;
        $periodoHorarioEdit["hora_fin"] = $request->hora_fin;
        $horario->update($periodoHorarioEdit);

        return response()->json($horario);
    }

    public function destroyPeriodoHorario(Request $request, $id_periodohorario)
    {
        // FIX: Filtrar por id_empresa a través del periodoCurso
        $user = $request->sessionUser;
        $periodoHorario = DB::table("periodo_horario as ph")
            ->join("periodo_curso as pc", "ph.id_periodocurso", "pc.id_periodocurso")
            ->where("ph.id_periodohorario", $id_periodohorario)
            ->where("pc.id_empresa", $user->id_empresa)
            ->select("ph.id_periodohorario")
            ->first();

        if(!$periodoHorario) {
            return response()->json("¡Atención! Horario no encontrado.", 400);
        }

        $horario = PeriodoHorario::find($id_periodohorario);
        $horario->delete();

        return response()->json([]);
    }

    public function resumenCarrera(Request $request)
    {
        $user = $request->sessionUser;

        // FIX: Validar que id_periodo e id_carrera estén presentes
        if (!isset($request->id_periodo) || !isset($request->id_carrera)) {
            return response()->json([
                "total_cursos" => 0,
                "total_creditos" => 0,
                "total_horas_semanal" => 0,
                "total_ciclos" => 0,
            ]);
        }

        $first = DB::table("periodo_ciclo as a")
            ->join("periodo_curso as b", "a.id_periodociclo", "b.id_periodociclo")
            ->select(
                "a.id_periodo",
                DB::raw("count(1) as total_cursos"),
                DB::raw("sum(b.creditos) as total_creditos"),
                DB::raw("sum(b.horas_semanal) as total_horas_semanal")
            )
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_periodo", $request->id_periodo)
            ->where("a.id_carrera", $request->id_carrera)
            ->groupBy("a.id_periodo")
            ->first();

        $resumen = [];
        $resumen["total_cursos"] = $first ? $first->total_cursos : 0;
        $resumen["total_creditos"] = $first ? $first->total_creditos : 0;
        $resumen["total_horas_semanal"] = $first ? $first->total_horas_semanal : 0;

        $total_ciclos = DB::table("periodo_ciclo as a")
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_periodo", $request->id_periodo)
            ->where("a.id_carrera", $request->id_carrera)
            ->count();

        $resumen["total_ciclos"] = $total_ciclos;

        return response()->json($resumen);
    }
}