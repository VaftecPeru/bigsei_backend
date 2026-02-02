<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * SuperAdminSeeder - Crea usuario SuperAdministrador de prueba
 * 
 * ⚠️ IMPORTANTE: Cambiar la contraseña en producción
 * Este seeder crea un usuario SuperAdmin para pruebas iniciales
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un SuperAdmin
        $existingSuperAdmin = DB::table('usuario')
            ->join('usuario_rol', 'usuario.id_usuario', '=', 'usuario_rol.id_usuario')
            ->where('usuario_rol.id_rol', 1)
            ->where('usuario_rol.es_principal', '1')
            ->first();

        if ($existingSuperAdmin) {
            $this->command->warn('⚠️ Ya existe un SuperAdministrador en el sistema:');
            $this->command->info("   Usuario: {$existingSuperAdmin->username}");
            $this->command->info("   Email: {$existingSuperAdmin->correo}");
            $this->command->info('   No se creará un nuevo SuperAdmin.');
            return;
        }

        // Primero verificar/crear una sede (empresa)
        $empresa = DB::table('empresa')->first();
        
        if (!$empresa) {
            // Crear sede por defecto si no existe ninguna
            $idEmpresa = DB::table('empresa')->insertGetId([
                'id_tipodocumento' => 1, // RUC
                'numero_documento' => '20123456789',
                'razon_social' => 'Institución Educativa BIGSEI - Sede Principal',
                'nombre_comercial' => 'BIGSEI',
                'direccion_fiscal' => 'Av. Principal 123, Lima, Perú',
                'correo' => 'contacto@bigsei.edu.pe',
                'telefono' => '012345678',
                'estado' => '1',
                'fechareg' => now(),
            ]);
            $this->command->info('✅ Sede principal creada automáticamente');
        } else {
            $idEmpresa = $empresa->id_empresa;
        }

        // Verificar que exista el rol SuperAdministrador
        $rolSuperAdmin = DB::table('rol')->where('id_rol', 1)->first();
        if (!$rolSuperAdmin) {
            // Crear rol si no existe
            DB::table('rol')->insert([
                'id_rol' => 1,
                'nombre' => 'SuperAdministrador',
                'codigo' => 'superadministrador',
                'estado' => '1',
            ]);
            $this->command->info('✅ Rol SuperAdministrador creado');
        }

        // Crear usuario SuperAdmin
        $username = 'superadmin';
        $password = 'SuperAdmin2024!'; // Contraseña por defecto - CAMBIAR EN PRODUCCIÓN
        $email = 'superadmin@bigsei.edu.pe';

        $idUsuario = DB::table('usuario')->insertGetId([
            'dni' => '12345678',
            'nombres' => 'Super',
            'apellidoPaterno' => 'Administrador',
            'apellidoMaterno' => 'BIGSEI',
            'fechaNacimiento' => '1990-01-01',
            'genero' => 'Masculino',
            'telefono' => '999999999',
            'correo' => $email,
            'direccion' => 'Lima, Perú',
            'foto' => null,
            'username' => $username,
            'password' => Hash::make($password),
            'estado' => '1',
            'fechareg' => now(),
            'fechamod' => now(),
        ]);

        // Crear registro en persona
        DB::table('persona')->insert([
            'id_persona' => $idUsuario,
            'id_empresa' => $idEmpresa,
            'id_tipodocumento' => 1, // DNI
            'numero_documento' => '12345678',
            'nombre' => 'Super',
            'apellido_paterno' => 'Administrador',
            'apellido_materno' => 'BIGSEI',
            'nombre_completo' => 'Super Administrador BIGSEI',
            'correo' => $email,
            'telefono' => '999999999',
            'direccion' => 'Lima, Perú',
            'sexo' => 'Masculino',
            'fecha_nacimiento' => '1990-01-01',
            'estado' => '1',
        ]);

        // Asignar rol SuperAdministrador
        DB::table('usuario_rol')->insert([
            'id_empresa' => $idEmpresa,
            'id_usuario' => $idUsuario,
            'id_rol' => 1, // SuperAdministrador
            'es_principal' => '1',
        ]);

        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════════════════════════╗');
        $this->command->info('║           ✅ SUPERADMINISTRADOR CREADO EXITOSAMENTE        ║');
        $this->command->info('╠════════════════════════════════════════════════════════════╣');
        $this->command->info("║  Usuario:   {$username}                                     ║");
        $this->command->info("║  Email:     {$email}                         ║");
        $this->command->info("║  Password:  {$password}                            ║");
        $this->command->info('╠════════════════════════════════════════════════════════════╣');
        $this->command->info('║  ⚠️  IMPORTANTE: Cambiar la contraseña en producción       ║');
        $this->command->info('╚════════════════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
