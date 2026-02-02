<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * RoleSeeder - Crea los 11 roles del sistema BIGSEI
 * 
 * Roles:
 * 1. SuperAdministrador - Acceso total a todas las sedes
 * 2. Administrador - Gestión de una sede específica
 * 3. Director - Vista de reportes y estadísticas
 * 4. Docente - Gestión académica y notas
 * 5. Estudiante - Acceso a cursos y materiales
 * 6. Padre - Vista de hijos y pagos
 * 7. Contador - Gestión financiera
 * 8. Bibliotecario - Gestión de biblioteca
 * 9. Tópico Médico - Atención médica
 * 10. Vendedor - Ventas y matrículas
 * 11. Tutor - Acompañamiento académico
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id_rol' => 1,
                'nombre' => 'SuperAdministrador',
                'codigo' => 'superadministrador',
                'descripcion' => 'Acceso total al sistema, gestión de todas las sedes y usuarios',
                'nivel' => 1,
            ],
            [
                'id_rol' => 2,
                'nombre' => 'Administrador',
                'codigo' => 'admin',
                'descripcion' => 'Gestión de una sede específica, usuarios, cursos y matrículas',
                'nivel' => 2,
            ],
            [
                'id_rol' => 3,
                'nombre' => 'Director',
                'codigo' => 'director',
                'descripcion' => 'Vista de reportes, estadísticas y supervisión académica',
                'nivel' => 3,
            ],
            [
                'id_rol' => 4,
                'nombre' => 'Docente',
                'codigo' => 'docente',
                'descripcion' => 'Gestión de cursos, notas, asistencias y contenido académico',
                'nivel' => 4,
            ],
            [
                'id_rol' => 5,
                'nombre' => 'Estudiante',
                'codigo' => 'student',
                'descripcion' => 'Acceso a cursos, materiales, notas y progreso académico',
                'nivel' => 5,
            ],
            [
                'id_rol' => 6,
                'nombre' => 'Padre de Familia',
                'codigo' => 'padre',
                'descripcion' => 'Vista de hijos, pagos, notas y comunicación con docentes',
                'nivel' => 5,
            ],
            [
                'id_rol' => 7,
                'nombre' => 'Contador',
                'codigo' => 'contador',
                'descripcion' => 'Gestión financiera, pagos, facturación y reportes contables',
                'nivel' => 4,
            ],
            [
                'id_rol' => 8,
                'nombre' => 'Bibliotecario',
                'codigo' => 'bibliotecario',
                'descripcion' => 'Gestión de biblioteca, préstamos, reservas y catálogo',
                'nivel' => 4,
            ],
            [
                'id_rol' => 9,
                'nombre' => 'Tópico Médico',
                'codigo' => 'topicomedico',
                'descripcion' => 'Atención médica, historiales y reportes de salud',
                'nivel' => 4,
            ],
            [
                'id_rol' => 10,
                'nombre' => 'Vendedor',
                'codigo' => 'vendedor',
                'descripcion' => 'Ventas, matrículas, atención al cliente y seguimiento',
                'nivel' => 4,
            ],
            [
                'id_rol' => 11,
                'nombre' => 'Tutor',
                'codigo' => 'tutor',
                'descripcion' => 'Acompañamiento académico y seguimiento de estudiantes',
                'nivel' => 5,
            ],
        ];

        foreach ($roles as $rol) {
            // Solo usar columnas que existen en la tabla: id_rol, nombre, codigo
            DB::table('rol')->updateOrInsert(
                ['id_rol' => $rol['id_rol']],
                [
                    'nombre' => $rol['nombre'],
                    'codigo' => $rol['codigo'],
                ]
            );
        }

        $this->command->info('✅ Roles creados/actualizados exitosamente: ' . count($roles) . ' roles');
    }
}
