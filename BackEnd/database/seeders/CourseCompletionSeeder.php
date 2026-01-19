<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodoCurso;
use App\Models\PeriodoVideo;
use App\Models\ProgresoUsuarioContenido;
use App\Models\User; // Ajustar si el modelo de usuario es diferente

class CourseCompletionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ID del usuario a testear (o iterar todos)
        $userId = 1; 
        
        // Verificar si el usuario existe
        // $user = User::find($userId); 
        // if (!$user) { ... }

        $cursos = PeriodoCurso::all();

        foreach ($cursos as $curso) {
            $this->command->info("Completando curso ID: {$curso->id_periodocurso} para el usuario ID: {$userId}...");

            // Obtener todos los videos del curso (a través de módulos y temas)
            // Una forma eficiente es hacer un join o iterar. Iteraremos para simplicidad del seeder.
            
            $videos = \Illuminate\Support\Facades\DB::table('periodo_video')
                ->join('periodo_tema', 'periodo_video.id_periodotema', '=', 'periodo_tema.id_periodotema')
                ->join('periodo_modulo', 'periodo_tema.id_periodomodulo', '=', 'periodo_modulo.id_periodomodulo')
                ->where('periodo_modulo.id_periodocurso', $curso->id_periodocurso)
                ->select('periodo_video.id_periodovideo')
                ->get();

            if ($videos->isEmpty()) {
                $this->command->warn("  - El curso ID {$curso->id_periodocurso} no tiene videos. Ejecuta CourseContentSeeder primero.");
                continue;
            }

            foreach ($videos as $video) {
                ProgresoUsuarioContenido::updateOrCreate(
                    [
                        'id_usuario' => $userId,
                        'id_periodocurso' => $curso->id_periodocurso,
                        'tipo_contenido' => 'video',
                        'id_contenido' => $video->id_periodovideo
                    ],
                    [
                        'completado' => true,
                        'fecha_completado' => now(),
                        'fechareg' => now()
                    ]
                );
            }
            
            $this->command->info("  - Curso marcado como 100% completado.");
        }
    }
}
