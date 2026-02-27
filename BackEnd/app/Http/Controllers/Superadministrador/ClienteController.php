<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ClienteController — CRUD completo para la gestión de clientes del Superadministrador.
 * Los clientes son empresas/entidades que contratan los servicios o membresías de BigSei.
 */
class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('cliente as a')
            ->join('persona as b', 'a.id_cliente', 'b.id_persona')
            ->select(
                'a.id_cliente',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
                'a.ruc',
                'a.razon_social',
                'a.estado',
            );

        if (isset($request->text_search) && strlen($request->text_search) > 0) {
            $texto = str_replace(' ', '%', $request->text_search);
            $result->whereRaw(
                "upper(concat(b.nombre_completo, b.correo, a.ruc, a.razon_social)) LIKE upper(?)",
                ['%'.$texto.'%']
            );
        }
        $result->orderBy('b.nombre_completo', 'asc');
        return response()->json($result->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'required|string|max:255',
            'correo'          => 'required|email|max:100',
            'telefono'        => 'nullable|string|max:20',
            'ruc'             => 'nullable|string|max:15',
            'razon_social'    => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $id_persona = DB::table('persona')->insertGetId([
                'nombre_completo' => $request->nombre_completo,
                'correo'          => $request->correo,
                'telefono'        => $request->telefono ?? '',
            ]);
            DB::table('cliente')->insert([
                'id_cliente'   => $id_persona,
                'ruc'          => $request->ruc ?? '',
                'razon_social' => $request->razon_social ?? '',
                'estado'       => '1',
            ]);
            DB::commit();
            return response()->json(['message' => 'Cliente creado correctamente', 'id' => $id_persona], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear cliente: '.$e->getMessage()], 500);
        }
    }

    public function show($id_cliente)
    {
        $cliente = DB::table('cliente as a')
            ->join('persona as b', 'a.id_cliente', 'b.id_persona')
            ->select('a.id_cliente', 'b.nombre_completo', 'b.telefono', 'b.correo', 'a.ruc', 'a.razon_social', 'a.estado')
            ->where('a.id_cliente', $id_cliente)
            ->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        return response()->json($cliente);
    }

    public function update(Request $request, $id_cliente)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'sometimes|required|string|max:255',
            'correo'          => 'sometimes|required|email|max:100',
            'telefono'        => 'nullable|string|max:20',
            'ruc'             => 'nullable|string|max:15',
            'razon_social'    => 'nullable|string|max:255',
            'estado'          => 'sometimes|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existe = DB::table('cliente')->where('id_cliente', $id_cliente)->exists();
        if (!$existe) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $personaData = array_filter([
                'nombre_completo' => $request->nombre_completo ?? null,
                'correo'          => $request->correo ?? null,
                'telefono'        => $request->telefono ?? null,
            ], fn($v) => !is_null($v));

            if (!empty($personaData)) {
                DB::table('persona')->where('id_persona', $id_cliente)->update($personaData);
            }

            $clienteData = array_filter([
                'ruc'          => $request->ruc ?? null,
                'razon_social' => $request->razon_social ?? null,
                'estado'       => $request->estado ?? null,
            ], fn($v) => !is_null($v));

            if (!empty($clienteData)) {
                DB::table('cliente')->where('id_cliente', $id_cliente)->update($clienteData);
            }

            DB::commit();
            return response()->json(['message' => 'Cliente actualizado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar cliente: '.$e->getMessage()], 500);
        }
    }

    public function destroy($id_cliente)
    {
        $existe = DB::table('cliente')->where('id_cliente', $id_cliente)->exists();
        if (!$existe) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('cliente')->where('id_cliente', $id_cliente)->update(['estado' => '0']);
            DB::commit();
            return response()->json(['message' => 'Cliente desactivado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al desactivar cliente: '.$e->getMessage()], 500);
        }
    }
}
