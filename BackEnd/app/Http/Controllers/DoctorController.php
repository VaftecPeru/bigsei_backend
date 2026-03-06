<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;

class DoctorController extends Controller
{
    public function index()
    {
        return Doctor::with('empresa')->get();
    }

    public function show($id)
    {
        return Doctor::with('empresa')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'fecha_contratacion' => 'nullable|date',
            'estado' => 'nullable|string|max:50',
            'id_empresa' => 'nullable|integer|exists:empresa,id_empresa',
        ]);

        $doctor = Doctor::create($validated);
        return response()->json($doctor, 201);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'fecha_contratacion' => 'nullable|date',
            'estado' => 'nullable|string|max:50',
            'id_empresa' => 'nullable|integer|exists:empresa,id_empresa',
        ]);

        $doctor->update($validated);
        return response()->json($doctor);
    }

    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();
        return response()->json(['message' => 'Doctor eliminado']);
    }
}