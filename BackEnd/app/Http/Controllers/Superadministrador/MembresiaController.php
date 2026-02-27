<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * MembresiaController — Gestión de membresías de usuarios para el Superadministrador.
 */
class MembresiaController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('membresia as a')
            ->join('persona as b', 'a.id_persona', 'b.id_persona')
            ->join('membresia_tipo as c', 'a.id_membresiatipo', 'c.id_membresiatipo')
            ->select(
                'a.id_membresia',
                'b.nombre_completo',
                'b.correo',
                'b.telefono',
                'c.nombre as tipo_membresia',
                'c.precio',
                'a.fecha_inicio',
                'a.fecha_fin',
                'a.estado',
                'a.fechareg',
            );

        if (isset($request->text_search) && strlen($request->text_search) > 0) {
            $texto = str_replace(' ', '%', $request->text_search);
            $result->whereRaw(
                "upper(concat(b.nombre_completo, b.correo, c.nombre)) LIKE upper(?)",
                ['%' . $texto . '%']
            );
        }

        if (isset($request->estado) && $request->estado !== '') {
            $result->where('a.estado', $request->estado);
        }

        $result->orderBy('a.fechareg', 'desc');
        return response()->json($result->get());
    }

    public function show($id_membresia)
    {
        $membresia = DB::table('membresia as a')
            ->join('persona as b', 'a.id_persona', 'b.id_persona')
            ->join('membresia_tipo as c', 'a.id_membresiatipo', 'c.id_membresiatipo')
            ->select(
                'a.id_membresia',
                'a.id_persona',
                'b.nombre_completo',
                'b.correo',
                'a.id_membresiatipo',
                'c.nombre as tipo_membresia',
                'c.precio',
                'a.fecha_inicio',
                'a.fecha_fin',
                'a.estado',
            )
            ->where('a.id_membresia', $id_membresia)
            ->first();

        if (!$membresia) {
            return response()->json(['error' => 'Membresía no encontrada'], 404);
        }
        return response()->json($membresia);
    }

    public function activar($id_membresia)
    {
        $membresia = DB::table('membresia')->where('id_membresia', $id_membresia)->first();
        if (!$membresia) {
            return response()->json(['error' => 'Membresía no encontrada'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('membresia')->where('id_membresia', $id_membresia)->update(['estado' => '1']);
            DB::commit();
            return response()->json(['message' => 'Membresía activada correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al activar membresía: ' . $e->getMessage()], 500);
        }
    }

    public function desactivar($id_membresia)
    {
        $membresia = DB::table('membresia')->where('id_membresia', $id_membresia)->first();
        if (!$membresia) {
            return response()->json(['error' => 'Membresía no encontrada'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('membresia')->where('id_membresia', $id_membresia)->update(['estado' => '0']);
            DB::commit();
            return response()->json(['message' => 'Membresía desactivada correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al desactivar membresía: ' . $e->getMessage()], 500);
        }
    }

    public function stats()
    {
        $total        = DB::table('membresia')->count();
        $activas      = DB::table('membresia')->where('estado', '1')->where('fecha_fin', '>=', now())->count();
        $vencidas     = DB::table('membresia')->where('fecha_fin', '<', now())->count();
        $desactivadas = DB::table('membresia')->where('estado', '0')->count();

        return response()->json(compact('total', 'activas', 'vencidas', 'desactivadas'));
    }
}
