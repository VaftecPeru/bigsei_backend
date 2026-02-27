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
    // ----------------------
    // Crear licencia normal
    // ----------------------
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
        ]);

        if ($validator->fails()) {
            return response()->json(implode(",", $validator->messages()->all()), 400);
        }

        $licenciaTipo = LicenciaTipo::find($request->id_licenciatipo);
        if (!$licenciaTipo) {
            return response()->json('Error. El tipo de licencia no existe.', 400);
        }

        $correoUsuario = Usuario::where("email", $request->correo)->first();
        if ($correoUsuario) {
            return response()->json('¡Atención! El correo de usuario está registrado por otra persona.', 400);
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

        // Crear persona, empresa, usuario y rol
        $persona = Persona::create([
            "id_tipodocumento" => $request->id_tipodocumento,
            "numero_documento" => $request->numero_documento,
            "nombre_completo" => $request->nombre_completo,
            "telefono" => $request->telefono,
            "direccion" => $request->direccion,
            "correo" => $request->correo,
            "fechareg" => now(),
            "estado" => '1'
        ]);

        $empresa = Empresa::create([
            "id_tipodocumento" => $request->empresa_id_tipodocumento,
            "numero_documento" => $request->empresa_numero_documento,
            "razon_social" => $request->empresa_razon_social,
            "tipo_relacion" => "C",
            "correo" => $request->correo,
            "contacto" => $request->nombre_completo
        ]);

        $usuario = Usuario::create([
            "id_usuario" => $persona->id_persona,
            "email" => $request->correo,
            "username" => $request->correo,
            "password" => Hash::make("123"),
            "estado" => "1",
            "fechareg" => now()
        ]);

        $idRolAdmin = 2;  // admin
        UsuarioRol::create([
            "id_empresa" => $empresa->id_empresa,
            "id_usuario" => $persona->id_persona,
            "id_rol" => $idRolAdmin,
            "es_principal" => "1"
        ]);

        $licencia = Licencia::create([
            "id_empresa" => $empresa->id_empresa,
            "id_licenciatipo" => $request->id_licenciatipo,
            "precio" => $request->precio,
            "fecha_inicio" => now(),
            "fecha_fin" => now()->addYear(),
            "estado" => "1",
            "fechareg" => now()
        ]);

        $token = AuthController::resetToken($persona->id_persona, null, $idRolAdmin);
        $licencia->token = $token;

        return response()->json($licencia);
    }

    // ----------------------
    // Listar tipos de licencia activos
    // ----------------------
    public function tipoActivos(Request $request)
    {
        $per_page = $request->per_page ?? 4;

        $result = DB::table("licencia_tipo")
            ->select("id_licenciatipo","nombre","descripcion","precio")
            ->where("estado", "1")
            ->orderBy("precio", "desc")
            ->paginate($per_page)
            ->through(fn ($licenciaTipo) => [
                "id_licenciatipo" => $licenciaTipo->id_licenciatipo,
                "nombre" => $licenciaTipo->nombre,
                "descripcion" => $licenciaTipo->descripcion,
                "precio" => $licenciaTipo->precio,
                "tipo_beneficios" => DB::table("tipo_beneficio as a")
                    ->join("licencia_tipo_beneficio as b", "a.id_tipobeneficio", "b.id_tipobeneficio")
                    ->select("a.descripcion", "b.orden", "b.esta_habilitado")
                    ->where("b.id_licenciatipo", $licenciaTipo->id_licenciatipo)
                    ->orderBy("orden", "asc")
                    ->get()
            ]);

        return response()->json($result);
    }

    // ----------------------
    // Crear nuevo tipo de licencia
    // ----------------------
    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|in:0,1',
        ]);

        $tipo = LicenciaTipo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'estado' => $request->estado,
            'id_usuarioreg' => auth()->id() ?? 1,
            'fechareg' => now(),
        ]);

        return response()->json($tipo, 201);
    }

    // ----------------------
    // Listar licencias activas (nuevo)
    // ----------------------
    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 4;

        $licencias = Licencia::with(['tipo','empresa'])
            ->orderBy('fecha_inicio','desc')
            ->paginate($per_page);

        return response()->json($licencias);
    }

    // ----------------------
    // Activar / Desactivar licencia
    // ----------------------
    public function toggle($id)
    {
        $licencia = Licencia::find($id);
        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }
        $licencia->estado = $licencia->estado == 1 ? 0 : 1;
        $licencia->save();

        return response()->json(['success' => true, 'estado' => $licencia->estado]);
    }

    // ----------------------
    // Renovar licencia
    // ----------------------
    public function renew($id)
    {
        $licencia = Licencia::find($id);
        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }
        $licencia->fecha_fin = now()->addYear();
        $licencia->save();

        return response()->json(['success' => true, 'fecha_fin' => $licencia->fecha_fin]);
    }
}