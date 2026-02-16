<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Pendiente;
use Illuminate\Http\Request;

class PendienteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->sessionUser;
        $result = Pendiente::where('id_usuario', $user->id_usuario)
            ->where('id_empresa', $user->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $user = $request->sessionUser;

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'fecha_limite' => 'nullable|date',
        ]);

        $pendiente = Pendiente::create([
            'id_usuario' => $user->id_usuario,
            'id_empresa' => $user->id_empresa,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'prioridad' => $request->prioridad,
            'fecha_limite' => $request->fecha_limite,
        ]);

        return response()->json($pendiente, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->sessionUser;

        $pendiente = Pendiente::where('id_pendiente', $id)
            ->where('id_usuario', $user->id_usuario)
            ->firstOrFail();

        $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => 'sometimes|in:Alta,Media,Baja',
            'completado' => 'sometimes|boolean',
            'fecha_limite' => 'nullable|date',
        ]);

        $pendiente->update($request->only([
            'titulo', 'descripcion', 'prioridad', 'completado', 'fecha_limite'
        ]));

        return response()->json($pendiente);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->sessionUser;

        $pendiente = Pendiente::where('id_pendiente', $id)
            ->where('id_usuario', $user->id_usuario)
            ->firstOrFail();

        $pendiente->delete();

        return response()->json(['message' => 'Pendiente eliminado']);
    }
}
