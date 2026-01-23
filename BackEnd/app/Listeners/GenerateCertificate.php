<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Models\Certificado;
use App\Models\PeriodoCurso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

/**
 * Listener que genera el certificado automáticamente cuando se dispara CourseCompleted
 * Hito 10: Generación de Certificado (PDF)
 */
class GenerateCertificate
{
    /**
     * Handle the event.
     */
    public function handle(CourseCompleted $event): void
    {
        try {
            $id_usuario = $event->id_usuario;
            $id_periodocurso = $event->id_periodocurso;

            // Verificar si ya existe un certificado
            $certificadoExistente = Certificado::where('id_usuario', $id_usuario)
                ->where('id_periodocurso', $id_periodocurso)
                ->first();

            if ($certificadoExistente) {
                Log::info("Certificado ya existe para usuario {$id_usuario}, curso {$id_periodocurso}");
                return;
            }

            // Obtener datos del curso y usuario
            $periodoCurso = PeriodoCurso::with(['curso', 'empresa'])->find($id_periodocurso);
            if (!$periodoCurso) {
                Log::error("Curso no encontrado: {$id_periodocurso}");
                return;
            }

            $usuario = DB::table('usuario')
                ->leftJoin('persona', 'usuario.id_usuario', '=', 'persona.id_persona')
                ->where('usuario.id_usuario', $id_usuario)
                ->select('usuario.*', 'persona.nombre', 'persona.apellido_paterno', 'persona.apellido_materno')
                ->first();

            if (!$usuario) {
                Log::error("Usuario no encontrado: {$id_usuario}");
                return;
            }

            // Generar código único
            $codigoCertificado = Certificado::generarCodigoUnico();
            $fechaEmision = Carbon::now();

            // Preparar datos del PDF
            $nombreArchivo = 'certificado_' . $codigoCertificado . '.pdf';
            $rutaArchivo = 'certificados/' . $nombreArchivo;

            $empresaNombre = $periodoCurso->empresa ? $periodoCurso->empresa->razon_social : 'BIGSEI';
            $nombreEstudiante = trim(($usuario->nombre ?? '') . ' ' . ($usuario->apellido_paterno ?? '') . ' ' . ($usuario->apellido_materno ?? ''));

            // Convertir logo a base64
            $logoPath = public_path('img/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoBase64 = 'data:image/png;base64,' . $logoData;
            }

            $dataPdf = [
                'nombre_estudiante' => $nombreEstudiante,
                'nombre_curso' => $periodoCurso->curso->nombre ?? 'Curso',
                'nombre_empresa' => $empresaNombre,
                'fecha_emision' => $fechaEmision->format('d \\d\\e F \\d\\e Y'),
                'codigo_certificado' => $codigoCertificado,
                'duracion_curso' => $periodoCurso->horas_semanal ?? '40',
                'es_sincrono' => $periodoCurso->es_sincrono,
                'logo_base64' => $logoBase64,
            ];

            // Generar PDF
            $pdf = Pdf::loadView('pdf.certificado', $dataPdf);
            $pdf->setPaper('landscape', 'A4');

            // Guardar el PDF
            $fullPath = storage_path('app/public/' . $rutaArchivo);
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }
            file_put_contents($fullPath, $pdf->output());

            // Crear registro del certificado
            Certificado::create([
                'id_usuario' => $id_usuario,
                'id_periodocurso' => $id_periodocurso,
                'codigo_certificado' => $codigoCertificado,
                'ruta_archivo' => $rutaArchivo,
                'nombre_archivo' => $nombreArchivo,
                'fecha_emision' => $fechaEmision,
                'estado' => true
            ]);

            Log::info("Certificado generado automáticamente: {$codigoCertificado} para usuario {$id_usuario}");

        } catch (\Exception $e) {
            Log::error("Error al generar certificado automático: " . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
