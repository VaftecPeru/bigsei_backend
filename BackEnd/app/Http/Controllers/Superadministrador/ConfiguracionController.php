<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ConfiguracionController — Configuración global del sistema para el Superadministrador.
 * Lee/guarda configuraciones en tabla `configuracion` (clave-valor).
 * Si la tabla no existe, responde con datos por defecto sin romper nada.
 */
class ConfiguracionController extends Controller
{
    public function index()
    {
        try {
            $configs = DB::table('configuracion')->get()->keyBy('clave');
            return response()->json($configs);
        } catch (\Exception $e) {
            // Si la tabla no existe aún, devolver configuración por defecto
            return response()->json([
                'sistema_nombre'    => (object)['clave' => 'sistema_nombre',    'valor' => 'BigSei'],
                'sistema_url'       => (object)['clave' => 'sistema_url',       'valor' => 'https://bigsei.com'],
                'sistema_correo'    => (object)['clave' => 'sistema_correo',    'valor' => 'admin@bigsei.com'],
                'modo_mantenimiento'=> (object)['clave' => 'modo_mantenimiento','valor' => '0'],
            ]);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'configuraciones' => 'required|array',
            'configuraciones.*.clave' => 'required|string|max:100',
            'configuraciones.*.valor' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            foreach ($request->configuraciones as $config) {
                DB::table('configuracion')->updateOrInsert(
                    ['clave' => $config['clave']],
                    ['valor' => $config['valor'] ?? '', 'fechamod' => now()]
                );
            }
            DB::commit();
            return response()->json(['message' => 'Configuración guardada correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al guardar configuración: ' . $e->getMessage()], 500);
        }
    }

    public function stats()
    {
        $totalEmpresas   = DB::table('empresa')->count();
        $totalUsuarios   = DB::table('usuario')->where('estado', '1')->count();
        $totalLicencias  = DB::table('licencia')->where('estado', '1')->count();
        $totalMembresias = DB::table('membresia')->where('estado', '1')->count();

        return response()->json(compact('totalEmpresas', 'totalUsuarios', 'totalLicencias', 'totalMembresias'));
    }
}
