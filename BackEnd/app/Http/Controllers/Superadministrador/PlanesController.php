<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * PlanesController — Gestión de tipos de membresía y licencia para el Superadministrador.
 */
class PlanesController extends Controller
{
    // ── TIPOS DE MEMBRESÍA ──────────────────────────────────────────

    public function indexMembresiaTipo()
    {
        return response()->json(DB::table('membresia_tipo')->orderBy('precio', 'asc')->get());
    }

    public function storeMembresiaTipo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
            'duracion'    => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $id = DB::table('membresia_tipo')->insertGetId([
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion ?? '',
                'precio'      => $request->precio,
                'duracion'    => $request->duracion,
                'estado'      => '1',
                'fechareg'    => now(),
            ]);
            DB::commit();
            return response()->json(['message' => 'Tipo de membresía creado', 'id' => $id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    public function updateMembresiaTipo(Request $request, $id)
    {
        $tipo = DB::table('membresia_tipo')->where('id_membresiatipo', $id)->first();
        if (!$tipo) {
            return response()->json(['error' => 'Tipo de membresía no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $data = array_filter([
                'nombre'      => $request->nombre      ?? null,
                'descripcion' => $request->descripcion ?? null,
                'precio'      => $request->precio      ?? null,
                'duracion'    => $request->duracion     ?? null,
                'estado'      => $request->estado       ?? null,
            ], fn($v) => !is_null($v));

            DB::table('membresia_tipo')->where('id_membresiatipo', $id)->update($data);
            DB::commit();
            return response()->json(['message' => 'Tipo de membresía actualizado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    // ── TIPOS DE LICENCIA ────────────────────────────────────────────

    public function indexLicenciaTipo()
    {
        return response()->json(DB::table('licencia_tipo')->orderBy('precio', 'asc')->get());
    }

    public function storeLicenciaTipo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $id = DB::table('licencia_tipo')->insertGetId([
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion ?? '',
                'precio'      => $request->precio,
                'estado'      => '1',
            ]);
            DB::commit();
            return response()->json(['message' => 'Tipo de licencia creado', 'id' => $id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    public function updateLicenciaTipo(Request $request, $id)
    {
        $tipo = DB::table('licencia_tipo')->where('id_licenciatipo', $id)->first();
        if (!$tipo) {
            return response()->json(['error' => 'Tipo de licencia no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $data = array_filter([
                'nombre'      => $request->nombre      ?? null,
                'descripcion' => $request->descripcion ?? null,
                'precio'      => $request->precio      ?? null,
                'estado'      => $request->estado       ?? null,
            ], fn($v) => !is_null($v));

            DB::table('licencia_tipo')->where('id_licenciatipo', $id)->update($data);
            DB::commit();
            return response()->json(['message' => 'Tipo de licencia actualizado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }
}
