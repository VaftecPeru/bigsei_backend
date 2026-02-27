<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TramitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('tramites')->insert([
            [
                'idUsuario' => 1, 
                'tipo_tramite' => 'Constancia de Estudios',
                'estado' => 'Pendiente',
                'fecha_solicitud' => now(),
            ],
            [
                'idUsuario' => 2,
                'tipo_tramite' => 'Certificado de Notas',
                'estado' => 'Aprobado',
                'fecha_solicitud' => now(),
            ],
            [
                'idUsuario' => 5,
                'tipo_tramite' => 'Diploma',
                'estado' => 'Rechazado',
                'fecha_solicitud' => now(),
            ],
        ]);
    }
}
