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
        $validatedData = $request->validate([
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
    }

    public function show($id)
    {
        $paciente = Paciente::findOrFail($id);
        return response()->json($paciente);
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);

        $validatedData = $request->validate([
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
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();
        return response()->json(['message' => 'Paciente eliminado'], 204);
    }
}