<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use Carbon\Carbon;

class CitaController extends Controller
{
    // =========================
    // LISTAR
    // =========================
    public function index()
    {
        try {
            $citas = Cita::with(['paciente', 'doctor'])->get()->map(function ($cita) {
                return [
                    'id_cita' => $cita->id_cita,
                    'id_paciente' => $cita->id_paciente,
                    'id_doctor' => $cita->id_doctor,

                    'paciente_nombre' => $cita->paciente 
                        ? $cita->paciente->nombres . ' ' . $cita->paciente->apellidos 
                        : null,

                    'doctor_nombre' => $cita->doctor 
                        ? $cita->doctor->nombre . ' ' . $cita->doctor->apellido 
                        : null,

                    'fecha' => $cita->fecha 
                        ? Carbon::parse($cita->fecha)->format('d/m/Y') 
                        : null,

                    'hora_inicio' => $cita->hora_inicio 
                        ? substr($cita->hora_inicio, 0, 5) 
                        : null,

                    'hora_fin' => $cita->hora_fin 
                        ? substr($cita->hora_fin, 0, 5) 
                        : null,

                    'motivo' => $cita->motivo,
                    'estado' => $cita->estado
                ];
            });

            return response()->json($citas);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // CREAR
    // =========================
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_paciente' => 'required|integer',
                'id_doctor' => 'required|integer',
                'fecha' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i',
                'motivo' => 'nullable'
            ]);

            // Convertir correctamente la hora
            $horaInicio = \Carbon\Carbon::createFromFormat('H:i', $request->hora_inicio);
            $horaFin = $horaInicio->copy()->addMinutes(30);

            $cita = Cita::create([
                'id_paciente' => $request->id_paciente,
                'id_doctor' => $request->id_doctor,
                'fecha' => $request->fecha,
                'hora_inicio' => $horaInicio->format('H:i:s'),
                'hora_fin' => $horaFin->format('H:i:s'),
                'motivo' => $request->motivo,
                'estado' => 'Pendiente'
            ]);

            return response()->json($cita, 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // ACTUALIZAR
    // =========================
    public function update(Request $request, $id)
    {
        try {
            $cita = Cita::findOrFail($id);

            $request->validate([
                'id_paciente' => 'required|integer',
                'id_doctor' => 'required|integer',
                'fecha' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i',
                'motivo' => 'nullable'
            ]);

            $horaInicio = \Carbon\Carbon::createFromFormat('H:i', $request->hora_inicio);
            $horaFin = $horaInicio->copy()->addMinutes(30);

            $cita->update([
                'id_paciente' => $request->id_paciente,
                'id_doctor' => $request->id_doctor,
                'fecha' => $request->fecha,
                'hora_inicio' => $horaInicio->format('H:i:s'),
                'hora_fin' => $horaFin->format('H:i:s'),
                'motivo' => $request->motivo,
            ]);

            return response()->json([
                'message' => 'Actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // ELIMINAR
    // =========================
    public function destroy($id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $cita->delete();

            return response()->json(['message' => 'Eliminado correctamente']);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}