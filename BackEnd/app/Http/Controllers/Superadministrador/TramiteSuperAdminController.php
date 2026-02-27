<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tramite;

class TramiteSuperAdminController extends Controller
{
    // Listar trámites
    public function index()
    {
        $tramites = Tramite::with('usuario')->paginate(5);

        $tramites->getCollection()->transform(function ($t) {
            return [
                'idTramite' => $t->idTramite,
                'idUsuario' => $t->idUsuario,
                'tipo_tramite' => $t->tipo_tramite,
                'estado' => $t->estado,
                'fecha_solicitud' => $t->fecha_solicitud,
                'usuario' => $t->usuario
                    ? $t->usuario->nombres . ' ' . $t->usuario->apellidoPaterno
                    : 'Sin usuario'
            ];
        });

        return response()->json($tramites);
    }

    // Crear un trámite
    public function store(Request $request)
    {
        $request->validate([
            'idUsuario' => 'required|exists:usuario,id_usuario',
            'tipo_tramite' => 'required|string|max:255',
            'estado' => 'required|string',
        ]);

        $tramite = Tramite::create([
            'idUsuario' => $request->idUsuario,
            'tipo_tramite' => $request->tipo_tramite,
            'estado' => $request->estado,
            'fecha_solicitud' => now(),
        ]);

        return response()->json(['message' => 'Trámite creado', 'tramite' => $tramite]);
    }

    // Actualizar trámite
    public function update(Request $request, $id)
    {
        $tramite = Tramite::findOrFail($id);
        $tramite->update($request->only(['tipo_tramite', 'estado']));

        return response()->json(['message' => 'Trámite actualizado', 'tramite' => $tramite]);
    }

    // Eliminar trámite
    public function destroy($id)
    {
        $tramite = Tramite::findOrFail($id);
        $tramite->delete();

        return response()->json(['message' => 'Trámite eliminado']);
    }
}
