<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Usuario;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\UsuarioSesion;
use Google_Client;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Buscar o crear usuario
            $usuario = Usuario::firstOrNew(['correo' => $googleUser->getEmail()]);

            // Dividir el nombre completo en partes
            $nombreCompleto = explode(' ', $googleUser->getName(), 2);
            $nombres = $nombreCompleto[0];
            $apellidos = $nombreCompleto[1] ?? '';
            $email = $googleUser->getEmail();
            $name = $googleUser->getName();
            $googleId = $googleUser->getId();
            $avatar = $googleUser->getAvatar();

            // Si es nuevo usuario o no tiene google_id
            if (!$usuario->exists || empty($usuario->google_id)) {
                // 1. Crear persona
                $persona = Persona::create([
                    'nombre' => $nombres,
                    'apellido_paterno' => $apellidos,
                    'apellido_materno' => '',
                    'correo' => $email,
                    'estado' => 1,
                    'fechareg' => now(),
                    'nombre_completo' => $name,
                ]);

                // 2. Crear usuario ligado a persona
                $usuario->fill([
                    'id_usuario' => $persona->id_persona,
                    'nombres' => $nombres,
                    'apellidoPaterno' => $apellidos,
                    'apellidoMaterno' => '',
                    'google_id' => $googleId,
                    'foto' => $avatar,
                    'password' => bcrypt(uniqid()),
                    'estado' => 1,
                ])->save();
            }

            $rolPorDefecto = \App\Models\Rol::where('nombreRol', 'student')->first();

            if ($rolPorDefecto && !\App\Models\UsuarioRol::where('id_usuario', $usuario->id_usuario)->where('id_rol', $rolPorDefecto->id_rol)->exists()) {
                \App\Models\UsuarioRol::create([
                    'id_empresa' => 1,
                    'id_usuario' => $usuario->id_usuario,
                    'id_rol' => $rolPorDefecto->id_rol,
                    'es_principal' => 0,
                ]);
            }

            // Generar token JWT
            $token = JWTAuth::fromUser($usuario);
            $rolUsuario = $usuario->roles()->first();
            $nombreRol = optional(optional($rolUsuario)->rol)->nombreRol ?? 'student';

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombreCompleto' => $usuario->nombreCompleto,
                    'correo' => $usuario->correo,
                    'foto' => $usuario->foto,
                    'rol' => $nombreRol
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Google Auth Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al autenticar con Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyGoogleToken(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado',
            ], 400);
        }

        $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($token);

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Token de Google inválido',
            ], 401);
        }

        $email = $payload['email'];
        $name = $payload['name'] ?? '';
        $googleId = $payload['sub'];
        $avatar = $payload['picture'] ?? '';

        $nombreCompleto = explode(' ', $name, 2);
        $nombres = $nombreCompleto[0];
        $apellidos = $nombreCompleto[1] ?? '';

        // Verifica si existe una persona registrada con ese correo
        $persona = Persona::where('correo', $email)->first();

        if (!$persona) {
            return response()->json([
                'success' => false,
                'message' => 'Este correo no está autorizado para iniciar sesión con Google.',
            ], 403);
        }

        // Verifica si ya existe un usuario con ese correo
        $usuario = Usuario::where('correo', $email)->first();

        // Si no existe usuario, lo creamos
        if (!$usuario) {
            $usuario = new Usuario([
                'id_usuario' => $persona->id_persona,
                'nombres' => $nombres,
                'apellidoPaterno' => $apellidos,
                'apellidoMaterno' => '',
                'google_id' => $googleId,
                'foto' => $avatar,
                'correo' => $email,
                'password' => bcrypt(uniqid()),
                'estado' => 1,
                'id_empresa' => 1,
            ]);
            $usuario->save();
        }

        // Asignar rol por defecto si no tiene
        $rolPorDefecto = \App\Models\Rol::where('nombre', 'student')->first();

        if ($rolPorDefecto && !\App\Models\UsuarioRol::where('id_usuario', $usuario->id_usuario)->where('id_rol', $rolPorDefecto->id_rol)->exists()) {
            \App\Models\UsuarioRol::create([
                'id_empresa' => 1,
                'id_usuario' => $usuario->id_usuario,
                'id_rol' => $rolPorDefecto->id_rol,
                'es_principal' => 1,
            ]);
        }

        $rolUsuario = $usuario->roles()->first();
        $nombreRol = optional(optional($rolUsuario)->rol)->nombreRol ?? 'student';

        DB::table("usuario_sesion")
            ->where("id_usuario", $usuario->id_usuario)
            ->update(["estado" => "0"]);

        $usuarioSesion = [];
        $usuarioSesion["id_empresa"] = "1";
        $usuarioSesion["id_usuario"] = $usuario->id_usuario;
        $usuarioSesion["id_rol"] = $rolPorDefecto->id_rol;
        $usuarioSesion["token"] = $this->apiToken();
        $usuarioSesion["estado"] = "1";
        $usuarioSesion["fechareg"] = now();
        $usuarioSesion["fechamod"] = now();
        $usuarioSesion = UsuarioSesion::create($usuarioSesion);

        $request->merge([
            "sessionUser" => [
                "id_usuario" => $usuario->id_usuario,
                "id_empresa" => $usuario->id_empresa,
                "id_rol" => $rolPorDefecto->id_rol,
                "token" => $usuarioSesion->token
            ]
        ]);

        return response()->json([
            'success' => true,
            'token' => $usuarioSesion->token,
            'user' => [
                'id_usuario' => $usuario->id_usuario,
                'nombreCompleto' => $usuario->nombreCompleto,
                'correo' => $usuario->correo,
                'foto' => $usuario->foto,
                'rol' => $nombreRol,
            ]
        ]);
    }

    private function apiToken()
    {
        $str_random = Str::random(60);
        $apiToken = uniqid(base64_encode($str_random));
        return $apiToken;
    }

    private static function apiTokenStatic()
    {
        $str_random = Str::random(60);
        $apiToken = uniqid(base64_encode($str_random));
        return $apiToken;
    }
}
