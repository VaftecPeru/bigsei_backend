<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use Exception;

class DoctorController extends Controller
{
    // 🔹 LISTAR
    public function index()
    {
        try {
            return response()->json(
                Doctor::with(['empresa', 'especialidad'])->get()
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener los doctores: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🔹 OBTENER UNO
    public function show($id)
    {
        try {
            return response()->json(
                Doctor::with(['empresa', 'especialidad'])->findOrFail($id)
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Doctor no encontrado: ' . $e->getMessage()
            ], 404);
        }
    }

    // 🔹 CREAR
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'dni' => 'required|size:8|unique:doctor,dni',
                'nombre' => 'required|string|max:255',
                'apellido' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'fecha_contratacion' => 'nullable|date',
                'estado' => 'nullable|string|max:20',

                // 🔹 CAMBIO IMPORTANTE: ahora es nullable (coherente con tu BD)
                'id_especialidad' => 'nullable|integer|exists:especialidades,id_especialidad',
                'id_empresa' => 'nullable|integer|exists:empresas,id_empresa',
            ]);

            $doctor = Doctor::create($validated);

            return response()->json($doctor, 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al crear el doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🔹 ACTUALIZAR
    public function update(Request $request, $id)
    {
        try {
            $doctor = Doctor::findOrFail($id);

            $validated = $request->validate([
                'dni' => 'required|size:8|unique:doctor,dni,' . $id . ',id_doctor',
                'nombre' => 'required|string|max:255',
                'apellido' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'fecha_contratacion' => 'nullable|date',
                'estado' => 'nullable|string|max:20',

                // 🔹 mismo cambio aquí
                'id_especialidad' => 'nullable|integer|exists:especialidades,id_especialidad',
                'id_empresa' => 'nullable|integer|exists:empresas,id_empresa',
            ]);

            $doctor->update($validated);

            return response()->json($doctor);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar el doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🔹 ELIMINAR
    public function destroy($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            $doctor->delete();

            return response()->json([
                'message' => 'Doctor eliminado correctamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar el doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🔹 CONTAR
    public function cantidad()
    {
        try {
            return response()->json([
                'cantidad' => Doctor::count()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al contar doctores: ' . $e->getMessage()
            ], 500);
        }
    }
}