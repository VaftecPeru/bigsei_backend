<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Persona;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Matricula;
use App\Models\PeriodoCursoPrecio;
use App\Models\PeriodoCurso;
use App\Models\MatriculaCurso;
use App\Models\UsuarioRol;
use App\Http\Controllers\AuthController;

class MatriculaController extends Controller
{
    public function cursoLibres(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("periodo_curso as a")
            ->join("periodo as b", "a.id_periodo", "b.id_periodo")
            ->join("empresa as d", "a.id_empresa", "d.id_empresa")
            ->join("curso as e", "a.id_curso", "e.id_curso")
            ->join("tipo_categoria as f", "a.id_tipocategoria", "f.id_tipocategoria")
            ->leftJoin("resena as i", "a.id_periodocurso", "i.id_periodocurso")
            ->select(
                "a.id_periodocurso",
                "a.detalle",
                "e.url_img",
                "e.id_archivo",
                DB::raw("d.razon_social as empresa_razon_social"),
                DB::raw("e.nombre as curso_nombre"),
                DB::raw("round(
                    sum(case when i.id_resena is not null then i.rating else 0 end)
                    /
                    if(
                        sum(case when i.id_resena is not null then 1 else 0 end) = 0,
                        1,
                        sum(case when i.id_resena is not null then 1 else 0 end)
                    )
                , 1) as rating"),
                DB::raw("sum(case when i.id_resena is not null then 1 else 0 end) as reviews")
            )
            ->where("a.estado", "1")
            ->where("b.esta_abierto", "1")
            ->whereRaw("(a.es_sincrono = '0' || f.visible_web = '1')");

        if(isset($request->id_tipocategoria)) {
            $paginate->where("a.id_tipocategoria", $request->id_tipocategoria);
        }

        $paginate = $paginate->groupBy("a.id_periodocurso", "a.detalle", "e.url_img", "d.razon_social", "e.nombre", "e.id_archivo")
            ->orderBy("empresa_razon_social", "asc")
            ->orderBy("curso_nombre", "asc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function showCursoLibres($id_periodocurso)
    {
        $curso = DB::table("periodo_curso as a")
            ->join("periodo as b", "a.id_periodo", "b.id_periodo")
            ->join("empresa as d", "a.id_empresa", "d.id_empresa")
            ->join("curso as e", "a.id_curso", "e.id_curso")
            ->join("tipo_categoria as f", "a.id_tipocategoria", "f.id_tipocategoria")
            ->join("periodo_ciclo as z", "a.id_periodociclo", "z.id_periodociclo")
            ->join("tipo_titulo_academico as x", "z.id_tipotituloacademico", "x.id_tipotituloacademico")
            ->join("titulo_academico as y", "z.id_tituloacademico", "y.id_tituloacademico")
            ->join("carrera as w", "z.id_carrera", "w.id_carrera")
            ->leftJoin("persona as g", "a.id_docente", "g.id_persona")
            ->select(
                "a.id_periodocurso",
                "a.detalle",
                DB::raw("d.razon_social as empresa_razon_social"),
                DB::raw("e.nombre as curso_nombre"),
                DB::raw("concat((datediff(a.fecha_fin, a.fecha_inicio) div 30), ' meses medio-tiempo') as tiempo_de_duracion"),
                DB::raw("g.nombre_completo as docente_nombre"),
                DB::raw("x.nombre as tipotituloacademico_nombre"),
                DB::raw("y.nombre as tituloacademico_nombre"),
                DB::raw("w.nombre as carrera_nombre"),
                DB::raw("d.id_archivo as empresa_id_archivo"),
                DB::raw("e.id_archivo as curso_id_archivo")
            )
            ->where("a.id_periodocurso", $id_periodocurso)
            ->where("a.estado", "1")
            ->where("b.estado", "1")
            ->whereRaw("(a.es_sincrono = '0' || f.visible_web = '1')")
            ->first();

        if($curso) {
            $curso->precios = DB::table("periodo_curso_precio")
                ->select(
                    "id_periodocursoprecio",
                    "importe",
                    "tipo",
                    DB::raw("case when tipo = '1' then 'Anual' else 'Mensual' end as tipo_descripcion")
                )
                ->where("id_periodocurso", $id_periodocurso)
                ->where("estado", "1")
                ->get();
        }

        return response()->json($curso);
    }

    public function storeCursoLibre(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_periodocursoprecio' => 'required',
            'importe' => 'required|numeric|min:0|not_in:0',
            'id_tipodocumento' => 'required',
            'numero_documento' => 'required|string|max:20|min:8',
            'nombre_completo' => 'required|string|max:450',
            'telefono' => 'required|string|max:20|min:7',
            'direccion' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255',
            'numero_operacion' => 'required|max:50',
            'importe_operacion' => 'required|numeric|not_in:0|min:1',
        ], [
            'id_periodocursoprecio' => 'El precio es requerido',
            'importe' => 'El importe es requerido',
            'id_tipodocumento' => 'El tipo documento es requerido',
            'numero_documento' => 'El número documento es requerido',
            'nombre_completo' => 'El nombre es requerido',
            'telefono' => 'El teléfono es requerido',
            'direccion' => 'El dirección es requerido',
            'correo' => 'El correo es requerido',
            'numero_operacion' => 'El número operación es requerido',
            'importe_operacion' => 'El importe es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $periodoCursoPrecio = PeriodoCursoPrecio::find($request->id_periodocursoprecio);
        if (!$periodoCursoPrecio) {
            return response()->json('¡Atención! El precio del curso no existe.', 400);
        }
        $periodoCurso = PeriodoCurso::find($periodoCursoPrecio->id_periodocurso);

        $correoUsuario = Usuario::where("email", $request->correo)
            ->first();
        if ($correoUsuario) {
            return response()->json('¡Atención! El correo de usuario en esta registrado por otra persona.', 400);
        }

        $numeroDocumentoPersona = Persona::where("numero_documento", $request->numero_documento)
            ->where("id_tipodocumento", $request->id_tipodocumento)->first();
        if ($numeroDocumentoPersona) {
            return response()->json('¡Atención! El documento existe.', 400);
        }

        $persona = [];
        $persona["id_tipodocumento"] = $request->id_tipodocumento;
        $persona["numero_documento"] = $request->numero_documento;
        $persona["nombre"] = $request->nombre_completo;
        $persona["nombre_completo"] = $request->nombre_completo;
        // $persona["fecha_nacimiento"] = $request->fecha_nacimiento;
        $persona["telefono"] = $request->telefono;
        $persona["direccion"] = $request->direccion;
        $persona["correo"] = $request->correo;
        // $persona["sexo"] = $request->sexo;
        $persona["fechareg"] = now();
        $persona["estado"] = '1';
        $persona = Persona::create($persona);

        $estudiante = [];
        $estudiante["id_estudiante"] = $persona->id_persona;
        // $estudiante["codigo"] = 1;
        $estudiante["estado"] = "1";
        $estudiante["fechareg"] = now();
        $estudiante = Estudiante::create($estudiante);

        $usuario = [];
        $usuario["id_usuario"] = $persona->id_persona;
        $usuario["email"] = $request->correo;
        $usuario["username"] = $request->correo;
        $usuario["password"] = Hash::make("123");
        $usuario["estado"] = "1";
        $usuario["fechareg"] = now();
        $usuario = Usuario::create($usuario);

        $idRolStudent = 5;  // student
        $usuarioRol = [];
        $usuarioRol["id_empresa"] = $periodoCurso->id_empresa;
        $usuarioRol["id_usuario"] = $persona->id_persona;
        $usuarioRol["id_rol"] = $idRolStudent;  // student
        $usuarioRol["es_principal"] = "1";
        $usuarioRol = UsuarioRol::create($usuarioRol);

        $matricula = [];
        $matricula["id_empresa"] = $periodoCurso->id_empresa;
        $matricula["id_periodo"] = $periodoCurso->id_periodo;
        $matricula["id_estudiante"] = $persona->id_persona;
        $matricula["importe"] = $request->importe;
        $matricula["tipo"] = "1"; // sin membresia
        $matricula["estado"] = "1";
        $matricula["fechareg"] = now();
        // $matricula["id_usuarioreg"] = $user->id_usuario;
        $matricula = Matricula::create($matricula);

        $matriculaCurso = [];
        $matriculaCurso["id_empresa"] = $periodoCurso->id_empresa;
        $matriculaCurso["id_matricula"] = $matricula->id_matricula;
        // $matriculaCurso["id_cursohorario"] = ;
        $matriculaCurso["id_periodocurso"] = $periodoCurso->id_periodocurso;
        $matriculaCurso["id_curso"] = $periodoCurso->id_curso;
        $matriculaCurso["fechareg"] = now();
        $matriculaCurso = MatriculaCurso::create($matriculaCurso);

        $token = AuthController::resetToken($persona->id_persona, $periodoCurso->id_empresa, $idRolStudent);

        $result = Matricula::find($matricula->id_matricula);
        $result->token = $token;

        return response()->json($result);
    }

    public function precioCursoLibres(Request $request)
    {
        $result = DB::table('periodo_curso_precio as a')
            ->select(
                'a.id_periodocursoprecio',
                'a.importe',
                'a.tipo',
                DB::raw("round((a.importe/12), 2) as importe_mes"),
                DB::raw("case when a.tipo = '1' then 'Anual ilimitado' when a.tipo = '2' then 'Mensual ilimitado' end tipo_descripcion"),
                DB::raw("case when a.tipo = '1' then 'año' when a.tipo = '2' then 'mes' end tipo_nombre")
            )
            ->where("a.estado", "1");

        if(isset($request->id_periodocurso)) {
            $result->where("a.id_periodocurso", $request->id_periodocurso);
        }

        $result->orderBy("tipo", "asc");
        $result = $result->get();

        return response()->json($result);
    }

    public function categoriaCursosLibres()
    {
        $result = DB::table('periodo_curso as a')
            ->join('periodo as b', 'a.id_periodo', DB::raw("b.id_periodo and a.estado = '1' and b.esta_abierto = '1'"))
            ->join('tipo_categoria as c', 'a.id_tipocategoria', DB::raw("c.id_tipocategoria and (a.es_sincrono = '0' || c.visible_web = '1')"))
            ->rightJoin('tipo_categoria as d', 'a.id_tipocategoria', 'd.id_tipocategoria')
            ->select(
                "d.id_tipocategoria",
                "d.orden",
                "d.nombre",
                DB::raw("sum(case when a.id_tipocategoria is not null then 1 else 0 end) as cant_cursos")
            )
            ->where("d.tipo", "1");

        $result->groupBy("d.id_tipocategoria", "d.orden", "d.nombre");
        $result = $result->get();

        return response()->json($result);
    }
}