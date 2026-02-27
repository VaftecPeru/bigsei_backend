<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * LicenciaController — Gestión de licencias de empresas para el Superadministrador.
 */
class LicenciaController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('licencia as a')
            ->join('empresa as b', 'a.id_empresa', 'b.id_empresa')
            ->join('licencia_tipo as c', 'a.id_licenciatipo', 'c.id_licenciatipo')
            ->select(
                'a.id_licencia',
                'b.razon_social',
                'b.numero_documento',
                'c.nombre as tipo_licencia',
                'c.precio',
                'a.fecha_inicio',
                'a.fecha_fin',
                'a.estado',
                'a.fechareg',
            );

        if (isset($request->text_search) && strlen($request->text_search) > 0) {
            $texto = str_replace(' ', '%', $request->text_search);
            $result->whereRaw(
                "upper(concat(b.razon_social, b.numero_documento, c.nombre)) LIKE upper(?)",
                ['%' . $texto . '%']
            );
        }

        if (isset($request->estado) && $request->estado !== '') {
            $result->where('a.estado', $request->estado);
        }

        $result->orderBy('a.fechareg', 'desc');
        return response()->json($result->get());
    }

    public function show($id_licencia)
    {
        $licencia = DB::table('licencia as a')
            ->join('empresa as b', 'a.id_empresa', 'b.id_empresa')
            ->join('licencia_tipo as c', 'a.id_licenciatipo', 'c.id_licenciatipo')
            ->select(
                'a.id_licencia',
                'a.id_empresa',
                'b.razon_social',
                'b.numero_documento',
                'a.id_licenciatipo',
                'c.nombre as tipo_licencia',
                'c.precio',
                'a.fecha_inicio',
                'a.fecha_fin',
                'a.estado',
            )
            ->where('a.id_licencia', $id_licencia)
            ->first();

        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }
        return response()->json($licencia);
    }

    public function activar($id_licencia)
    {
        $licencia = DB::table('licencia')->where('id_licencia', $id_licencia)->first();
        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('licencia')
                ->where('id_licencia', $id_licencia)
                ->update([
                    'estado' => '1',
                    'fecha_inicio' => now(),
                    'fecha_fin' => now()->addYear(),
                ]);
            DB::commit();
            return response()->json(['message' => 'Licencia activada correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al activar licencia: ' . $e->getMessage()], 500);
        }
    }

    public function desactivar($id_licencia)
    {
        $licencia = DB::table('licencia')->where('id_licencia', $id_licencia)->first();
        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('licencia')->where('id_licencia', $id_licencia)->update(['estado' => '0']);
            DB::commit();
            return response()->json(['message' => 'Licencia desactivada correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al desactivar licencia: ' . $e->getMessage()], 500);
        }
    }

    public function renovar(Request $request, $id_licencia)
    {
        $validator = Validator::make($request->all(), [
            'meses' => 'sometimes|integer|min:1|max:60',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $licencia = DB::table('licencia')->where('id_licencia', $id_licencia)->first();
        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }

        DB::beginTransaction();
        try {
            $meses = $request->meses ?? 12;
            $nuevaFechaFin = now()->addMonths($meses);

            DB::table('licencia')->where('id_licencia', $id_licencia)->update([
                'estado' => '1',
                'fecha_fin' => $nuevaFechaFin,
            ]);
            DB::commit();
            return response()->json([
                'message' => 'Licencia renovada por ' . $meses . ' mes(es)',
                'fecha_fin' => $nuevaFechaFin,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al renovar licencia: ' . $e->getMessage()], 500);
        }
    }

    public function stats()
    {
        $total        = DB::table('licencia')->count();
        $activas      = DB::table('licencia')->where('estado', '1')->where('fecha_fin', '>=', now())->count();
        $vencidas     = DB::table('licencia')->where('fecha_fin', '<', now())->count();
        $desactivadas = DB::table('licencia')->where('estado', '0')->count();

        return response()->json(compact('total', 'activas', 'vencidas', 'desactivadas'));
    }
}
