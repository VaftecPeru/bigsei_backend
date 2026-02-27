<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ConfigEmpresaController extends Controller
{
    /**
     * Obtener configuración de la empresa del admin actual.
     */
    public function show(Request $request)
    {
        $user = $request->sessionUser;
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        try {
            $empresa = DB::table('empresa')
                ->where('id_empresa', $user->id_empresa)
                ->first();

            if (!$empresa) {
                return response()->json(['error' => 'Empresa no encontrada'], 404);
            }

            return response()->json($empresa);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar configuración',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar configuración de la empresa (nombre, logo, etc.).
     */
    public function update(Request $request)
    {
        $user = $request->sessionUser;
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        try {
            $campos = [];

            if ($request->has('nombre')) {
                $campos['nombre'] = $request->nombre;
            }
            if ($request->has('ruc')) {
                $campos['ruc'] = $request->ruc;
            }
            if ($request->has('telefono')) {
                $campos['telefono'] = $request->telefono;
            }
            if ($request->has('direccion')) {
                $campos['direccion'] = $request->direccion;
            }
            if ($request->has('email')) {
                $campos['email'] = $request->email;
            }
            if ($request->has('web')) {
                $campos['web'] = $request->web;
            }

            if (empty($campos)) {
                return response()->json(['error' => 'No hay campos para actualizar'], 400);
            }

            DB::table('empresa')
                ->where('id_empresa', $user->id_empresa)
                ->update($campos);

            $empresa = DB::table('empresa')
                ->where('id_empresa', $user->id_empresa)
                ->first();

            return response()->json(['message' => 'Configuración actualizada', 'empresa' => $empresa]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar configuración',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas generales de la empresa.
     */
    public function stats(Request $request)
    {
        $user = $request->sessionUser;
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        try {
            $id_empresa = $user->id_empresa;

            $totalUsuarios = DB::table('usuario')
                ->where('id_empresa', $id_empresa)
                ->count();

            $totalEstudiantes = DB::table('estudiante as e')
                ->join('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->where('p.id_empresa', $id_empresa)
                ->count();

            $totalDocentes = DB::table('docente as d')
                ->join('persona as p', 'd.id_persona', '=', 'p.id_persona')
                ->where('p.id_empresa', $id_empresa)
                ->count();

            $membresiasActivas = DB::table('membresia as m')
                ->join('persona as p', 'm.id_persona', '=', 'p.id_persona')
                ->where('p.id_empresa', $id_empresa)
                ->where('m.estado', 1)
                ->count();

            return response()->json([
                'total_usuarios' => $totalUsuarios,
                'total_estudiantes' => $totalEstudiantes,
                'total_docentes' => $totalDocentes,
                'membresias_activas' => $membresiasActivas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar estadísticas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
