<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // UserFactory::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // Seeders de configuración inicial (ejecutar primero)
            RoleSeeder::class,
            SuperAdminSeeder::class,
            
            // Seeders de contenido de cursos
            CourseContentSeeder::class,
            CourseCompletionSeeder::class,
        ]);
    }
}
