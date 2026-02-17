<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * Tests de integración para los endpoints del Plan de Estudio en el rol Admin.
 *
 * REQUISITOS PARA EJECUTAR:
 * 1. Base de datos con datos reales o seedeada (empresa, usuario, carrera, ciclo, etc.)
 * 2. Una sesión activa de usuario Admin en la tabla `usuario_sesion`
 *
 * CONFIGURACIÓN:
 * - Ajustar TOKEN_ADMIN con un token válido de la tabla usuario_sesion
 * - Ajustar las constantes de IDs según la base de datos de prueba
 *
 * EJECUTAR:
 *   php artisan test --filter=PlanEstudioControllerTest
 *   php artisan test --filter=PlanEstudioControllerTest::test_listar_planes
 */
class PlanEstudioControllerTest extends TestCase
{
    /**
     * Token de sesión de un usuario Admin activo.
     * Obtenerlo de la tabla usuario_sesion donde estado='1' y el rol sea admin.
     */
    private string $TOKEN_ADMIN = '';

    /**
     * IDs para pruebas. Se ajustan en setUp() consultando la BD.
     */
    private $id_carrera = null;
    private $id_planestudio_creado = null;
    private $id_planestudiociclo_creado = null;
    private $id_planestudiocurso_creado = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Buscar un token de admin activo automáticamente
        $sesion = DB::table('usuario_sesion as a')
            ->join('rol as b', 'a.id_rol', 'b.id_rol')
            ->where('a.estado', '1')
            ->where('b.codigo', 'admin')
            ->select('a.token')
            ->first();

        if ($sesion) {
            $this->TOKEN_ADMIN = $sesion->token;
        }

