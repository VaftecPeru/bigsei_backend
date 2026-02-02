<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\UsuarioRol;
use App\Models\Rol;
use App\Models\Empresa;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * UsuarioController - Gestión completa de usuarios para SuperAdministrador
 * 
 * Permite CRUD de usuarios, asignación de roles y sedes
 * Acceso: Solo SuperAdministrador
 */
class UsuarioController extends Controller
{
    /**
     * Listar todos los usuarios del sistema (todas las sedes)
     * GET /api/superadministrador/usuarios
     */
    public function index(Request $request)
    {
        try {
            $query = Usuario::select(
                'usuario.id_usuario',
                'usuario.dni',
                'usuario.nombres',
                'usuario.apellidoPaterno',
                'usuario.apellidoMaterno',
                'usuario.correo',
                'usuario.telefono',
                'usuario.username',
                'usuario.estado',
                'usuario.fechareg',
                DB::raw("CONCAT(usuario.nombres, ' ', usuario.apellidoPaterno, ' ', usuario.apellidoMaterno) as nombre_completo"),
                'empresa.razon_social as sede',
                'empresa.id_empresa',
                'rol.nombre as rol_nombre',
                'rol.codigo as rol_codigo',
                'rol.id_rol'
            )
            ->leftJoin('usuario_rol as ur', function($join) {
                $join->on('usuario.id_usuario', '=', 'ur.id_usuario')
                     ->where('ur.es_principal', '=', '1');
            })
            ->leftJoin('rol', 'ur.id_rol', '=', 'rol.id_rol')
            ->leftJoin('empresa', 'ur.id_empresa', '=', 'empresa.id_empresa')
            ->where('usuario.estado', '1');

            // Filtros
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('usuario.dni', 'LIKE', "%{$search}%")
                      ->orWhere('usuario.nombres', 'LIKE', "%{$search}%")
                      ->orWhere('usuario.apellidoPaterno', 'LIKE', "%{$search}%")
                      ->orWhere('usuario.correo', 'LIKE', "%{$search}%")
                      ->orWhere('usuario.username', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('id_rol')) {
                $query->where('rol.id_rol', $request->input('id_rol'));
            }

            if ($request->has('id_empresa')) {
                $query->where('ur.id_empresa', $request->input('id_empresa'));
            }

            // Paginación
            $perPage = $request->input('per_page', 20);
            $usuarios = $query->orderBy('usuario.fechareg', 'desc')
                             ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $usuarios->items(),
                'pagination' => [
                    'current_page' => $usuarios->currentPage(),
                    'total_pages' => $usuarios->lastPage(),
                    'total_items' => $usuarios->total(),
                    'per_page' => $usuarios->perPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al listar usuarios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo usuario
     * POST /api/superadministrador/usuarios
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|size:8|unique:usuario,dni',
            'nombres' => 'required|string|max:60',
            'apellidoPaterno' => 'required|string|max:40',
            'apellidoMaterno' => 'required|string|max:40',
            'fechaNacimiento' => 'required|date',
            'genero' => 'required|string|in:Masculino,Femenino',
            'telefono' => 'required|string|max:15',
            'correo' => 'required|email|max:50|unique:usuario,correo',
            'direccion' => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:usuario,username',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|integer|exists:rol,id_rol',
            'id_empresa' => 'required|integer|exists:empresa,id_empresa',
        ], [
            'dni.required' => 'El DNI es obligatorio',
            'dni.size' => 'El DNI debe tener 8 dígitos',
            'dni.unique' => 'El DNI ya está registrado',
            'nombres.required' => 'Los nombres son obligatorios',
            'apellidoPaterno.required' => 'El apellido paterno es obligatorio',
            'apellidoMaterno.required' => 'El apellido materno es obligatorio',
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'El correo no es válido',
            'correo.unique' => 'El correo ya está registrado',
            'username.required' => 'El nombre de usuario es obligatorio',
            'username.unique' => 'El nombre de usuario ya existe',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'id_rol.required' => 'Debe seleccionar un rol',
            'id_rol.exists' => 'El rol seleccionado no existe',
            'id_empresa.required' => 'Debe seleccionar una sede',
            'id_empresa.exists' => 'La sede seleccionada no existe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Crear usuario
            $usuario = new Usuario();
            $usuario->dni = $request->dni;
            $usuario->nombres = $request->nombres;
            $usuario->apellidoPaterno = $request->apellidoPaterno;
            $usuario->apellidoMaterno = $request->apellidoMaterno;
            $usuario->fechaNacimiento = $request->fechaNacimiento;
            $usuario->genero = $request->genero;
            $usuario->telefono = $request->telefono;
            $usuario->correo = $request->correo;
            $usuario->direccion = $request->direccion;
            $usuario->username = $request->username;
            $usuario->password = Hash::make($request->password);
            $usuario->estado = '1';
            $usuario->fechareg = now();
            $usuario->fechamod = now();
            $usuario->save();

            // Crear registro en persona
            $persona = new Persona();
            $persona->id_persona = $usuario->id_usuario;
            $persona->id_empresa = $request->id_empresa;
            $persona->nombre = $request->nombres;
            $persona->apellido_paterno = $request->apellidoPaterno;
            $persona->apellido_materno = $request->apellidoMaterno;
            $persona->numero_documento = $request->dni;
            $persona->correo = $request->correo;
            $persona->telefono = $request->telefono;
            $persona->direccion = $request->direccion;
            $persona->sexo = $request->genero;
            $persona->fecha_nacimiento = $request->fechaNacimiento;
            $persona->estado = '1';
            $persona->nombre_completo = $request->nombres . ' ' . $request->apellidoPaterno . ' ' . $request->apellidoMaterno;
            $persona->save();

            // Asignar rol principal
            $usuarioRol = new UsuarioRol();
            $usuarioRol->id_empresa = $request->id_empresa;
            $usuarioRol->id_usuario = $usuario->id_usuario;
            $usuarioRol->id_rol = $request->id_rol;
            $usuarioRol->es_principal = '1';
            $usuarioRol->save();

            DB::commit();

            Log::info('Usuario creado exitosamente', [
                'id_usuario' => $usuario->id_usuario,
                'username' => $usuario->username,
                'id_rol' => $request->id_rol,
                'id_empresa' => $request->id_empresa,
                'created_by' => $request->sessionUser->id_usuario ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre_completo' => $persona->nombre_completo,
                    'username' => $usuario->username,
                    'rol' => Rol::find($request->id_rol)->nombre,
                    'sede' => Empresa::find($request->id_empresa)->razon_social,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear usuario: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de un usuario
     * GET /api/superadministrador/usuarios/{id}
     */
    public function show($id)
    {
        try {
            $usuario = Usuario::select(
                'usuario.*',
                DB::raw("CONCAT(usuario.nombres, ' ', usuario.apellidoPaterno, ' ', usuario.apellidoMaterno) as nombre_completo"),
                'persona.direccion as persona_direccion',
                'persona.foto',
                'empresa.razon_social as sede_nombre',
                'empresa.id_empresa',
                'rol.nombre as rol_nombre',
                'rol.codigo as rol_codigo',
                'rol.id_rol'
            )
            ->leftJoin('persona', 'usuario.id_usuario', '=', 'persona.id_persona')
            ->leftJoin('usuario_rol as ur', function($join) {
                $join->on('usuario.id_usuario', '=', 'ur.id_usuario')
                     ->where('ur.es_principal', '=', '1');
            })
            ->leftJoin('rol', 'ur.id_rol', '=', 'rol.id_rol')
            ->leftJoin('empresa', 'ur.id_empresa', '=', 'empresa.id_empresa')
            ->where('usuario.id_usuario', $id)
            ->where('usuario.estado', '1')
            ->first();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Obtener todos los roles del usuario
            $roles = UsuarioRol::where('id_usuario', $id)
                ->join('rol', 'usuario_rol.id_rol', '=', 'rol.id_rol')
                ->join('empresa', 'usuario_rol.id_empresa', '=', 'empresa.id_empresa')
                ->select('rol.id_rol', 'rol.nombre', 'rol.codigo', 'empresa.id_empresa', 'empresa.razon_social', 'usuario_rol.es_principal')
                ->get();

            $usuario->roles = $roles;

            return response()->json([
                'success' => true,
                'data' => $usuario
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar usuario
     * PUT /api/superadministrador/usuarios/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'sometimes|string|size:8|unique:usuario,dni,' . $id . ',id_usuario',
            'nombres' => 'sometimes|string|max:60',
            'apellidoPaterno' => 'sometimes|string|max:40',
            'apellidoMaterno' => 'sometimes|string|max:40',
            'fechaNacimiento' => 'sometimes|date',
            'genero' => 'sometimes|string|in:Masculino,Femenino',
            'telefono' => 'sometimes|string|max:15',
            'correo' => 'sometimes|email|max:50|unique:usuario,correo,' . $id . ',id_usuario',
            'direccion' => 'sometimes|string|max:100',
            'username' => 'sometimes|string|max:60|unique:usuario,username,' . $id . ',id_usuario',
            'estado' => 'sometimes|string|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $usuario = Usuario::find($id);
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Actualizar datos del usuario
            if ($request->has('dni')) $usuario->dni = $request->dni;
            if ($request->has('nombres')) $usuario->nombres = $request->nombres;
            if ($request->has('apellidoPaterno')) $usuario->apellidoPaterno = $request->apellidoPaterno;
            if ($request->has('apellidoMaterno')) $usuario->apellidoMaterno = $request->apellidoMaterno;
            if ($request->has('fechaNacimiento')) $usuario->fechaNacimiento = $request->fechaNacimiento;
            if ($request->has('genero')) $usuario->genero = $request->genero;
            if ($request->has('telefono')) $usuario->telefono = $request->telefono;
            if ($request->has('correo')) $usuario->correo = $request->correo;
            if ($request->has('direccion')) $usuario->direccion = $request->direccion;
            if ($request->has('username')) $usuario->username = $request->username;
            if ($request->has('estado')) $usuario->estado = $request->estado;
            
            $usuario->fechamod = now();
            $usuario->save();

            // Actualizar persona
            $persona = Persona::find($id);
            if ($persona) {
                if ($request->has('nombres')) $persona->nombre = $request->nombres;
                if ($request->has('apellidoPaterno')) $persona->apellido_paterno = $request->apellidoPaterno;
                if ($request->has('apellidoMaterno')) $persona->apellido_materno = $request->apellidoMaterno;
                if ($request->has('dni')) $persona->numero_documento = $request->dni;
                if ($request->has('correo')) $persona->correo = $request->correo;
                if ($request->has('telefono')) $persona->telefono = $request->telefono;
                if ($request->has('direccion')) $persona->direccion = $request->direccion;
                if ($request->has('genero')) $persona->sexo = $request->genero;
                if ($request->has('fechaNacimiento')) $persona->fecha_nacimiento = $request->fechaNacimiento;
                
                // Actualizar nombre completo
                $persona->nombre_completo = $usuario->nombres . ' ' . $usuario->apellidoPaterno . ' ' . $usuario->apellidoMaterno;
                $persona->save();
            }

            DB::commit();

            Log::info('Usuario actualizado exitosamente', [
                'id_usuario' => $id,
                'updated_by' => $request->sessionUser->id_usuario ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre_completo' => $persona->nombre_completo ?? null,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar usuario: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar usuario (soft delete - cambiar estado)
     * DELETE /api/superadministrador/usuarios/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Soft delete: cambiar estado a 0
            $usuario->estado = '0';
            $usuario->fechamod = now();
            $usuario->save();

            // Desactivar todas las sesiones activas
            DB::table('usuario_sesion')
                ->where('id_usuario', $id)
                ->update(['estado' => '0']);

            Log::info('Usuario eliminado (soft delete)', [
                'id_usuario' => $id,
                'deleted_by' => $request->sessionUser->id_usuario ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar rol a usuario
     * POST /api/superadministrador/usuarios/{id}/asignar-rol
     */
    public function asignarRol(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_rol' => 'required|integer|exists:rol,id_rol',
            'id_empresa' => 'required|integer|exists:empresa,id_empresa',
            'es_principal' => 'sometimes|boolean',
        ], [
            'id_rol.required' => 'Debe seleccionar un rol',
            'id_rol.exists' => 'El rol seleccionado no existe',
            'id_empresa.required' => 'Debe seleccionar una sede',
            'id_empresa.exists' => 'La sede seleccionada no existe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Si se marca como principal, desactivar otros roles principales
            if ($request->es_principal) {
                UsuarioRol::where('id_usuario', $id)
                    ->where('es_principal', '1')
                    ->update(['es_principal' => '0']);
            }

            // Verificar si ya existe esta combinación rol-sede
            $existingRol = UsuarioRol::where('id_usuario', $id)
                ->where('id_rol', $request->id_rol)
                ->where('id_empresa', $request->id_empresa)
                ->first();

            if ($existingRol) {
                // Actualizar rol existente
                $existingRol->es_principal = $request->es_principal ? '1' : $existingRol->es_principal;
                $existingRol->save();
            } else {
                // Crear nuevo rol
                $usuarioRol = new UsuarioRol();
                $usuarioRol->id_usuario = $id;
                $usuarioRol->id_rol = $request->id_rol;
                $usuarioRol->id_empresa = $request->id_empresa;
                $usuarioRol->es_principal = $request->es_principal ? '1' : '0';
                $usuarioRol->save();
            }

            Log::info('Rol asignado a usuario', [
                'id_usuario' => $id,
                'id_rol' => $request->id_rol,
                'id_empresa' => $request->id_empresa,
                'es_principal' => $request->es_principal ?? false,
                'assigned_by' => $request->sessionUser->id_usuario ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rol asignado exitosamente',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al asignar rol: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar sede de un usuario
     * POST /api/superadministrador/usuarios/{id}/cambiar-sede
     */
    public function cambiarSede(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_empresa' => 'required|integer|exists:empresa,id_empresa',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Actualizar sede en el rol principal
            UsuarioRol::where('id_usuario', $id)
                ->where('es_principal', '1')
                ->update(['id_empresa' => $request->id_empresa]);

            // Actualizar sede en persona
            Persona::where('id_persona', $id)
                ->update(['id_empresa' => $request->id_empresa]);

            DB::commit();

            Log::info('Sede cambiada para usuario', [
                'id_usuario' => $id,
                'new_id_empresa' => $request->id_empresa,
                'changed_by' => $request->sessionUser->id_usuario ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sede cambiada exitosamente',
                'data' => [
                    'id_empresa' => $request->id_empresa,
                    'sede' => Empresa::find($request->id_empresa)->razon_social,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar sede: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar sede',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetear contraseña de usuario
     * POST /api/superadministrador/usuarios/{id}/reset-password
     */
    public function resetPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'sometimes|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Generar contraseña aleatoria si no se proporciona
            $newPassword = $request->password ?? Str::random(8);
            
            $usuario->password = Hash::make($newPassword);
            $usuario->fechamod = now();
            $usuario->save();

            // Desactivar todas las sesiones activas (forzar re-login)
            DB::table('usuario_sesion')
                ->where('id_usuario', $id)
                ->update(['estado' => '0']);

            Log::info('Contraseña reseteada', [
                'id_usuario' => $id,
                'reset_by' => $request->sessionUser->id_usuario ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña reseteada exitosamente',
                'data' => [
                    'id_usuario' => $id,
                    'new_password' => $request->password ? null : $newPassword, // Solo mostrar si fue generada automáticamente
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al resetear contraseña: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al resetear contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar/Desactivar usuario
     * POST /api/superadministrador/usuarios/{id}/toggle-estado
     */
    public function toggleEstado(Request $request, $id)
    {
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $nuevoEstado = $usuario->estado === '1' ? '0' : '1';
            $usuario->estado = $nuevoEstado;
            $usuario->fechamod = now();
            $usuario->save();

            // Si se desactiva, cerrar sesiones
            if ($nuevoEstado === '0') {
                DB::table('usuario_sesion')
                    ->where('id_usuario', $id)
                    ->update(['estado' => '0']);
            }

            Log::info('Estado de usuario cambiado', [
                'id_usuario' => $id,
                'nuevo_estado' => $nuevoEstado,
                'changed_by' => $request->sessionUser->id_usuario ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => $nuevoEstado === '1' ? 'Usuario activado' : 'Usuario desactivado',
                'data' => [
                    'id_usuario' => $id,
                    'estado' => $nuevoEstado,
                    'estado_texto' => $nuevoEstado === '1' ? 'Activo' : 'Inactivo',
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar roles disponibles
     * GET /api/superadministrador/roles
     */
    public function listarRoles()
    {
        try {
            $roles = Rol::where('estado', '1')
                ->orWhereNull('estado')
                ->select('id_rol', 'nombre', 'codigo')
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $roles
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al listar roles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar sedes disponibles
     * GET /api/superadministrador/sedes
     */
    public function listarSedes()
    {
        try {
            $sedes = Empresa::where('estado', '1')
                ->orWhereNull('estado')
                ->select('id_empresa', 'razon_social', 'numero_documento')
                ->orderBy('razon_social')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $sedes
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al listar sedes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener sedes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}