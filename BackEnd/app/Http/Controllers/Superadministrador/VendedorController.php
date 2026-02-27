<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendedorController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('vendedor as a')
            ->join('persona as b', 'a.id_vendedor', 'b.id_persona')
            ->select(
                'a.id_vendedor',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
                'a.estado',
            );
        if (isset($request->text_search)) {
            $texto = $request->text_search;
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(b.nombre_completo, b.telefono, b.correo)) LIKE upper(?)", ['%'.$texto.'%']);
        }
        $result->orderBy("nombre_completo", "asc");
        return response()->json($result->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'required|string|max:255',
            'telefono'        => 'nullable|string|max:20',
            'correo'          => 'required|email|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $id_persona = DB::table('persona')->insertGetId([
                'nombre_completo' => $request->nombre_completo,
                'telefono'        => $request->telefono ?? '',
                'correo'          => $request->correo,
            ]);
            DB::table('vendedor')->insert([
                'id_vendedor' => $id_persona,
                'estado'      => '1',
            ]);
            DB::commit();
            return response()->json(['message' => 'Vendedor creado correctamente', 'id' => $id_persona], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear vendedor: '.$e->getMessage()], 500);
        }
    }

    public function show($id_vendedor)
    {
        $vendedor = DB::table('vendedor as a')
            ->join('persona as b', 'a.id_vendedor', 'b.id_persona')
            ->select('a.id_vendedor', 'b.nombre_completo', 'b.telefono', 'b.correo', 'a.estado')
            ->where('a.id_vendedor', $id_vendedor)
            ->first();

        if (!$vendedor) {
            return response()->json(['error' => 'Vendedor no encontrado'], 404);
        }
        return response()->json($vendedor);
    }

    public function update(Request $request, $id_vendedor)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'sometimes|required|string|max:255',
            'telefono'        => 'nullable|string|max:20',
            'correo'          => 'sometimes|required|email|max:100',
            'estado'          => 'sometimes|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendedor = DB::table('vendedor')->where('id_vendedor', $id_vendedor)->first();
        if (!$vendedor) {
            return response()->json(['error' => 'Vendedor no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $personaData = array_filter([
                'nombre_completo' => $request->nombre_completo ?? null,
                'telefono'        => $request->telefono ?? null,
                'correo'          => $request->correo ?? null,
            ], fn($v) => !is_null($v));

            if (!empty($personaData)) {
                DB::table('persona')->where('id_persona', $id_vendedor)->update($personaData);
            }
            if ($request->has('estado')) {
                DB::table('vendedor')->where('id_vendedor', $id_vendedor)->update(['estado' => $request->estado]);
            }
            DB::commit();
            return response()->json(['message' => 'Vendedor actualizado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar vendedor: '.$e->getMessage()], 500);
        }
    }

    public function destroy($id_vendedor)
    {
        $vendedor = DB::table('vendedor')->where('id_vendedor', $id_vendedor)->first();
        if (!$vendedor) {
            return response()->json(['error' => 'Vendedor no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            // Desactivar en lugar de eliminar para mantener integridad referencial
            DB::table('vendedor')->where('id_vendedor', $id_vendedor)->update(['estado' => '0']);
            DB::commit();
            return response()->json(['message' => 'Vendedor desactivado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al desactivar vendedor: '.$e->getMessage()], 500);
        }
    }
}