<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UsuarioSesion;
use App\Models\Rol;
use App\Models\Empresa;

/**
 * UserController - Gestión de usuario logueado
 * 
 * Maneja operaciones del usuario actual como:
 * - Cambiar sede/empresa sin cerrar sesión
 * - Cambiar rol sin cerrar sesión
 * - Obtener datos actualizados del usuario
 */
class UserController extends Controller
{
    /**
     * Cambiar sede/empresa del usuario logueado
     * POST /api/user/change-empresa
     */
    public function changeEmpresa(Request $request)
    {
        $request->validate([
            'id_empresa' => 'required|integer|exists:empresa,id_empresa',
        ]);

        try {
            $sessionUser = $request->sessionUser;
            $id_usuario = $sessionUser->id_usuario;
            $id_rol = $sessionUser->id_rol;

            // Verificar que el usuario tenga acceso a esa empresa
            $tieneAcceso = DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->where('id_empresa', $request->id_empresa)
                ->exists();

            if (!$tieneAcceso) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene acceso a esta sede'
                ], 403);
            }

            // Actualizar la sede en la sesión actual
            DB::table('usuario_sesion')
                ->where('token', $sessionUser->token)
                ->update(['id_empresa' => $request->id_empresa]);

            // Actualizar la sede en el rol principal
            DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->where('id_rol', $id_rol)
                ->where('es_principal', '1')
                ->update(['id_empresa' => $request->id_empresa]);

            // Actualizar persona
            DB::table('persona')
                ->where('id_persona', $id_usuario)
                ->update(['id_empresa' => $request->id_empresa]);

            Log::info('Usuario cambió de sede', [
                'id_usuario' => $id_usuario,
                'old_empresa' => $sessionUser->id_empresa,
                'new_empresa' => $request->id_empresa,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sede cambiada exitosamente',
                'data' => [
                    'id_empresa' => $request->id_empresa,
                    'empresa' => Empresa::find($request->id_empresa),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al cambiar sede: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar sede',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar rol del usuario logueado
     * POST /api/user/change-rol
     */
    public function changeRol(Request $request)
    {
        $request->validate([
            'id_rol' => 'required|integer|exists:rol,id_rol',
        ]);

        try {
            $sessionUser = $request->sessionUser;
            $id_usuario = $sessionUser->id_usuario;

            // Verificar que el usuario tenga ese rol asignado
            $usuarioRol = DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->where('id_rol', $request->id_rol)
                ->first();

            if (!$usuarioRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene asignado este rol'
                ], 403);
            }

            // Desactivar rol principal actual
            DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->where('es_principal', '1')
                ->update(['es_principal' => '0']);

            // Activar nuevo rol como principal
            DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->where('id_rol', $request->id_rol)
                ->update(['es_principal' => '1']);

            // Actualizar sesión con nuevo rol y empresa
            DB::table('usuario_sesion')
                ->where('token', $sessionUser->token)
                ->update([
                    'id_rol' => $request->id_rol,
                    'id_empresa' => $usuarioRol->id_empresa,
                ]);

            $rol = Rol::find($request->id_rol);

            Log::info('Usuario cambió de rol', [
                'id_usuario' => $id_usuario,
                'old_rol' => $sessionUser->id_rol,
                'new_rol' => $request->id_rol,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rol cambiado exitosamente',
                'data' => [
                    'id_rol' => $request->id_rol,
                    'rol' => $rol,
                    'url_base' => $rol->codigo,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al cambiar rol: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar sede y rol simultáneamente
     * POST /api/user/change-sede-rol
     */
    public function changeSedeAndRol(Request $request)
    {
        $request->validate([
            'id_empresa' => 'required|integer|exists:empresa,id_empresa',
            'id_rol' => 'required|integer|exists:rol,id_rol',
        ]);

        try {
            $sessionUser = $request->sessionUser;
            $id_usuario = $sessionUser->id_usuario;

            // Verificar que el usuario tenga ese rol en esa empresa
            $usuarioRol = DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->where('id_rol', $request->id_rol)
                ->where('id_empresa', $request->id_empresa)
                ->first();

            if (!$usuarioRol) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene asignado este rol en esta sede'
                ], 403);
            }

            // Desactivar rol principal actual
            DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->where('es_principal', '1')
                ->update(['es_principal' => '0']);

            // Activar nuevo rol como principal
            DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->where('id_rol', $request->id_rol)
                ->where('id_empresa', $request->id_empresa)
                ->update(['es_principal' => '1']);

            // Actualizar persona
            DB::table('persona')
                ->where('id_persona', $id_usuario)
                ->update(['id_empresa' => $request->id_empresa]);

            // Actualizar sesión
            DB::table('usuario_sesion')
                ->where('token', $sessionUser->token)
                ->update([
                    'id_rol' => $request->id_rol,
                    'id_empresa' => $request->id_empresa,
                ]);

            $rol = Rol::find($request->id_rol);
            $empresa = Empresa::find($request->id_empresa);

            Log::info('Usuario cambió sede y rol', [
                'id_usuario' => $id_usuario,
                'new_rol' => $request->id_rol,
                'new_empresa' => $request->id_empresa,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración cambiada exitosamente',
                'data' => [
                    'id_rol' => $request->id_rol,
                    'rol' => $rol,
                    'id_empresa' => $request->id_empresa,
                    'empresa' => $empresa,
                    'url_base' => $rol->codigo,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al cambiar configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos actualizados del usuario logueado
     * GET /api/user/me
     */
    public function me(Request $request)
    {
        try {
            $sessionUser = $request->sessionUser;
            $id_usuario = $sessionUser->id_usuario;

            $usuario = DB::table('usuario')
                ->select(
                    'usuario.id_usuario',
                    'usuario.dni',
                    'usuario.nombres',
                    'usuario.apellidoPaterno',
                    'usuario.apellidoMaterno',
                    'usuario.correo',
                    'usuario.telefono',
                    'usuario.username',
                    'usuario.foto',
                    DB::raw("CONCAT(usuario.nombres, ' ', usuario.apellidoPaterno, ' ', usuario.apellidoMaterno) as nombre_completo"),
                    'empresa.id_empresa',
                    'empresa.razon_social as empresa_nombre',
                    'empresa.direccion_fiscal as empresa_direccion',
                    'rol.id_rol',
                    'rol.nombre as rol_nombre',
                    'rol.codigo as rol_codigo'
                )
                ->leftJoin('usuario_rol as ur', function($join) {
                    $join->on('usuario.id_usuario', '=', 'ur.id_usuario')
                         ->where('ur.es_principal', '=', '1');
                })
                ->leftJoin('rol', 'ur.id_rol', '=', 'rol.id_rol')
                ->leftJoin('empresa', 'ur.id_empresa', '=', 'empresa.id_empresa')
                ->where('usuario.id_usuario', $id_usuario)
                ->first();

            // Obtener todos los roles del usuario
            $roles = DB::table('usuario_rol')
                ->where('id_usuario', $id_usuario)
                ->join('rol', 'usuario_rol.id_rol', '=', 'rol.id_rol')
                ->join('empresa', 'usuario_rol.id_empresa', '=', 'empresa.id_empresa')
                ->select(
                    'rol.id_rol',
                    'rol.nombre',
                    'rol.codigo',
                    'empresa.id_empresa',
                    'empresa.razon_social as empresa_nombre',
                    'usuario_rol.es_principal'
                )
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'usuario' => $usuario,
                    'roles' => $roles,
                    'rol_actual' => [
                        'id' => $usuario->id_rol,
                        'nombre' => $usuario->rol_nombre,
                        'codigo' => $usuario->rol_codigo,
                    ],
                    'empresa_actual' => [
                        'id' => $usuario->id_empresa,
                        'nombre' => $usuario->empresa_nombre,
                    ],
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener datos de usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
