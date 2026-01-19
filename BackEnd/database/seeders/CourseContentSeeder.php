<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodoCurso;
use App\Models\PeriodoModulo;
use App\Models\PeriodoTema;
use App\Models\PeriodoVideo;
use Illuminate\Support\Facades\DB;

class CourseContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cursos = PeriodoCurso::all();

        if ($cursos->isEmpty()) {
            $this->command->info('No hay cursos (PeriodoCurso) encontrados. Asegúrate de tener cursos creados primero.');
            return;
        }

        foreach ($cursos as $curso) {
            $this->command->info("Generando contenido para el curso ID: {$curso->id_periodocurso}...");

            // Crear 3 Módulos por defecto si no existen
            $modulosBase = [
                ['titulo' => 'Módulo 1: Introducción y Fundamentos', 'orden' => 1],
                ['titulo' => 'Módulo 2: Desarrollo y Aplicación', 'orden' => 2],
                ['titulo' => 'Módulo 3: Proyecto Final y Evaluación', 'orden' => 3],
            ];

            foreach ($modulosBase as $modData) {
                $modulo = PeriodoModulo::firstOrCreate(
                    [
                        'id_periodocurso' => $curso->id_periodocurso,
                        'titulo' => $modData['titulo']
                    ],
                    [
                        'id_empresa' => 1, // Ajustar según tu lógica multi-empresa
                        'orden' => $modData['orden'],
                        'descripcion' => 'Contenido generado automáticamente por el Seeder.',
                        'fechareg' => now(),
                        'id_usuarioreg' => 1
                    ]
                );

                // Crear 2 Temas por Módulo
                $temasBase = [
                    ['titulo' => "Tema A: Conceptos Clave de {$modData['titulo']}"],
                    ['titulo' => "Tema B: Práctica de {$modData['titulo']}"],
                ];

                foreach ($temasBase as $temaData) {
                    $tema = PeriodoTema::firstOrCreate(
                        [
                            'id_periodomodulo' => $modulo->id_periodomodulo,
                            'titulo' => $temaData['titulo']
                        ],
                        [
                            'id_empresa' => 1,
                            'descripcion' => 'Tema introductorio con material audiovisual.',
                            'fechareg' => now(),
                            'id_usuarioreg' => 1
                        ]
                    );

                    // Crear 1 Video por Tema (Necesario para el cálculo de progreso)
                    PeriodoVideo::firstOrCreate(
                        [
                            'id_periodotema' => $tema->id_periodotema,
                            'nombre' => "Video Clase: {$temaData['titulo']}"
                        ],
                        [
                            'id_empresa' => 1,
                            'descripcion' => 'Video explicativo de la lección.',
                            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', // Dummy URL
                            'fechareg' => now(),
                            'id_usuarioreg' => 1
                        ]
                    );
                }
            }
        }
    }
}
