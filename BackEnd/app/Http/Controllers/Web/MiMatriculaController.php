<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\PeriodoCurso;
use App\Models\MatriculaCurso;
use App\Models\UsuarioRol;
use App\Models\MensajeriaGrupo;
use App\Models\MensajeriaPersona;
use App\Models\PeriodoCursoPrecio;

class MiMatriculaController extends Controller
{
    public function storeConMembresia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocurso" => "required",
        ], [
            "id_periodocurso" => "El curso es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $membresia = DB::table("membresia as a")
            ->select(
                "a.*"
            )
            ->where("a.estado", "1")
            ->where("a.id_persona", $user->id_usuario)
            ->whereRaw("a.fecha_fin >= cast(now() as date)")
            ->first();
        if (!$membresia) {
            return response()->json('¡Atención! No tiene membresia valida.', 400);
        }

        $periodoCurso = PeriodoCurso::find($request->id_periodocurso);
        if (!$periodoCurso) {
            return response()->json('¡Atención! El curso no existe.', 400);
        }

        $matriculaCurso = DB::table("matricula_curso as a")
            ->join("matricula as b", "a.id_matricula", "b.id_matricula")
            ->where("a.id_periodocurso", $request->id_periodocurso)
            ->where("b.id_estudiante", $user->id_usuario)
            ->where("b.estado", "1")
            ->first();
        if ($matriculaCurso) {
            return response()->json('¡Atención! Tiene una matriculado en el curso.', 400);
        }

        DB::beginTransaction();

        $estudiante = Estudiante::find($user->id_usuario);
        if(!$estudiante){
            $codigo_ultimo = Estudiante::max("codigo");
            $codigo_nuevo = $codigo_ultimo + 1;
            $estudiante = [];
            $estudiante["id_estudiante"] = $user->id_usuario;
            $estudiante["codigo"] = $codigo_nuevo;
            $estudiante["estado"] = "1";
            $estudiante["fechareg"] = now();
            $estudiante = Estudiante::create($estudiante);
        }

        $idRolStudent = 5;  // student
        $usuarioRol = UsuarioRol::where("id_usuario", $user->id_usuario)
            ->where("id_empresa", $periodoCurso->id_empresa)
            ->where("id_rol", $idRolStudent)
            ->first();
        if(!$usuarioRol){
            $usuarioRol = [];
            $usuarioRol["id_empresa"] = $periodoCurso->id_empresa;
            $usuarioRol["id_usuario"] = $user->id_usuario;
            $usuarioRol["id_rol"] = $idRolStudent;
            $usuarioRol["es_principal"] = "0";
            $usuarioRol = UsuarioRol::create($usuarioRol);
        }

        $matricula = [];
        $matricula["id_empresa"] = $periodoCurso->id_empresa;
        $matricula["id_periodo"] = $periodoCurso->id_periodo;
        $matricula["id_estudiante"] = $user->id_usuario;
        $matricula["importe"] = 0;
        $matricula["tipo"] = "2"; // membresia
        $matricula["id_membresia"] = $membresia->id_membresia;
        $matricula["estado"] = "1";
        $matricula["fechareg"] = now();
        $matricula = Matricula::create($matricula);

        $matriculaCurso = [];
        $matriculaCurso["id_empresa"] = $periodoCurso->id_empresa;
        $matriculaCurso["id_matricula"] = $matricula->id_matricula;
        $matriculaCurso["id_periodocurso"] = $periodoCurso->id_periodocurso;
        $matriculaCurso["id_curso"] = $periodoCurso->id_curso;
        $matriculaCurso["fechareg"] = now();
        $matriculaCurso = MatriculaCurso::create($matriculaCurso);

        $mensajeriaGrupo = MensajeriaGrupo::where("id_periodocurso", $periodoCurso->id_periodocurso)
            ->where("tipo", "1")
            ->first();
        if($mensajeriaGrupo){
            $mensajeriaPersona = MensajeriaPersona::where("id_mensajeriagrupo", $mensajeriaGrupo->id_mensajeriagrupo)
                ->where("id_persona", $user->id_usuario)
                ->first();
            if(!$mensajeriaPersona){
                $mensajeriaPersona = [];
                $mensajeriaPersona["id_mensajeriagrupo"] = $mensajeriaGrupo->id_mensajeriagrupo;
                $mensajeriaPersona["id_persona"] = $user->id_usuario;
                $mensajeriaPersona["estado"] = "1";
                $mensajeriaPersona["id_usuarioreg"] = $user->id_usuario;
                $mensajeriaPersona["fechareg"] = now();
                $mensajeriaPersona = MensajeriaPersona::create($mensajeriaPersona);
            }
        }

        DB::commit();

        $matricula = Matricula::find($matricula->id_matricula);

        return response()->json($matricula);
    }

    public function storeSinMembresia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocursoprecio" => "required",
            "importe" => "required|numeric|min:0|not_in:0",
            "numero_operacion" => "required|max:50",
            "importe_operacion" => "required|numeric|min:0|not_in:0",
        ], [
            "id_periodocursoprecio" => "El precio es requerido",
            "importe" => "El importe es requerido",
            "numero_operacion" => "El número operación es requerido",
            "importe_operacion" => "El importe operación es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $periodoCursoPrecio = PeriodoCursoPrecio::find($request->id_periodocursoprecio);
        if (!$periodoCursoPrecio) {
            return response()->json('¡Atención! El precio del curso no existe.', 400);
        }
        $periodoCurso = PeriodoCurso::find($periodoCursoPrecio->id_periodocurso);

        $matriculaCurso = DB::table("matricula_curso as a")
            ->join("matricula as b", "a.id_matricula", "b.id_matricula")
            ->where("a.id_periodocurso", $periodoCurso->id_periodocurso)
            ->where("b.id_estudiante", $user->id_usuario)
            ->where("b.estado", "1")
            ->first();
        if ($matriculaCurso) {
            return response()->json('¡Atención! Tiene una matriculado en el curso.', 400);
        }

        DB::beginTransaction();

        $estudiante = Estudiante::find($user->id_usuario);
        if(!$estudiante){
            $codigo_ultimo = Estudiante::max("codigo");
            $codigo_nuevo = $codigo_ultimo + 1;
            $estudiante = [];
            $estudiante["id_estudiante"] = $user->id_usuario;
            $estudiante["codigo"] = $codigo_nuevo;
            $estudiante["estado"] = "1";
            $estudiante["fechareg"] = now();
            $estudiante = Estudiante::create($estudiante);
        }

        $idRolStudent = 5;  // student
        $usuarioRol = UsuarioRol::where("id_usuario", $user->id_usuario)
            ->where("id_empresa", $periodoCurso->id_empresa)
            ->where("id_rol", $idRolStudent)
            ->first();
        if(!$usuarioRol){
            $usuarioRol = [];
            $usuarioRol["id_empresa"] = $periodoCurso->id_empresa;
            $usuarioRol["id_usuario"] = $user->id_usuario;
            $usuarioRol["id_rol"] = $idRolStudent;
            $usuarioRol["es_principal"] = "0";
            $usuarioRol = UsuarioRol::create($usuarioRol);
        }

        $matricula = [];
        $matricula["id_empresa"] = $periodoCurso->id_empresa;
        $matricula["id_periodo"] = $periodoCurso->id_periodo;
        $matricula["id_estudiante"] = $user->id_usuario;
        $matricula["importe"] = 0;
        $matricula["tipo"] = "1";  // sin membresia
        $matricula["estado"] = "1";
        $matricula["fechareg"] = now();
        $matricula = Matricula::create($matricula);

        $matriculaCurso = [];
        $matriculaCurso["id_empresa"] = $periodoCurso->id_empresa;
        $matriculaCurso["id_matricula"] = $matricula->id_matricula;
        $matriculaCurso["id_periodocurso"] = $periodoCurso->id_periodocurso;
        $matriculaCurso["id_curso"] = $periodoCurso->id_curso;
        $matriculaCurso["fechareg"] = now();
        $matriculaCurso = MatriculaCurso::create($matriculaCurso);

        $mensajeriaGrupo = MensajeriaGrupo::where("id_periodocurso", $periodoCurso->id_periodocurso)
            ->where("tipo", "1")
            ->first();
        if($mensajeriaGrupo){
            $mensajeriaPersona = MensajeriaPersona::where("id_mensajeriagrupo", $mensajeriaGrupo->id_mensajeriagrupo)
                ->where("id_persona", $user->id_usuario)
                ->first();
            if(!$mensajeriaPersona){
                $mensajeriaPersona = [];
                $mensajeriaPersona["id_mensajeriagrupo"] = $mensajeriaGrupo->id_mensajeriagrupo;
                $mensajeriaPersona["id_persona"] = $user->id_usuario;
                $mensajeriaPersona["estado"] = "1";
                $mensajeriaPersona["id_usuarioreg"] = $user->id_usuario;
                $mensajeriaPersona["fechareg"] = now();
                $mensajeriaPersona = MensajeriaPersona::create($mensajeriaPersona);
            }
        }

        DB::commit();

        $matricula = Matricula::find($matricula->id_matricula);

        return response()->json($matricula);
    }
}