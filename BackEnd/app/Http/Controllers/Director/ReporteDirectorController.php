<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteDirectorController extends Controller
{
    /**
     * Task 2: Reporte de estudiantes en PDF
     */
    public function reporteEstudiantesPdf(Request $request)
    {
        $user = $request->sessionUser;
        $estudiantes = DB::table('estudiante as a')
            ->join('persona as b', 'a.id_estudiante', 'b.id_persona')
            ->select(
                'a.id_estudiante',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
                'b.numero_documento',
                DB::raw("CASE WHEN a.estado = '1' THEN 'Activo' ELSE 'Inactivo' END as estado")
            )
            ->orderBy('b.nombre_completo')
            ->get();

        // Generate simple HTML-based PDF
        $html = $this->generarHtmlReporte(
            'Reporte de Estudiantes',
            ['#', 'Nombre', 'Documento', 'Teléfono', 'Correo', 'Estado'],
            $estudiantes->map(function ($e, $i) {
                return [
                    $i + 1,
                    $e->nombre_completo,
                    $e->numero_documento,
                    $e->telefono,
                    $e->correo,
                    $e->estado,
                ];
            })->toArray()
        );

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="reporte_estudiantes.html"');
    }

    /**
     * Task 2: Reporte de estudiantes en Excel (CSV)
     */
    public function reporteEstudiantesExcel(Request $request)
    {
        $user = $request->sessionUser;
        $estudiantes = DB::table('estudiante as a')
            ->join('persona as b', 'a.id_estudiante', 'b.id_persona')
            ->select(
                'b.nombre_completo',
                'b.numero_documento',
                'b.telefono',
                'b.correo',
                DB::raw("CASE WHEN a.estado = '1' THEN 'Activo' ELSE 'Inactivo' END as estado")
            )
            ->orderBy('b.nombre_completo')
            ->get();

        $csv = "Nombre,Documento,Teléfono,Correo,Estado\n";
        foreach ($estudiantes as $e) {
            $csv .= "\"{$e->nombre_completo}\",\"{$e->numero_documento}\",\"{$e->telefono}\",\"{$e->correo}\",\"{$e->estado}\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="reporte_estudiantes.csv"');
    }

    /**
     * Task 2: Reporte de docentes en PDF
     */
    public function reporteDocentesPdf(Request $request)
    {
        $user = $request->sessionUser;
        $docentes = DB::table('docente as a')
            ->join('persona as b', 'a.id_docente', 'b.id_persona')
            ->select(
                'a.id_docente',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
                'b.numero_documento',
                DB::raw("CASE WHEN a.estado = '1' THEN 'Activo' ELSE 'Inactivo' END as estado")
            )
            ->orderBy('b.nombre_completo')
            ->get();

        $html = $this->generarHtmlReporte(
            'Reporte de Docentes',
            ['#', 'Nombre', 'Documento', 'Teléfono', 'Correo', 'Estado'],
            $docentes->map(function ($d, $i) {
                return [
                    $i + 1,
                    $d->nombre_completo,
                    $d->numero_documento,
                    $d->telefono,
                    $d->correo,
                    $d->estado,
                ];
            })->toArray()
        );

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="reporte_docentes.html"');
    }

    /**
     * Task 2: Reporte de docentes en Excel (CSV)
     */
    public function reporteDocentesExcel(Request $request)
    {
        $user = $request->sessionUser;
        $docentes = DB::table('docente as a')
            ->join('persona as b', 'a.id_docente', 'b.id_persona')
            ->select(
                'b.nombre_completo',
                'b.numero_documento',
                'b.telefono',
                'b.correo',
                DB::raw("CASE WHEN a.estado = '1' THEN 'Activo' ELSE 'Inactivo' END as estado")
            )
            ->orderBy('b.nombre_completo')
            ->get();

        $csv = "Nombre,Documento,Teléfono,Correo,Estado\n";
        foreach ($docentes as $d) {
            $csv .= "\"{$d->nombre_completo}\",\"{$d->numero_documento}\",\"{$d->telefono}\",\"{$d->correo}\",\"{$d->estado}\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="reporte_docentes.csv"');
    }

    /**
     * Task 2: Reporte de rendimiento (indicadores) en PDF
     */
    public function reporteRendimientoPdf(Request $request)
    {
        $user = $request->sessionUser;
        $id_empresa = $user->id_empresa;

        // Datos de rendimiento por carrera
        $carreras = DB::table('carrera as c')
            ->where('c.id_empresa', $id_empresa)
            ->select('c.id_carrera', 'c.nombre')
            ->get();

        $filas = [];
        $i = 1;
        foreach ($carreras as $carrera) {
            $promedioNota = DB::table('evaluacion_nota as en')
                ->join('periodo_curso as pc', 'en.id_periodocurso', 'pc.id_periodocurso')
                ->join('periodo_ciclo as pcl', 'pc.id_periodociclo', 'pcl.id_periodociclo')
                ->where('pcl.id_carrera', $carrera->id_carrera)
                ->where('pc.id_empresa', $id_empresa)
                ->avg('en.nota');

            $asistencia = DB::table('asistencia as a')
                ->join('periodo_curso as pc', 'a.id_periodocurso', 'pc.id_periodocurso')
                ->join('periodo_ciclo as pcl', 'pc.id_periodociclo', 'pcl.id_periodociclo')
                ->where('pcl.id_carrera', $carrera->id_carrera)
                ->where('pc.id_empresa', $id_empresa)
                ->where('a.tipo', 'E')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN a.estado = 'A' OR a.estado = 'T' THEN 1 ELSE 0 END) as asistieron
                ")
                ->first();

            $tasa_asistencia = $asistencia && $asistencia->total > 0
                ? round(($asistencia->asistieron / $asistencia->total) * 100, 1)
                : 0;

            $filas[] = [
                $i++,
                $carrera->nombre,
                $promedioNota ? round($promedioNota, 1) : '0',
                $tasa_asistencia . '%',
            ];
        }

        $html = $this->generarHtmlReporte(
            'Reporte de Rendimiento por Carrera',
            ['#', 'Carrera', 'Nota Promedio', 'Asistencia'],
            $filas
        );

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="reporte_rendimiento.html"');
    }

    /**
     * Helper: genera un HTML con tabla para impresión/descarga
     */
    private function generarHtmlReporte(string $titulo, array $encabezados, array $filas): string
    {
        $fecha = now()->format('d/m/Y H:i');
        $totalFilas = count($filas);
        $headersHtml = '';
        foreach ($encabezados as $h) {
            $headersHtml .= "<th style='border:1px solid #ddd;padding:8px;background:#1e3a5f;color:white;text-align:left;'>{$h}</th>";
        }

        $rowsHtml = '';
        foreach ($filas as $index => $fila) {
            $bgColor = $index % 2 === 0 ? '#ffffff' : '#f2f7fc';
            $rowsHtml .= "<tr style='background:{$bgColor};'>";
            foreach ($fila as $cell) {
                $rowsHtml .= "<td style='border:1px solid #ddd;padding:8px;'>" . htmlspecialchars($cell ?? '') . "</td>";
            }
            $rowsHtml .= '</tr>';
        }

        return "<!DOCTYPE html>
        <html>
        <head>
            <meta charset=\"UTF-8\">
            <title>{$titulo}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
                h1 { color: #1e3a5f; border-bottom: 3px solid #1e3a5f; padding-bottom: 10px; }
                .meta { color: #666; margin-bottom: 20px; }
                table { border-collapse: collapse; width: 100%; margin-top: 10px; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <h1>{$titulo}</h1>
            <p class=\"meta\">Generado el: {$fecha} | Total registros: <strong>{$totalFilas}</strong></p>
            <table>
                <thead><tr>{$headersHtml}</tr></thead>
                <tbody>{$rowsHtml}</tbody>
            </table>
        </body>
        </html>";
    }
}
