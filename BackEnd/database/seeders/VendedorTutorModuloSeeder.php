<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * VendedorTutorModuloSeeder - Crea módulos de navegación para Vendedor y Tutor
 * 
 * Ejecutar: php artisan db:seed --class=VendedorTutorModuloSeeder
 */
class VendedorTutorModuloSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // MÓDULOS PARA VENDEDOR (id_rol = 10)
        // ============================================================
        $vendedorModulos = [
            [
                'nombre' => 'Dashboard',
                'url' => '/vendedor',
                'url_activa' => '1',
                'icon' => 'Home',
                'orden' => 1,
                'estado' => '1',
                'id_modulosup' => null,
            ],
            [
                'nombre' => 'Mis Clientes',
                'url' => '/vendedor/clientes',
                'url_activa' => '0',
                'icon' => 'Building',
                'orden' => 2,
                'estado' => '1',
                'id_modulosup' => null,
            ],
            [
                'nombre' => 'Suscripciones',
                'url' => '/vendedor/suscripciones',
                'url_activa' => '0',
                'icon' => 'ReceiptText',
                'orden' => 3,
                'estado' => '1',
                'id_modulosup' => null,
            ],
            [
                'nombre' => 'Renovaciones',
                'url' => '/vendedor/renovaciones',
                'url_activa' => '0',
                'icon' => 'AlarmClockCheck',
                'orden' => 4,
                'estado' => '1',
                'id_modulosup' => null,
            ],
            [
                'nombre' => 'Comisiones',
                'url' => '/vendedor/comisiones',
                'url_activa' => '0',
                'icon' => 'Receipt',
                'orden' => 5,
                'estado' => '1',
                'id_modulosup' => null,
            ],
        ];

        // ============================================================
        // MÓDULOS PARA TUTOR (id_rol = 11)
        // ============================================================
        $tutorModulos = [
            [
                'nombre' => 'Dashboard',
                'url' => '/tutor',
                'url_activa' => '1',
                'icon' => 'Home',
                'orden' => 1,
                'estado' => '1',
                'id_modulosup' => null,
            ],
            [
                'nombre' => 'Mis Estudiantes',
                'url' => '/tutor/estudiantes',
                'url_activa' => '0',
                'icon' => 'GraduationCap',
                'orden' => 2,
                'estado' => '1',
                'id_modulosup' => null,
            ],
            [
                'nombre' => 'Agenda',
                'url' => '/tutor/agenda',
                'url_activa' => '0',
                'icon' => 'CalendarDays',
                'orden' => 3,
                'estado' => '1',
                'id_modulosup' => null,
            ],
            [
                'nombre' => 'Seguimiento',
                'url' => '/tutor/seguimiento',
                'url_activa' => '0',
                'icon' => 'NotepadText',
                'orden' => 4,
                'estado' => '1',
                'id_modulosup' => null,
            ],
        ];

        // Obtener id_rol de vendedor y tutor
        $rolVendedor = DB::table('rol')->where('codigo', 'vendedor')->first();
        $rolTutor = DB::table('rol')->where('codigo', 'tutor')->first();

        if (!$rolVendedor || !$rolTutor) {
            $this->command->error('❌ No se encontró el rol vendedor o tutor. Ejecuta RoleSeeder primero.');
            return;
        }

        // Insertar módulos VENDEDOR y vincular a rol
        foreach ($vendedorModulos as $modulo) {
            $idModulo = DB::table('modulo')->insertGetId($modulo);
            DB::table('rol_modulo')->insert([
                'id_rol' => $rolVendedor->id_rol,
                'id_modulo' => $idModulo,
            ]);
            $this->command->info("  ✅ Módulo vendedor: {$modulo['nombre']} (id: {$idModulo})");
        }

        // Insertar módulos TUTOR y vincular a rol
        foreach ($tutorModulos as $modulo) {
            $idModulo = DB::table('modulo')->insertGetId($modulo);
            DB::table('rol_modulo')->insert([
                'id_rol' => $rolTutor->id_rol,
                'id_modulo' => $idModulo,
            ]);
            $this->command->info("  ✅ Módulo tutor: {$modulo['nombre']} (id: {$idModulo})");
        }

        $this->command->info('🎉 ¡Módulos de Vendedor y Tutor creados exitosamente!');
    }
}
