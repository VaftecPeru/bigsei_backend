<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index()
    {
        return response()->json(Paciente::all());
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'dni' => 'required|digits:8|unique:paciente,dni',
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'fecha_nacimiento' => 'nullable|date',
                'sexo' => 'nullable|in:M,F,Otro',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'direccion' => 'nullable|string|max:150',
                'tipo_sangre' => 'nullable|string|max:5',
            ]);

            $paciente = Paciente::create($validatedData);

            return response()->json($paciente, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear paciente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            return response()->json(
                Paciente::findOrFail($id)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Paciente no encontrado',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $paciente = Paciente::findOrFail($id);

            $validatedData = $request->validate([
                'dni' => 'required|digits:8|unique:paciente,dni,' . $paciente->id_paciente . ',id_paciente',
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'fecha_nacimiento' => 'nullable|date',
                'sexo' => 'nullable|in:M,F,Otro',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'direccion' => 'nullable|string|max:150',
                'tipo_sangre' => 'nullable|string|max:5',
            ]);

            $paciente->update($validatedData);

            return response()->json($paciente);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar paciente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->delete();

            return response()->json([
                'message' => 'Paciente eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar paciente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}