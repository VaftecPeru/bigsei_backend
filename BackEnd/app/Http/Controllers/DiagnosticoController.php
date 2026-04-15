<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\DiagnosticoMedico;

class DiagnosticoController extends Controller
{
    public function index()
    {
        // ✅ Cargar relaciones completas
        $diagnosticos = DiagnosticoMedico::with([
            'cita.paciente',
            'cita.doctor',
            'recetas'
        ])
        ->orderBy('fecha', 'desc')
        ->get();

        return response()->json($diagnosticos);
    }
    
    // Registrar o actualizar diagnóstico (y opcionalmente receta)
    public function store(Request $request, $id_cita)
    {
        try {
            $request->validate([
                'descripcion' => 'required|string',
                'observaciones' => 'nullable|string',
                'fecha' => 'nullable|date',
                'indicaciones' => 'nullable|string',
            ]);

            $cita = Cita::findOrFail($id_cita);

            $diagnostico = DiagnosticoMedico::updateOrCreate(
                ['id_cita' => $id_cita],
                [
                    'descripcion' => $request->descripcion,
                    'observaciones' => $request->observaciones,
                    'fecha' => $request->fecha ?? $cita->fecha
                ]
            );

            // ✅ Crear receta solo si existe
            if ($request->filled('indicaciones')) {
                $diagnostico->recetas()->create([
                    'indicaciones' => $request->indicaciones
                ]);
            }

            // ✅ Marcar cita atendida
            $cita->estado = 'Atendida';
            $cita->save();

            // ✅ IMPORTANTE: cargar relaciones para respuesta
            return response()->json([
                'message' => 'Diagnóstico registrado correctamente',
                'diagnostico' => $diagnostico->load([
                    'cita.paciente',
                    'cita.doctor',
                    'recetas'
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error interno'
            ], 500);
        }
    }

    // Ver diagnóstico junto con recetas
    public function show($id_cita)
    {
        $diagnostico = DiagnosticoMedico::with([
            'cita.paciente',
            'cita.doctor',
            'recetas'
        ])
        ->where('id_cita', $id_cita)
        ->first();

        if (!$diagnostico) {
            return response()->json([
                'message' => 'No existe diagnóstico para esta cita'
            ], 404);
        }

        return response()->json($diagnostico);
    }
}