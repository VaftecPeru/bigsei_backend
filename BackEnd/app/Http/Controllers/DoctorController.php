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
            return Doctor::with('empresa', 'especialidad')->get();
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo obtener los doctores: ' . $e->getMessage()], 500);
        }
    }

    // 🔹 OBTENER UNO (con su especialidad y empresa)
    public function show($id)
    {
        try {
            return Doctor::with('empresa', 'especialidad')->findOrFail($id);
        } catch (Exception $e) {
            return response()->json(['error' => 'Doctor no encontrado: ' . $e->getMessage()], 404);
        }
    }

    // 🔹 CREAR
    public function store(Request $request)
    {
        try {
            // Validación de los datos
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido' => 'nullable|string|max:255',
                'id_especialidad' => 'required|integer|exists:especialidades,id_especialidad', // Asegurarse que la especialidad exista
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'fecha_contratacion' => 'nullable|date',
                'estado' => 'nullable|string|max:50',
                'id_empresa' => 'nullable|integer|exists:empresas,id_empresa',
            ]);

            // Creación del doctor con los datos validados
            $doctor = Doctor::create($validated);

            // Devolver la respuesta con el doctor creado
            return response()->json($doctor, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al crear el doctor: ' . $e->getMessage()], 500);
        }
    }

    // 🔹 ACTUALIZAR
    public function update(Request $request, $id)
    {
        try {
            $doctor = Doctor::findOrFail($id);

            // Validación de los datos
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido' => 'nullable|string|max:255',
                'id_especialidad' => 'required|integer|exists:especialidades,id_especialidad', // Asegurarse que la especialidad exista
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'fecha_contratacion' => 'nullable|date',
                'estado' => 'nullable|string|max:50',
                'id_empresa' => 'nullable|integer|exists:empresas,id_empresa',
            ]);

            // Actualizar el doctor con los datos validados
            $doctor->update($validated);

            return response()->json($doctor);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al actualizar el doctor: ' . $e->getMessage()], 500);
        }
    }

    // 🔹 ELIMINAR
    public function destroy($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            $doctor->delete();

            return response()->json(['message' => 'Doctor eliminado']);
        } catch (Exception $e) {
            return response()->json(['error' =>     'Error al eliminar el doctor: ' . $e->getMessage()], 500);
        }
    }

    // CONTAR
    public function cantidad()
    {
        try {
            $cantidad = Doctor::count();

            return response()->json([
                'cantidad' => $cantidad
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al contar doctores: ' . $e->getMessage()
            ], 500);
        }
    }
}