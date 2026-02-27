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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneratedPasswordNotification;

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

        try {
            DB::beginTransaction();

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

            // Generar contraseña segura en lugar de "123"
            $plainPassword = Str::random(12);

            $usuario = [];
            $usuario["id_usuario"] = $persona->id_persona;
            $usuario["email"] = $request->correo;
            $usuario["username"] = $request->correo;
            $usuario["password"] = Hash::make($plainPassword);
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

            DB::commit();

            // Enviar contraseña por email (fuera de la transacción)
            try {
                Notification::route('mail', $request->correo)
                    ->notify(new GeneratedPasswordNotification($plainPassword, $request->nombre_completo));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo enviar email de contraseña: ' . $e->getMessage());
            }

            $licencia = Licencia::find($licencia->id_licencia);
            $licencia->token = $token;

            return response()->json($licencia);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json('Error al crear la licencia: ' . $e->getMessage(), 500);
        }
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