        // Buscar una carrera activa para las pruebas
        $carrera = DB::table('carrera')->where('estado', '1')->first();
        if ($carrera) {
            $this->id_carrera = $carrera->id_carrera;
        }
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->TOKEN_ADMIN,
            'Accept' => 'application/json',
        ];
    }

    // =====================================================
    // PLAN DE ESTUDIO - CRUD
    // =====================================================

    public function test_listar_planes(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $response = $this->withHeaders($this->headers())
            ->get('/api/admin/plan-estudios');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data',
            'per_page',
            'total',
        ]);
    }

    public function test_crear_plan_exitoso(): void
    {
        if (!$this->TOKEN_ADMIN || !$this->id_carrera) {
            $this->markTestSkipped('No se encontró token de admin o carrera activa.');
        }

        $response = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudios', [
                'id_carrera' => $this->id_carrera,
                'fecha_inicio' => '2026-03-01',
                'nombre' => 'Plan Test PHPUnit ' . now()->format('His'),
                'estado' => '1',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id_planestudio',
            'id_empresa',
            'id_carrera',
            'nombre',
            'estado',
        ]);

        // Guardar para pruebas subsiguientes
        $this->id_planestudio_creado = $response->json('id_planestudio');
        $this->assertNotNull($this->id_planestudio_creado);
    }

    public function test_crear_plan_sin_nombre_falla(): void
    {
        if (!$this->TOKEN_ADMIN || !$this->id_carrera) {
            $this->markTestSkipped('No se encontró token de admin o carrera activa.');
        }

        $response = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudios', [
                'id_carrera' => $this->id_carrera,
                'fecha_inicio' => '2026-03-01',
                // 'nombre' => falta intencionalmente
                'estado' => '1',
            ]);

        $response->assertStatus(400);
    }

    public function test_ver_plan(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        // Primero crear un plan
        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        $response = $this->withHeaders($this->headers())
            ->get('/api/admin/plan-estudios/' . $plan->id_planestudio);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id_planestudio',
            'nombre',
            'carrera_nombre',
            'estado_descripcion',
        ]);

        // Verificar que el bug está corregido: estado_descripcion debe ser 'Activo'
        $this->assertEquals('Activo', $response->json('estado_descripcion'));

        // Limpiar
        DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
    }

    public function test_editar_plan(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        $nuevoNombre = 'Plan Editado PHPUnit ' . now()->format('His');
        $response = $this->withHeaders($this->headers())
            ->put('/api/admin/plan-estudios/' . $plan->id_planestudio, [
                'id_carrera' => $this->id_carrera,
                'fecha_inicio' => '2026-06-01',
                'nombre' => $nuevoNombre,
                'estado' => '1',
            ]);

        $response->assertStatus(200);
        $this->assertEquals($nuevoNombre, $response->json('nombre'));

        // Limpiar
        DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
    }

    public function test_publicar_plan(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        $response = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudios/' . $plan->id_planestudio . '/publicar');

        $response->assertStatus(200);
        $this->assertEquals('1', $response->json('esta_publicado'));

        // Limpiar
        DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
    }

    public function test_publicar_plan_ya_publicado_falla(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        // Publicar primero
        $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudios/' . $plan->id_planestudio . '/publicar');

        // Intentar publicar de nuevo
        $response = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudios/' . $plan->id_planestudio . '/publicar');

        $response->assertStatus(400);

        // Limpiar
        DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
    }

    public function test_eliminar_plan(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        $response = $this->withHeaders($this->headers())
            ->delete('/api/admin/plan-estudios/' . $plan->id_planestudio);

        $response->assertStatus(200);

        // Verificar que no existe
        $deleted = DB::table('plan_estudio')
            ->where('id_planestudio', $plan->id_planestudio)
            ->first();
        $this->assertNull($deleted);
    }

    // =====================================================
    // CICLOS
    // =====================================================

    public function test_listar_ciclos_check(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        $response = $this->withHeaders($this->headers())
            ->get('/api/admin/plan-estudio-ciclos/check?id_planestudio=' . $plan->id_planestudio);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);

        // Limpiar
        DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
    }

    public function test_crud_ciclo_en_plan(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        // Buscar un ciclo existente
        $ciclo = DB::table('ciclo')->first();
        if (!$ciclo) {
            DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
            $this->markTestSkipped('No hay ciclos en la BD.');
        }

        // Crear ciclo en plan
        $response = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudio-ciclos', [
                'id_planestudio' => $plan->id_planestudio,
                'id_ciclo' => $ciclo->id_ciclo,
            ]);

        $response->assertStatus(200);
        $id_planestudiociclo = $response->json('id_planestudiociclo');
        $this->assertNotNull($id_planestudiociclo);

        // Listar ciclos del plan
        $response = $this->withHeaders($this->headers())
            ->get('/api/admin/plan-estudio-ciclos?id_planestudio=' . $plan->id_planestudio);

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));

        // Eliminar ciclo del plan
        $response = $this->withHeaders($this->headers())
            ->delete('/api/admin/plan-estudio-ciclos/' . $id_planestudiociclo);

        $response->assertStatus(200);

        // Limpiar
        DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
    }

    // =====================================================
    // CURSOS EN CICLO
    // =====================================================

    public function test_crud_curso_en_ciclo(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        $ciclo = DB::table('ciclo')->first();
        if (!$ciclo) {
            DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
            $this->markTestSkipped('No hay ciclos en la BD.');
        }

        // Crear ciclo en plan
        $cicloPlan = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudio-ciclos', [
                'id_planestudio' => $plan->id_planestudio,
                'id_ciclo' => $ciclo->id_ciclo,
            ]);
        $id_planestudiociclo = $cicloPlan->json('id_planestudiociclo');

        // Crear curso en ciclo
        $response = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudio-cursos', [
                'id_planestudiociclo' => $id_planestudiociclo,
                'curso_codigo' => 'TST-' . now()->format('His'),
                'curso_nombre' => 'Curso Test PHPUnit',
                'creditos' => 3,
                'horas_semanal' => 4,
                'tipo' => 'O',
            ]);

        $response->assertStatus(200);
        $id_planestudiocurso = $response->json('id_planestudiocurso');
        $id_curso = $response->json('id_curso');
        $this->assertNotNull($id_planestudiocurso);

        // Listar cursos
        $response = $this->withHeaders($this->headers())
            ->get('/api/admin/plan-estudio-cursos?id_planestudiociclo=' . $id_planestudiociclo);

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));

        // Ver detalle del curso
        $response = $this->withHeaders($this->headers())
            ->get('/api/admin/plan-estudio-cursos/' . $id_planestudiocurso);

        $response->assertStatus(200);
        $this->assertEquals('Curso Test PHPUnit', $response->json('nombre'));

        // Editar curso
        $response = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudio-cursos/' . $id_planestudiocurso, [
                'curso_codigo' => 'TST-EDIT',
                'curso_nombre' => 'Curso Test Editado',
                'creditos' => 5,
                'horas_semanal' => 6,
                'tipo' => 'E',
            ]);

        $response->assertStatus(200);

        // Eliminar curso (elimina plan_estudio_curso + curso)
        $response = $this->withHeaders($this->headers())
            ->delete('/api/admin/plan-estudio-cursos/' . $id_planestudiocurso);

        $response->assertStatus(200);

        // Limpiar
        DB::table('plan_estudio_ciclo')->where('id_planestudiociclo', $id_planestudiociclo)->delete();
        DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
    }

    // =====================================================
    // ESTADÍSTICAS
    // =====================================================

    public function test_estadisticas_plan(): void
    {
        if (!$this->TOKEN_ADMIN) {
            $this->markTestSkipped('No se encontró token de admin activo.');
        }

        $plan = $this->crearPlanDePrueba();
        if (!$plan) {
            $this->markTestSkipped('No se pudo crear plan de prueba.');
        }

        $response = $this->withHeaders($this->headers())
            ->get('/api/admin/plan-estudios/estadisticas?id_planestudio=' . $plan->id_planestudio);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_cursos',
            'total_creditos',
            'total_horas_semanal',
            'total_ciclos',
        ]);

        // Plan vacío debe devolver 0s
        $this->assertEquals(0, $response->json('total_cursos'));
        $this->assertEquals(0, $response->json('total_ciclos'));

        // Limpiar
        DB::table('plan_estudio')->where('id_planestudio', $plan->id_planestudio)->delete();
    }

    // =====================================================
    // WEB - PLANES PUBLICADOS (PORTADA)
    // =====================================================

    public function test_planes_publicados_portada(): void
    {
        $response = $this->get('/api/web/planes-publicados');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data',
        ]);
    }

    public function test_plan_publicado_detalle_no_existe(): void
    {
        $response = $this->get('/api/web/planes-publicados/999999');

        $response->assertStatus(404);
    }

    // =====================================================
    // HELPER: Crear plan de prueba
    // =====================================================

    private function crearPlanDePrueba(): ?object
    {
        if (!$this->id_carrera) {
            return null;
        }

        $response = $this->withHeaders($this->headers())
            ->post('/api/admin/plan-estudios', [
                'id_carrera' => $this->id_carrera,
                'fecha_inicio' => '2026-03-01',
                'nombre' => 'Plan Test ' . now()->format('YmdHis'),
                'estado' => '1',
            ]);

        if ($response->status() !== 200) {
            return null;
        }

        return (object) $response->json();
    }
}
