<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\UsuarioRol;
use App\Models\UsuarioSesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // /**
    //  * Login de usuario y generación de token JWT.
    //  */

    // public function login(Request $request)
    // {
    //     $credentials = $request->only('username', 'password');

    //     if (!$token = JWTAuth::attempt($credentials)) {
    //         return response()->json(['error' => 'Credenciales inválidas'], 401);
    //     }

    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type' => 'bearer',
    //         'user' => JWTAuth::user()
    //     ]);
    // }


    // /**
    //  * Logout del usuario y revocación del token JWT.
    //  */
    // public function logout(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'idUsuario' => 'required|integer|exists:usuarios,idUsuario',
    //     ], [
    //         'idUsuario.required' => 'El campo idUsuario es obligatorio',
    //         'idUsuario.exists' => 'El usuario no existe',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     $user = Usuario::where('idUsuario', $request->idUsuario)->first();

    //     if ($user) {
    //         try {

    //             $user->estado = 'loggedOff';
    //             $user->save();

    //             return response()->json(['success' => true, 'message' => 'Usuario deslogueado correctamente'], 200);
    //         } catch (JWTException $e) {
    //             return response()->json(['error' => 'No se pudo desloguear al usuario'], 500);
    //         }
    //     }

    //     return response()->json(['success' => false, 'message' => 'No se pudo encontrar el usuario'], 404);
    // }

    /**
     * Refrescar el token JWT.
     */
    public function refreshToken(Request $request)
    {
        try {

            $oldToken = JWTAuth::getToken();
            Log::info('Refrescando token: Token recibido', ['token' => (string) $oldToken]);

            $newToken = JWTAuth::refresh($oldToken);
            Log::info('Token refrescado: Nuevo token', ['newToken' => $newToken]);

            return response()->json(['accessToken' => $newToken], 200);
        } catch (JWTException $e) {

            Log::error('Error al refrescar el token', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'No se pudo refrescar el token'], 500);
        }
    }

    /**
     * Actualizar ultima actividad usuario.
     */
    public function updateLastActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuarios,idUsuario',
        ], [
            'idUsuario.required' => 'El campo idUsuario es obligatorio',
            'idUsuario.exists' => 'El usuario no existe',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = Usuario::find($request->idUsuario);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $user->activity()->updateOrCreate(
            ['idUsuario' => $user->idUsuario],
            ['last_activity' => now()]
        );

        return response()->json(['message' => 'Last activity updated'], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'El usuario es requerido.',
            'password.required' => 'La contraseña es requerida.',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",", $validator->messages()->all()),
                400
            );
        }

        $usuario = Usuario::select('usuario.*', "b.id_empresa", "b.id_rol", DB::raw("c.nombre as url_base"),
                DB::raw("d.nombre_completo as nombre_completop"))
            ->join('usuario_rol as b', 'usuario.id_usuario', 'b.id_usuario')
            ->join('rol as c', 'b.id_rol', 'c.id_rol')
            ->join('persona as d', 'usuario.id_usuario', 'd.id_persona')
            ->where('username', $request->username)
            ->where('usuario.estado', '1')
            ->where('b.es_principal', '1')
            ->first();

        if (!$usuario || !password_verify($request->password, $usuario->password)) {
            return response()->json('Nombre de usuario y/o contraseña incorrectos.', 400);
        }

        DB::table("usuario_sesion")
            ->where("id_usuario", $usuario->id_usuario)
            ->update(["estado" => "0"]);

        $usuarioSesion = [];
        $usuarioSesion["id_empresa"] = $usuario->id_empresa;
        $usuarioSesion["id_usuario"] = $usuario->id_usuario;
        $usuarioSesion["id_rol"] = $usuario->id_rol;
        $usuarioSesion["token"] = $this->apiToken();
        $usuarioSesion["estado"] = "1";
        $usuarioSesion["fechareg"] = now();
        $usuarioSesion["fechamod"] = now();
        $usuarioSesion = UsuarioSesion::create($usuarioSesion);

        return response()->json([
            "token" => $usuarioSesion->token,
            "id_usuario" => $usuario->id_usuario,
            "nombre" => $usuario->nombre_completop,
            "message" => 'Se logueo correctamente.',
            "url_base" => $usuario->url_base,
        ], 200);
    }

    public function logout(Request $request)
    {
        $authorization = $request->header('Authorization') ?? "";
        $token = str_replace("Bearer ", "", $authorization);
        $usuario = UsuarioSesion::where("token", $token)->first();

        if (!$usuario) {
            return response()->json(['message' => 'Ocurrio un error al cerrar sesión.'], 400);
        }

        UsuarioSesion::where("id_usuario", $usuario->id_usuario)
            ->where("id_empresa", $usuario->id_empresa)
            ->where("estado", "1")
            ->update(["estado" => "0"]);

        return response()->json(['message' => 'Peticiòn exitosa.'], 200);
    }

    public static function resetToken($id_usuario, $id_empresa, $id_rol)
    {
        DB::table("usuario_sesion")
            ->where("id_usuario", $id_usuario)
            ->update(["estado" => "0"]);

        $usuarioSesion = [];
        $usuarioSesion["id_empresa"] = $id_empresa;
        $usuarioSesion["id_usuario"] = $id_usuario;
        $usuarioSesion["id_rol"] = $id_rol;
        $usuarioSesion["token"] = self::apiTokenStatic();
        $usuarioSesion["estado"] = "1";
        $usuarioSesion["fechareg"] = now();
        $usuarioSesion["fechamod"] = now();
        $usuarioSesion = UsuarioSesion::create($usuarioSesion);

        return $usuarioSesion->token;
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
