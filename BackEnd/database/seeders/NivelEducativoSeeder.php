<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelEducativoSeeder extends Seeder
{
    /**
     * Poblar la tabla tipo_niveleducativo con los nuevos niveles educativos.
     */
    public function run()
    {
        $niveles = [
            ['nombre' => 'Primaria'],
            ['nombre' => 'Secundaria'],
            ['nombre' => 'Universidad'],
            ['nombre' => 'Bachiller'],
            ['nombre' => 'Título'],
            ['nombre' => 'Maestría'],
            ['nombre' => 'Doctorado'],
            ['nombre' => 'Empresa'],
        ];

        foreach ($niveles as $nivel) {
            DB::table('tipo_niveleducativo')->updateOrInsert(
                ['nombre' => $nivel['nombre']],
                ['nombre' => $nivel['nombre']]
            );
        }

        $this->command->info('Niveles educativos agregados correctamente.');
    }
}
