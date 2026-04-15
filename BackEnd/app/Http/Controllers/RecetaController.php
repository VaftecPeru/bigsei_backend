<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\RecetaMedica;

class RecetaController extends Controller
{
    // Registrar o actualizar receta
    public function store(Request $request, $id_cita)
    {
        $request->validate([
            'indicaciones' => 'required|string',
        ]);

        $cita = Cita::findOrFail($id_cita);

        $receta = RecetaMedica::updateOrCreate(
            ['id_cita' => $id_cita],
            ['indicaciones' => $request->indicaciones]
        );

        // Marcar cita como atendida
        $cita->estado = 'Atendida';
        $cita->save();

        return response()->json([
            'message' => 'Receta registrada correctamente',
            'receta' => $receta
        ]);
    }

    // Ver receta
    public function show($id_cita)
    {
        $receta = RecetaMedica::where('id_cita', $id_cita)->first();

        if (!$receta) {
            return response()->json(['message' => 'No existe receta para esta cita'], 404);
        }

        return response()->json($receta);
    }
}