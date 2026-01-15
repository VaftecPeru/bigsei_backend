<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Persona;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Licencia;
use App\Models\LicenciaTipo;
use App\Models\UsuarioRol;
use App\Http\Controllers\AuthController;

class LicenciaController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_licenciatipo' => 'required',
            'precio' => 'required|numeric|min:0|not_in:0',
            'id_tipodocumento' => 'required',
            'numero_documento' => 'required|string|max:20|min:8',
            'nombre_completo' => 'required|string|max:450',
            'telefono' => 'required|string|max:20|min:7',
            'direccion' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255',
            'numero_operacion' => 'required|max:50',
            'importe_operacion' => 'required|numeric|not_in:0|min:1',
            'empresa_id_tipodocumento' => 'required',
            'empresa_numero_documento' => 'required|max:50',
            'empresa_razon_social' => 'required|max:255',
        ], [
            'id_licenciatipo' => 'La licencia es requerida',
            'precio' => 'El precio es requerido',
            'id_tipodocumento' => 'El tipo documento es requerido',
            'numero_documento' => 'El número documento es requerido',
            'nombre_completo' => 'El nombre es requerido',
            'telefono' => 'El teléfono es requerido',
            'direccion' => 'El dirección es requerido',
            'correo' => 'El correo es requerido',
            'numero_operacion' => 'El número operación es requerido',
            'importe_operacion' => 'El importe es requerido',
            'empresa_id_tipodocumento' => 'El tipo de documento es requerido',
            'empresa_numero_documento' => 'El número de documento es requerido',
            'empresa_razon_social' => 'La razón social es requerida',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $licenciaTipo = LicenciaTipo::find($request->id_licenciatipo);
        if (!$licenciaTipo) {
            return response()->json('Error. El tipo de licencia no existe.', 400);
        }

        $correoUsuario = Usuario::where("email", $request->correo)
            ->first();
        if ($correoUsuario) {
            return response()->json('¡Atención! El correo de usuario en esta registrado por otra persona.', 400);
        }

        $numeroDocumentoPersona = Persona::where("numero_documento", $request->numero_documento)
            ->where("id_tipodocumento", $request->id_tipodocumento)->first();
        if ($numeroDocumentoPersona) {
            return response()->json('Error. El documento existe.', 400);
        }

        $numeroDocumentoEmpresa = Empresa::where("numero_documento", $request->empresa_numero_documento)
            ->where("id_tipodocumento", $request->empresa_id_tipodocumento)->first();
        if ($numeroDocumentoEmpresa) {
            return response()->json('Error. El número de documento empresa existe.', 400);
        }

        $persona = [];
        $persona["id_tipodocumento"] = $request->id_tipodocumento;
        $persona["numero_documento"] = $request->numero_documento;
        $persona["nombre_completo"] = $request->nombre_completo;
        $persona["telefono"] = $request->telefono;
        $persona["direccion"] = $request->direccion;
        $persona["correo"] = $request->correo;
        $persona["fechareg"] = now();
        $persona["estado"] = '1';
        $persona = Persona::create($persona);

        $empresa = [];
        $empresa["id_tipodocumento"] = $request->empresa_id_tipodocumento;
        $empresa["numero_documento"] = $request->empresa_numero_documento;
        $empresa["razon_social"] = $request->empresa_razon_social;
        $empresa["tipo_relacion"] = "C";
        $empresa["correo"] = $request->correo;
        $empresa["contacto"] = $request->nombre_completo;
        $empresa = Empresa::create($empresa);

        $usuario = [];
        $usuario["id_usuario"] = $persona->id_persona;
        $usuario["email"] = $request->correo;
        $usuario["username"] = $request->correo;
        $usuario["password"] = Hash::make("123");
        $usuario["estado"] = "1";
        $usuario["fechareg"] = now();
        $usuario = Usuario::create($usuario);

        $idRolAdmin = 2;  // admin
        $usuarioRol = [];
        $usuarioRol["id_empresa"] = $empresa->id_empresa;
        $usuarioRol["id_usuario"] = $persona->id_persona;
        $usuarioRol["id_rol"] = $idRolAdmin;
        $usuarioRol["es_principal"] = "1";
        $usuarioRol = UsuarioRol::create($usuarioRol);

        $licencia = [];
        $licencia["id_empresa"] = $empresa->id_empresa;
        $licencia["id_licenciatipo"] = $request->id_licenciatipo;
        $licencia["precio"] = $request->precio;
        $licencia["fecha_inicio"] = now();
        $licencia["fecha_fin"] = now()->addYear();
        $licencia["estado"] = "1";
        $licencia["fechareg"] = now();
        $licencia = Licencia::create($licencia);

        $token = AuthController::resetToken($persona->id_persona, $empresa->id_empresa, $idRolAdmin);

        $licencia = Licencia::find($licencia->id_licencia);
        $licencia->token = $token;

        return response()->json($licencia);
    }

    public function tipoActivos(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 2;
        }
        $result = DB::table("licencia_tipo")
            ->select(
                "id_licenciatipo",
                "nombre",
                "descripcion",
                "precio"
            )
            ->where("estado", "1");

        $result->orderBy("precio", "desc");
        $result = $result->paginate($per_page)
            ->through(fn ($licenciaTipo) => [
                "id_licenciatipo" => $licenciaTipo->id_licenciatipo,
                "nombre" => $licenciaTipo->nombre,
                "descripcion" => $licenciaTipo->descripcion,
                "precio" => $licenciaTipo->precio,
                "tipo_beneficios" => DB::table("tipo_beneficio as a")
                    ->join("licencia_tipo_beneficio as b", "a.id_tipobeneficio", "b.id_tipobeneficio")
                    ->select("a.descripcion", "b.orden", ".esta_habilitado")
                    ->where("b.id_licenciatipo", $licenciaTipo->id_licenciatipo)
                    ->orderBy("orden", "asc")
                    ->get()
            ]);

        return response()->json($result);
    }
}