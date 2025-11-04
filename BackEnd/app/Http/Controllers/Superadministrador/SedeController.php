<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use App\Models\UsuarioSede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Usuario;
use App\Models\Sedes;
use App\Models\CursoAsistencia;
use Carbon\Carbon;

class SedeController extends Controller
{
    public function obtenerReporteUsuarioPorSede(Request $request)
    {
        $idUsuario = $request->input('idUsuario');

        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuarios,idUsuario',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Obtener datos del estudiante
        $estudiante = Usuario::with(['rol', 'sedes'])
            ->findOrFail($idUsuario);

        // Obtener datos del padre/madre (asumiendo relación)
        $padre = Usuario::whereHas('rol', function ($q) {
            $q->where('nombreRol', 'Padre'); // Ajusta según tu estructura
        })
            ->where('idEstudiante', $idUsuario) // Ajusta según tu relación
            ->first();

        // Obtener datos del profesor (asumiendo relación a través de sede)
        $profesor = Usuario::whereHas('rol', function ($q) {
            $q->where('nombreRol', 'Profesor');
        })
            ->whereHas('sedes', function ($q) use ($estudiante) {
                $q->whereIn('idSede', $estudiante->sedes->pluck('idSede'));
            })
            ->first();

        // Obtener sede principal (asumiendo primera sede)
        $sede = $estudiante->sedes->first();

        $data = [
            'estudiante' => $estudiante,
            'padre' => $padre,
            'profesor' => $profesor,
            'sede' => $sede,
            'fecha' => now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('exports.reporte_usuario_sede', $data);

        return $pdf->download('reporte_estudiante_' . $estudiante->id . '.pdf');
    }

    public function obtenerReportePorSede(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idSede' => 'required|integer|exists:sedes,idSede',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio'
        ], [
            'idSede.required' => 'El ID de la sede es requerido',
            'idSede.exists' => 'La sede no existe',
            'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $idSede = $request->input('idSede');
        $fechaInicio = $request->input('fecha_inicio') ? Carbon::parse($request->input('fecha_inicio')) : now()->startOfMonth();
        $fechaFin = $request->input('fecha_fin') ? Carbon::parse($request->input('fecha_fin')) : now()->endOfMonth();

        // Obtener datos de la sede
        $sede = Sedes::findOrFail($idSede);

        // Obtener estudiantes de la sede
        $estudiantes = Usuario::whereHas('sedes', function ($query) use ($idSede) {
            $query->where('idSede', $idSede);
        })->whereHas('rol', function ($query) {
            $query->where('nombreRol', 'Estudiante'); // Ajusta según tu estructura
        })->get();

        // Calcular asistencia por estudiante
        $reporte = [];
        $totalAsistencias = 0;
        $totalRegistros = 0;

        foreach ($estudiantes as $estudiante) {
            // Obtener asistencias del estudiante a través de CursoAsistencia
            $asistencias = CursoAsistencia::whereHas('cursoEstudiantes', function ($query) use ($estudiante) {
                $query->where('idUsuario', $estudiante->idUsuario);
            })
                ->whereBetween('fechaRegistro', [$fechaInicio, $fechaFin])
                ->get();

            $presentes = $asistencias->where('estado', 'presente')->count();
            $total = $asistencias->count();
            $porcentaje = $total > 0 ? round(($presentes / $total) * 100, 2) : 0;

            $reporte[] = [
                'estudiante' => $estudiante,
                'presentes' => $presentes,
                'ausentes' => $total - $presentes,
                'total' => $total,
                'porcentaje' => $porcentaje
            ];

            $totalAsistencias += $presentes;
            $totalRegistros += $total;
        }

        // Calcular porcentaje general
        $porcentajeGeneral = $totalRegistros > 0 ? round(($totalAsistencias / $totalRegistros) * 100, 2) : 0;

        $data = [
            'sede' => $sede,
            'reporte' => $reporte,
            'fechaInicio' => $fechaInicio->format('d/m/Y'),
            'fechaFin' => $fechaFin->format('d/m/Y'),
            'porcentajeGeneral' => $porcentajeGeneral,
            'totalAsistencias' => $totalAsistencias,
            'totalRegistros' => $totalRegistros,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('exports.asistencia_sede', $data);

        return $pdf->download('reporte_asistencia_' . $sede->nombreSede . '_' . now()->format('Ymd') . '.pdf');
    }

    public function descargarMatriculasPorSede()
    {
        $sedes = Sedes::with(['matriculas.usuario', 'matriculas.matriculaCursos.curso'])
                     ->get();

        $pdf = PDF::loadView('exports.matriculas_por_sede', compact('sedes'));

        return $pdf->download('matriculas_por_sede.pdf');
    }
}
