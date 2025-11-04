<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicoController extends Controller
{
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
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
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

    public function indexPeriodo(Request $request)
    {
        $user = $request->sessionUser;
        $result = DB::table("periodo as a")
            ->select(
                "a.id_periodo",
                "a.nombre"
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->orderBy("id_periodo", "desc");
        $result = $result->get();

        return response()->json($result);
    }

    public function indexPeriodoCiclo(Request $request)
    {
        $user = $request->sessionUser;
        $result = DB::table("periodo_ciclo as a")
            ->join("ciclo as b", "a.id_ciclo", "b.id_ciclo")
            ->select(
                "a.id_periodociclo",
                DB::raw("b.nombre as ciclo_nombre")
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(b.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        if(isset($request->id_periodo)) {
            $result->where("a.id_periodo", $request->id_periodo);
        }
        if(isset($request->id_carrera)) {
            $result->where("a.id_carrera", $request->id_carrera);
        }

        $result->orderBy("nombre", "asc");
        $result->orderBy("id_periodociclo", "asc");
        $result = $result->get();

        return response()->json($result);
    }

    public function indexPeriodoCurso(Request $request)
    {
        $user = $request->sessionUser;
        $result = DB::table("periodo_curso as a")
            ->join("persona as b", "a.id_docente", "b.id_persona")
            ->join("curso as c", "a.id_curso", "c.id_curso")
            ->join("tipo_modalidadestudio as d", "a.id_tipomodalidadestudio", "d.id_tipomodalidadestudio")
            ->select(
                "a.id_periodocurso",
                "b.correo",
                DB::raw("b.nombre_completo as docente_nombre"),
                DB::raw("c.nombre as curso_nombre"),
                DB::raw("(select max(x.nombre) from periodo_horario z inner join aula x on
                    z.id_aula = x.id_aula and z.id_periodocurso = a.id_periodocurso) as aula_nombre"
                ),
                DB::raw("(select max(concat(x.nombre, ': ', z.hora_inicio, ' a ', z.hora_fin)) from periodo_horario z inner join dia x on
                    z.id_dia = x.id_dia and z.id_periodocurso = a.id_periodocurso) as horario_descripcion"
                )
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(b.nombre_completo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->orderBy("id_periodocurso", "desc");
        $result = $result->get();

        return response()->json($result);
    }

    public function carrerasActivas(Request $request)
    {
        $user = $request->sessionUser;
        $result = DB::table("periodo as a")
            ->join("periodo_ciclo as b", "a.id_periodo", "b.id_periodo")
            ->join("carrera as c", "b.id_carrera", "c.id_carrera")
            ->select(
                "b.id_periodociclo",
                DB::raw("c.nombre as carrera_nombre"),
            )
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.estado", "1");

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(c.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $result->groupBy("b.id_periodociclo", "c.nombre");
        $result->orderBy("carrera_nombre", "asc");
        $result = $result->get();

        return response()->json($result);
    }

    public function indexPlanEstudio(Request $request)
    {
        $user = $request->sessionUser;
        $result = DB::table('plan_estudio_curso as a')
            ->join('plan_estudio_ciclo as b', 'a.id_planestudiociclo', 'b.id_planestudiociclo')
            ->join('plan_estudio as c', 'b.id_planestudio', 'c.id_planestudio')
            ->join('carrera as d', 'c.id_carrera', 'd.id_carrera')
            ->join('ciclo as e', 'b.id_ciclo', 'e.id_ciclo')
            ->join('curso as f', 'a.id_curso', 'f.id_curso')
            ->select(
                'a.id_planestudiocurso',
                'a.id_planestudio',
                'a.id_planestudiociclo',
                'a.id_curso',
                'a.creditos',
                'a.horas_semanal',
                'a.tipo',
                DB::raw("f.nombre as curso_nombre"),
                DB::raw("d.nombre as carrera_nombre"),
                DB::raw("e.nombre as ciclo_nombre"),
                DB::raw("case
                    when a.tipo = 'O' then 'Obligatorio'
                    when a.tipo = 'E' then 'Electivo'
                    end as tipo_descripcion"
                )
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(f.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        if(isset($request->id_carrera)) {
            $result->where("c.id_carrera", $request->id_carrera);
        }
        if(isset($request->id_ciclo)) {
            $result->where("b.id_ciclo", $request->id_ciclo);
        }

        $result->orderBy("carrera_nombre", "desc");
        $result->orderBy("ciclo_nombre", "desc");
        $result->orderBy("curso_nombre", "desc");
        $result = $result->get();

        return response()->json($result);
    }
}