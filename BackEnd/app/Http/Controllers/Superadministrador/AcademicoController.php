<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Periodo;
use App\Models\PeriodoCiclo;
use App\Models\PeriodoCurso;
use App\Models\Curso;

class AcademicoController extends Controller
{
    public function indexPeriodo(Request $request)
    {
        $result = DB::table('periodo')
            ->select(
                'id_periodo',
                'nombre',
                'descripcion',
                'fecha_ini',
                'fecha_fin'
            )
            ->where("id_empresa", 1);
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(nombre, descripcion)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        $result->orderBy("id_periodo", "desc");
        $result = $result->get();

        return response()->json($result);
    }

    public function showPeriodo($id_periodo)
    {
        $result = DB::table('periodo')
            ->select(
                'id_periodo',
                'nombre',
                'descripcion',
                'fecha_ini',
                'fecha_fin'
            )
            ->where('id_periodo', $id_periodo)
            ->first();

        return response()->json($result);
    }

    public function storePeriodo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'fecha_ini' => 'required',
            'fecha_fin' => 'required',
        ], [
            'nombre.required' => 'El nombre del perìodo es requerido',
            'fecha_ini.required' => 'La fecha de inicio es requerida',
            'fecha_fin.required' => 'La fecha de fin es requerida',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->sessionUser;

        $periodo = [];
        $periodo["nombre"] = $request->nombre;
        $periodo["descripcion"] = '';
        $periodo["fecha_ini"] = $request->fecha_ini;
        $periodo["fecha_fin"] = $request->fecha_fin;
        $periodo["fechareg"] = now();
        $periodo["id_usuarioreg"] = $user->id_usuario;
        $periodo["id_empresa"] = $user->id_empresa;
        $periodo["estado"] = "1";
        $periodo = Periodo::create($periodo);

        $result = Periodo::find($periodo->id_periodo);

        return response()->json($result);
    }

    public function updatePeriodo(Request $request, $id_periodo)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'fecha_ini' => 'required',
            'fecha_fin' => 'required',
        ], [
            'nombre.required' => 'El nombre del perìodo es requerido',
            'fecha_ini.required' => 'La fecha de inicio es requerida',
            'fecha_fin.required' => 'La fecha de fin es requerida',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $periodo = Periodo::find($id_periodo);

        if(!$periodo) {
            return response()->json("El periodo no existe.", 400);
        }

        $periodoEdit = [];
        $periodoEdit["nombre"] = $request->nombre;
        $periodoEdit["fecha_ini"] = $request->fecha_ini;
        $periodoEdit["fecha_fin"] = $request->fecha_fin;
        $periodo->update($periodoEdit);

        $result = Periodo::find($id_periodo);

        return response()->json($result);
    }

    public function destroyPeriodo($id_periodo)
    {
        $periodo = Periodo::find($id_periodo);
        if(!$periodo) {
            return response()->json("Periodo no encontrado.", 400);
        }

        $periodoCiclo = PeriodoCiclo::where("id_periodo", $id_periodo)->first();
        if($periodoCiclo) {
            return response()->json("Ciclo depende de este periodo.", 400);
        }

        $periodo->delete();

        return response()->json([]);
    }

    public function indexPeriodoCiclo(Request $request)
    {
        $result = DB::table('periodo_ciclo as a')
            ->join('periodo as b', 'a.id_periodo', 'b.id_periodo')
            ->select(
                'a.id_periodociclo',
                'a.id_periodo',
                'a.descripcion',
                'a.codigo',
                DB::raw("b.nombre as periodo_nombre")
            )
            ->where("a.id_empresa", 1);
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.codigo, a.descripcion)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        $result->orderBy("id_periodo", "desc");
        $result->orderBy("id_periodociclo", "desc");
        $result = $result->get();

        return response()->json($result);
    }

    public function showPeriodoCiclo($id_periodociclo)
    {
        $result = DB::table('periodo')
            ->select(
                'id_periodociclo',
                'id_periodo',
                'descripcion',
                'codigo'
            )
            ->where('id_periodociclo', $id_periodociclo)
            ->first();

        return response()->json($result);
    }

    public function storePeriodoCiclo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_periodo' => 'required',
            'descripcion' => 'required|string|max:255',
            'codigo' => 'required|string|max:50',
        ], [
            'id_periodo.required' => 'El periodo es requerido',
            'descripcion.required' => 'La descripciòn es requerida',
            'codigo.required' => 'El còdigo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->sessionUser;

        $periodoCiclo = [];
        $periodoCiclo["id_periodo"] = $request->id_periodo;
        $periodoCiclo["descripcion"] = $request->descripcion;
        $periodoCiclo["codigo"] = $request->codigo;
        $periodoCiclo["fechareg"] = now();
        $periodoCiclo["id_usuarioreg"] = $user->id_usuario;
        $periodoCiclo["id_empresa"] = 1;
        $periodoCiclo["estado"] = 1;
        $periodoCiclo = PeriodoCiclo::create($periodoCiclo);

        $result = PeriodoCiclo::find($periodoCiclo->id_periodociclo);

        return response()->json($result);
    }

    public function updatePeriodoCiclo(Request $request, $id_periodociclo)
    {
        $validator = Validator::make($request->all(), [
            'id_periodo' => 'required',
            'descripcion' => 'required|string|max:255',
            'codigo' => 'required|string|max:50',
        ], [
            'id_periodo.required' => 'El periodo es requerido',
            'descripcion.required' => 'La descripciòn es requerida',
            'codigo.required' => 'El còdigo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $periodoCiclo = PeriodoCiclo::find($id_periodociclo);

        if(!$periodoCiclo) {
            return response()->json("El periodo y ciclo no existe.", 400);
        }

        $periodoCicloEdit = [];
        $periodoCicloEdit["id_periodo"] = $request->id_periodo;
        $periodoCicloEdit["descripcion"] = $request->descripcion;
        $periodoCicloEdit["codigo"] = $request->codigo;
        $periodoCiclo->update($periodoCicloEdit);

        $result = PeriodoCiclo::find($id_periodociclo);

        return response()->json($result);
    }

    public function destroyPeriodoCiclo($id_periodociclo)
    {
        $periodoCiclo = PeriodoCiclo::find($id_periodociclo);
        if(!$periodoCiclo) {
            return response()->json("Periodo y ciclo no encontrado.", 400);
        }

        $periodoCiclo->delete();

        return response()->json([]);
    }

    public function indexPeriodoCurso(Request $request)
    {
        $result = DB::table('periodo_curso as a')
            ->join('persona as b', 'a.id_docente', 'b.id_persona')
            ->join('curso as c', 'a.id_curso', 'c.id_curso')
            ->join('tipo_modalidadestudio as d', 'a.id_tipomodalidadestudio', 'd.id_tipomodalidadestudio')
            ->select(
                'a.id_periodocurso',
                'a.vacantes',
                'a.id_periodo',
                'a.id_periodociclo',
                'a.id_tipomodalidadestudio',
                'a.id_docente',
                DB::raw("d.nombre as modalidadestudio_nombre"),
                DB::raw("b.nombre_completo as docente_nombre_completo"),
                DB::raw("c.nombre as curso_nombre")
            )
            ->where("a.id_empresa", 1);
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

    public function showPeriodoCurso($id_periodocurso)
    {
        $result = DB::table('periodo_curso')
            ->select(
                'id_periodocurso',
                'id_empresa',
                'id_periodo',
                'id_periodociclo',
                'id_usuarioreg',
                'fechareg',
                'estado',
                'id_curso',
                'id_docente',
                'vacantes',
                'id_tipomodalidadestudio'
            )
            ->where('id_periodocurso', $id_periodocurso)
            ->first();

        return response()->json($result);
    }

    public function storePeriodoCurso(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'id_docente' => 'required',
            'id_tipomodalidadestudio' => 'required',
            'vacantes' => 'required',
            'id_periodo' => 'required',
        ], [
            'nombre.required' => 'El nombre es requerido',
            'id_docente.required' => 'El docente es requerida',
            'id_tipomodalidadestudio.required' => 'La modalidad de estudio es requerido',
            'vacantes.required' => 'La vacante es requerido',
            'id_periodo.required' => 'El periodo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $curso = [];
        $curso["id_empresa"] = 1;
        $curso["codigo"] = "";
        $curso["nombre"] = $request->nombre;
        $curso = Curso::create($curso);

        $periodoCurso = [];
        $periodoCurso["id_empresa"] = 1;
        $periodoCurso["id_periodo"] = $request->id_periodo;
        $periodoCurso["id_usuarioreg"] = 1;
        $periodoCurso["fechareg"] = now();
        $periodoCurso["estado"] = '1';
        $periodoCurso["id_curso"] = $curso->id_curso;
        $periodoCurso["id_docente"] = $request->id_docente;
        $periodoCurso["vacantes"] = $request->vacantes;
        $periodoCurso["id_tipomodalidadestudio"] = $request->id_tipomodalidadestudio;
        $periodoCurso = PeriodoCurso::create($periodoCurso);

        $result = PeriodoCurso::find($periodoCurso->id_periodocurso);

        return response()->json($result);
    }

    public function updatePeriodoCurso(Request $request, $id_periodocurso)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'id_docente' => 'required',
            'id_tipomodalidadestudio' => 'required',
            'vacantes' => 'required',
            'id_periodo' => 'required',
        ], [
            'nombre.required' => 'El nombre es requerido',
            'id_docente.required' => 'El docente es requerida',
            'id_tipomodalidadestudio.required' => 'La modalidad de estudio es requerido',
            'vacantes.required' => 'La vacante es requerido',
            'id_periodo.required' => 'El periodo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $periodoCurso = PeriodoCurso::find($id_periodocurso);

        if(!$periodoCurso) {
            return response()->json("El periodo y curso no existe.", 400);
        }

        $curso = Curso::find($periodoCurso->id_curso);

        $cursoEdit = [];
        $cursoEdit["nombre"] = $request->nombre;
        $curso->update($cursoEdit);

        $periodoCursoEdit = [];
        $periodoCursoEdit["id_periodo"] = $request->id_periodo;
        $periodoCursoEdit["id_docente"] = $request->id_docente;
        $periodoCursoEdit["vacantes"] = $request->vacantes;
        $periodoCursoEdit["id_tipomodalidadestudio"] = $request->id_tipomodalidadestudio;
        $periodoCurso->update($periodoCursoEdit);

        $result = PeriodoCurso::find($id_periodocurso);

        return response()->json($result);
    }

    public function destroyPeriodoCurso($id_periodocurso)
    {
        $periodoCurso = PeriodoCurso::find($id_periodocurso);
        if(!$periodoCurso) {
            return response()->json("Periodo y curso no encontrado.", 400);
        }

        $periodoCurso->delete();

        return response()->json([]);
    }

    public function docentesActivos(Request $request)
    {
        $result = DB::table('periodo as a')
            ->join("periodo_curso as b", "a.id_periodo", "b.id_periodo")
            ->join("persona as c", "b.id_docente", "c.id_persona")
            ->select(
                'b.id_docente',
                DB::raw("c.nombre_completo as docente_nombre"),
                'c.numero_documento'
            )
            ->where("a.estado", "1")
            ->where("a.id_empresa", 1);
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(c.nombre_completo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        $result->groupBy("b.id_docente", "c.numero_documento", "c.nombre_completo");
        $result->orderBy("docente_nombre", "asc");
        $result = $result->get();

        return response()->json($result);
    }

    public function cursosActivos(Request $request)
    {
        $result = DB::table('periodo as a')
            ->join("periodo_curso as b", "a.id_periodo", "b.id_periodo")
            ->join("curso as c", "b.id_curso", "c.id_curso")
            ->select(
                'b.id_periodocurso',
                'b.id_curso',
                DB::raw("c.nombre as curso_nombre")
            )
            ->where("a.estado", "1")
            ->where("a.id_empresa", 1);
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(c.nombre)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        if(isset($request->id_docente)) {
            $result->where("b.id_docente", $request->id_docente);
        }
        $result->orderBy("curso_nombre", "asc");
        $result = $result->get();

        return response()->json($result);
    }
}