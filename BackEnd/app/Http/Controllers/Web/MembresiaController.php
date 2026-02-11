<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Persona;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Membresia;
use App\Models\MembresiaTipo;
use App\Models\UsuarioRol;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneratedPasswordNotification;

class MembresiaController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_membresiatipo' => 'required',
            'precio' => 'required|numeric|min:0|not_in:0',
            'id_tipodocumento' => 'required',
            'numero_documento' => 'required|string|max:20|min:8',
            'nombre_completo' => 'required|string|max:450',
            'telefono' => 'required|string|max:20|min:7',
            'direccion' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255',
            'numero_operacion' => 'required|max:50',
            'importe_operacion' => 'required|numeric|not_in:0|min:1',
        ], [
            'id_membresiatipo' => 'El tipo es requerido',
            'precio' => 'El precio es requerido',
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

        $membresiaTipo = MembresiaTipo::find($request->id_membresiatipo);
        if (!$membresiaTipo) {
            return response()->json('Error. El tipo de membresía no existe.', 400);
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

            $estudiante = [];
            $estudiante["id_estudiante"] = $persona->id_persona;
            $estudiante["estado"] = "1";
            $estudiante["fechareg"] = now();
            $estudiante = Estudiante::create($estudiante);

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

            $idRolStudent = 5;  // student
            $usuarioRol = [];
            $usuarioRol["id_usuario"] = $persona->id_persona;
            $usuarioRol["id_rol"] = $idRolStudent;
            $usuarioRol["es_principal"] = "1";
            $usuarioRol = UsuarioRol::create($usuarioRol);

            $membresia = [];
            $membresia["id_persona"] = $persona->id_persona;
            $membresia["id_membresiatipo"] = $request->id_membresiatipo;
            $membresia["precio"] = $request->precio;
            $membresia["fecha_inicio"] = now();
            $membresia["fecha_fin"] = $membresiaTipo->es_anual == '1' ? now()->addMonths(12) : now()->addMonth();
            $membresia["estado"] = "1";
            $membresia["fechareg"] = now();
            $membresia = Membresia::create($membresia);

            $token = AuthController::resetToken($persona->id_persona, null, $idRolStudent);

            DB::commit();

            // Enviar contraseña por email (fuera de la transacción)
            try {
                Notification::route('mail', $request->correo)
                    ->notify(new GeneratedPasswordNotification($plainPassword, $request->nombre_completo));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo enviar email de contraseña: ' . $e->getMessage());
            }

            $membresia = Membresia::find($membresia->id_membresia);
            $membresia->token = $token;

            return response()->json($membresia);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json('Error al crear la membresía: ' . $e->getMessage(), 500);
        }
    }
}