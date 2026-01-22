<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//CONTROLADORES
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\ReporteEstudiantesController;
use App\Http\Controllers\ContadorController;
use App\Http\Controllers\BibliotecaController;

use App\Http\Controllers\Superadministrador\EstudianteController as SuperAdminEstudianteController;
use App\Http\Controllers\Superadministrador\DocenteController as SuperAdminDocenteController;
use App\Http\Controllers\Superadministrador\EmpresaController as SuperAdminEmpresaController;
use App\Http\Controllers\Superadministrador\VendedorController as SuperAdminVendedorController;
use App\Http\Controllers\Superadministrador\AcademicoController as SuperAdminAcademicoController;
use App\Http\Controllers\Superadministrador\AsistenciaController as SuperAdminAsistenciaController;
use App\Http\Controllers\Superadministrador\MatriculaController as SuperAdminMatriculaController;
use App\Http\Controllers\Admin\EstudianteController as AdminEstudianteController;
use App\Http\Controllers\Admin\DocenteController as AdminDocenteController;
use App\Http\Controllers\Admin\UsuarioController as AdminUsuarioController;
use App\Http\Controllers\Admin\AcademicoController as AdminAcademicoController;
use App\Http\Controllers\Admin\MensajeriaController as AdminMensajeriaController;
use App\Http\Controllers\Admin\RolController as AdminRolController;
use App\Http\Controllers\Admin\ModuloController as AdminModuloController;
use App\Http\Controllers\Admin\PlanEstudioController as AdminPlanEstudioController;
use App\Http\Controllers\Director\EstudianteController as DirectorEstudianteController;
use App\Http\Controllers\Director\DocenteController as DirectorDocenteController;
use App\Http\Controllers\Director\AcademicoController as DirectorAcademicoController;
use App\Http\Controllers\Director\AsistenciaController as DirectorAsistenciaController;
use App\Http\Controllers\Docente\AcademicoController as DocenteAcademicoController;
use App\Http\Controllers\Docente\ArchivoController as DocenteArchivoController;
use App\Http\Controllers\Docente\MensajeriaController as DocenteMensajeriaController;
use App\Http\Controllers\Docente\AsistenciaController as DocenteAsistenciaController;
use App\Http\Controllers\Docente\MiAsistenciaController as DocenteMiAsistenciaController;
use App\Http\Controllers\Docente\EvaluacionNotaController as DocenteEvaluacionNotaController;
use App\Http\Controllers\Docente\EvaluacionCriterioController as DocenteEvaluacionCriterioController;
use App\Http\Controllers\Estudiante\MiCarreraController as EstudianteMiCarreraController;
use App\Http\Controllers\Estudiante\MiPerfilController as EstudianteMiPerfilController;
use App\Http\Controllers\Estudiante\MiNotaController as EstudianteMiNotaController;
use App\Http\Controllers\Estudiante\MiAcademicoController as EstudianteMiAcademicoController;
use App\Http\Controllers\Estudiante\ArchivoController as EstudianteArchivoController;
use App\Http\Controllers\Estudiante\MiMatriculaController as EstudianteMiMatriculaController;
use App\Http\Controllers\Estudiante\ReporteController as EstudianteReporteController;
use App\Http\Controllers\Estudiante\CertificadoController as EstudianteCertificadoController;
use App\Http\Controllers\Setup\ArchivoController as SetupArchivoController;
use App\Http\Controllers\Setup\TipoDocumentoController as SetupTipoDocumentoController;
use App\Http\Controllers\Setup\TipoModalidadestudioController as SetupTipoModalidadestudioController;
use App\Http\Controllers\Setup\TipoNivelEducativoController as SetupTipoNivelEducativoController;
use App\Http\Controllers\Setup\TipoEspecializacionController as SetupTipoEspecializacionController;
use App\Http\Controllers\Setup\CarreraController as SetupCarreraController;
use App\Http\Controllers\Setup\CicloController as SetupCicloController;
use App\Http\Controllers\Setup\CursoController as SetupCursoController;
use App\Http\Controllers\Setup\CategoriaController as SetupCategoriaController;
use App\Http\Controllers\Setup\SeccionController as SetupSeccionController;
use App\Http\Controllers\Setup\EmpresaController as SetupEmpresaController;
use App\Http\Controllers\Setup\RolController as SetupRolController;
use App\Http\Controllers\Setup\UsuarioController as SetupUsuarioController;
use App\Http\Controllers\Setup\TipoTituloAcademicoController as SetupTipoTituloAcademicoController;
use App\Http\Controllers\Setup\TituloAcademicoController as SetupTituloAcademicoController;
use App\Http\Controllers\Setup\TipoPreguntaController as SetupTipoPreguntaController;
use App\Http\Controllers\Setup\AulaController as SetupAulaController;
use App\Http\Controllers\Setup\DiaController as SetupDiaController;
use App\Http\Controllers\Superadministrador\SedeController;
use App\Http\Controllers\Dashboard\DashboardController as SetupDashboardController;

use App\Http\Controllers\Web\MatriculaController as WebMatriculaController;
use App\Http\Controllers\Web\CursoController as WebCursoController;
use App\Http\Controllers\Web\MisCursosController as WebMisCursosController;
use App\Http\Controllers\Web\MembresiaTipoController as WebMembresiaTipoController;
use App\Http\Controllers\Web\MembresiaController as WebMembresiaController;
use App\Http\Controllers\Web\SolicitudController as WebSolicitudController;
use App\Http\Controllers\Web\LicenciaController as WebLicenciaController;
use App\Http\Controllers\Web\CarreraController as WebCarreraController;
use App\Http\Controllers\Web\TipoCategoriaController as WebTipoCategoriaController;
use App\Http\Controllers\Web\TemaController as WebTemaController;
use App\Http\Controllers\Web\EmpresaController as WebEmpresaController;
use App\Http\Controllers\Web\MiMembresiaController as WebMiMembresiaController;
use App\Http\Controllers\Web\MiMatriculaController as WebMiMatriculaController;

//RUTAS

Route::group(['prefix' => 'superadministrador', 'middleware' => ['CheckUserRoleMW:superadministrador']], function () {
    Route::get('estudiantes', [SuperAdminEstudianteController::class, 'index']);
    Route::get('estudiantes/{id_estudiante}', [SuperAdminEstudianteController::class, 'show']);
    Route::post('estudiantes', [SuperAdminEstudianteController::class, 'store']);
    Route::put('estudiantes/{id_estudiante}', [SuperAdminEstudianteController::class, 'update']);
    Route::delete('estudiantes/{id_estudiante}', [SuperAdminEstudianteController::class, 'destroy']);
    Route::get('docentes', [SuperAdminDocenteController::class, 'index']);
    Route::get('docentes/{id_docente}', [SuperAdminDocenteController::class, 'show']);
    Route::post('docentes', [SuperAdminDocenteController::class, 'store']);
    Route::put('docentes/{id_docente}', [SuperAdminDocenteController::class, 'update']);
    Route::delete('docentes/{id_docente}', [SuperAdminDocenteController::class, 'destroy']);
    Route::get('empresas', [SuperAdminEmpresaController::class, 'index']);
    Route::get('empresas/{id_empresa}', [SuperAdminEmpresaController::class, 'show']);
    Route::post('empresas', [SuperAdminEmpresaController::class, 'store']);
    Route::put('empresas/{id_empresa}', [SuperAdminEmpresaController::class, 'update']);
    Route::post('empresas/{id_empresa}/archivos', [SuperAdminEmpresaController::class, 'storeArchivo']);
    Route::delete('empresas/{id_empresa}', [SuperAdminEmpresaController::class, 'destroy']);
    Route::get('vendedores', [SuperAdminVendedorController::class, 'index']);
    Route::get('academico-periodos', [SuperAdminAcademicoController::class, 'indexPeriodo']);
    Route::get('academico-periodos/{id_periodo}', [SuperAdminAcademicoController::class, 'showPeriodo']);
    Route::post('academico-periodos', [SuperAdminAcademicoController::class, 'storePeriodo']);
    Route::put('academico-periodos/{id_periodo}', [SuperAdminAcademicoController::class, 'updatePeriodo']);
    Route::delete('academico-periodos/{id_periodo}', [SuperAdminAcademicoController::class, 'destroyPeriodo']);
    Route::get('academico-periodo-ciclos', [SuperAdminAcademicoController::class, 'indexPeriodoCiclo']);
    Route::get('academico-periodo-ciclos/{id_periodociclo}', [SuperAdminAcademicoController::class, 'showPeriodoCiclo']);
    Route::post('academico-periodo-ciclos', [SuperAdminAcademicoController::class, 'storePeriodoCiclo']);
    Route::put('academico-periodo-ciclos/{id_periodociclo}', [SuperAdminAcademicoController::class, 'updatePeriodoCiclo']);
    Route::delete('academico-periodo-ciclos/{id_periodociclo}', [SuperAdminAcademicoController::class, 'destroyPeriodoCiclo']);
    Route::get('academico-periodo-cursos/docentes-activos', [SuperAdminAcademicoController::class, 'docentesActivos']);
    Route::get('academico-periodo-cursos/cursos-activos', [SuperAdminAcademicoController::class, 'cursosActivos']);
    Route::get('academico-periodo-cursos', [SuperAdminAcademicoController::class, 'indexPeriodoCurso']);
    Route::get('academico-periodo-cursos/{id_periodocurso}', [SuperAdminAcademicoController::class, 'showPeriodoCurso']);
    Route::post('academico-periodo-cursos', [SuperAdminAcademicoController::class, 'storePeriodoCurso']);
    Route::put('academico-periodo-cursos/{id_periodocurso}', [SuperAdminAcademicoController::class, 'updatePeriodoCurso']);
    Route::delete('academico-periodo-cursos/{id_periodocurso}', [SuperAdminAcademicoController::class, 'destroyPeriodoCurso']);
    Route::get('asistencias', [SuperAdminAsistenciaController::class, 'index']);
    Route::post('asistencias', [SuperAdminAsistenciaController::class, 'store']);
    Route::get('matriculas/estudiantes-activos', [SuperAdminMatriculaController::class, 'estudiantesActivos']);
    Route::get('matriculas/cursos-activos', [SuperAdminMatriculaController::class, 'cursosActivos']);
});
Route::group(['prefix' => 'admin', 'middleware' => ['CheckUserRoleMW:admin']], function () {
    Route::get('estudiantes', [AdminEstudianteController::class, 'index']);
    Route::get('estudiantes/{id_estudiante}', [AdminEstudianteController::class, 'show']);
    Route::post('estudiantes', [AdminEstudianteController::class, 'store']);
    Route::post('estudiantes/{id_estudiante}', [AdminEstudianteController::class, 'update']);
    Route::delete('estudiantes/{id_estudiante}', [AdminEstudianteController::class, 'destroy']);
    Route::get('docentes', [AdminDocenteController::class, 'index']);
    Route::get('docentes/{id_docente}', [AdminDocenteController::class, 'show']);
    Route::post('docentes', [AdminDocenteController::class, 'store']);
    Route::post('docentes/{id_docente}', [AdminDocenteController::class, 'update']);
    Route::delete('docentes/{id_docente}', [AdminDocenteController::class, 'destroy']);
    Route::get('usuarios/buscar-personas', [AdminUsuarioController::class, 'buscarPersona']);
    Route::post('usuarios/completar-personas', [AdminUsuarioController::class, 'completarPersona']);
    Route::get('usuarios', [AdminUsuarioController::class, 'index']);
    Route::get('usuarios/{id_usuario}', [AdminUsuarioController::class, 'show']);
    Route::post('usuarios', [AdminUsuarioController::class, 'store']);
    Route::post('usuarios/generar-password', [AdminUsuarioController::class, 'generarPassword']);
    Route::post('usuarios/{id_usuario}', [AdminUsuarioController::class, 'update']);
    Route::delete('usuarios/{id_usuario}', [AdminUsuarioController::class, 'destroy']);
    Route::get('academico-periodos/carrera-estadisticas', [AdminAcademicoController::class, 'carreraEstadisticas']);
    Route::get('academico-periodos/resumen-carreras', [AdminAcademicoController::class, 'resumenCarrera']);
    Route::get('academico-periodos', [AdminAcademicoController::class, 'indexPeriodo']);
    Route::get('academico-periodos/{id_periodo}', [AdminAcademicoController::class, 'showPeriodo']);
    Route::post('academico-periodos', [AdminAcademicoController::class, 'storePeriodo']);
    Route::put('academico-periodos/{id_periodo}', [AdminAcademicoController::class, 'updatePeriodo']);
    Route::delete('academico-periodos/{id_periodo}', [AdminAcademicoController::class, 'destroyPeriodo']);
    Route::post('academico-periodos/{id_periodo}/abrir', [AdminAcademicoController::class, 'abrir']);
    Route::get('academico-periodo-ciclos', [AdminAcademicoController::class, 'indexPeriodoCiclo']);
    Route::get('academico-periodo-ciclos/{id_periodociclo}', [AdminAcademicoController::class, 'showPeriodoCiclo']);
    Route::post('academico-periodo-ciclos', [AdminAcademicoController::class, 'storePeriodoCiclo']);
    Route::put('academico-periodo-ciclos/{id_periodociclo}', [AdminAcademicoController::class, 'updatePeriodoCiclo']);
    Route::delete('academico-periodo-ciclos/{id_periodociclo}', [AdminAcademicoController::class, 'destroyPeriodoCiclo']);
    Route::get('academico-periodo-cursos', [AdminAcademicoController::class, 'indexPeriodoCurso']);
    Route::get('academico-periodo-cursos/{id_periodocurso}', [AdminAcademicoController::class, 'showPeriodoCurso']);
    Route::post('academico-periodo-cursos', [AdminAcademicoController::class, 'storePeriodoCurso']);
    Route::put('academico-periodo-cursos/{id_periodocurso}', [AdminAcademicoController::class, 'updatePeriodoCurso']);
    Route::delete('academico-periodo-cursos/{id_periodocurso}', [AdminAcademicoController::class, 'destroyPeriodoCurso']);
    Route::get('academico-periodo-curso-precios', [AdminAcademicoController::class, 'indexPeriodoCursoPrecio']);
    Route::post('academico-periodo-curso-precios', [AdminAcademicoController::class, 'storePeriodoCursoPrecio']);
    Route::delete('academico-periodo-curso-precios/{id_periodocursoprecio}', [AdminAcademicoController::class, 'destroyPeriodoCursoPrecio']);
    // Route::get('academico-plan-estudio-ciclos', [AdminAcademicoController::class, 'indexPlanEstudioCiclo']);
    // Route::get('academico-plan-estudio-cursos', [AdminAcademicoController::class, 'indexPlanEstudioCurso']);
    Route::get('academico-carreras', [AdminAcademicoController::class, 'indexCarrera']);
    Route::get('mensajerias/grupos', [AdminMensajeriaController::class, 'indexGrupo']);
    Route::post('mensajerias/grupos', [AdminMensajeriaController::class, 'storeGrupo']);
    Route::get('mensajerias/personas', [AdminMensajeriaController::class, 'indexPersona']);
    Route::post('mensajerias/estudiante-todos', [AdminMensajeriaController::class, 'storeEstudianteTodos']);
    Route::post('mensajerias/docentes', [AdminMensajeriaController::class, 'storeDocente']);
    Route::get('roles', [AdminRolController::class, 'index']);
    Route::post('roles', [AdminRolController::class, 'store']);
    Route::delete('roles/{id_usuariorol}', [AdminRolController::class, 'destroy']);
    Route::put('roles/{id_usuariorol}/elegir-principal', [AdminRolController::class, 'elegirPrincipal']);
    Route::get('modulos', [AdminModuloController::class, 'index']);
    Route::get('academico-periodo-horarios', [AdminAcademicoController::class, 'indexPeriodoHorario']);
    Route::get('academico-periodo-horarios/{id_periodohorario}', [AdminAcademicoController::class, 'showPeriodoHorario']);
    Route::post('academico-periodo-horarios', [AdminAcademicoController::class, 'storePeriodoHorario']);
    Route::put('academico-periodo-horarios/{id_periodohorario}', [AdminAcademicoController::class, 'updatePeriodoHorario']);
    Route::delete('academico-periodo-horarios/{id_periodohorario}', [AdminAcademicoController::class, 'destroyPeriodoHorario']);
    Route::get('plan-estudios/estadisticas', [AdminPlanEstudioController::class, 'estadisticas']);
    Route::get('plan-estudios', [AdminPlanEstudioController::class, 'index']);
    Route::get('plan-estudios/{id_planestudio}', [AdminPlanEstudioController::class, 'show']);
    Route::post('plan-estudios', [AdminPlanEstudioController::class, 'store']);
    Route::post('plan-estudios/{id_planestudio}/publicar', [AdminPlanEstudioController::class, 'publicar']);
    Route::put('plan-estudios/{id_planestudio}', [AdminPlanEstudioController::class, 'update']);
    Route::delete('plan-estudios/{id_planestudio}', [AdminPlanEstudioController::class, 'destroy']);
    Route::get('plan-estudio-ciclos/check', [AdminPlanEstudioController::class, 'indexCicloCheck']);
    Route::get('plan-estudio-ciclos', [AdminPlanEstudioController::class, 'indexCiclo']);
    Route::post('plan-estudio-ciclos', [AdminPlanEstudioController::class, 'storeCiclo']);
    Route::delete('plan-estudio-ciclos/{id_planestudiociclo}', [AdminPlanEstudioController::class, 'destroyCiclo']);
    Route::get('plan-estudio-cursos', [AdminPlanEstudioController::class, 'indexCurso']);
    Route::get('plan-estudio-cursos/{id_planestudiocurso}', [AdminPlanEstudioController::class, 'showCurso']);
    Route::post('plan-estudio-cursos', [AdminPlanEstudioController::class, 'storeCurso']);
    Route::post('plan-estudio-cursos/{id_planestudiocurso}', [AdminPlanEstudioController::class, 'updateCurso']);
    Route::delete('plan-estudio-cursos/{id_planestudiocurso}', [AdminPlanEstudioController::class, 'destroyCurso']);
});
Route::group(['prefix' => 'director', 'middleware' => ['CheckUserRoleMW:director']], function () {
    Route::get('estudiantes', [DirectorEstudianteController::class, 'index']);
    Route::get('docentes', [DirectorDocenteController::class, 'index']);
    Route::get('academico-carreras', [DirectorAcademicoController::class, 'indexCarrera']);
    Route::get('academico-periodos', [DirectorAcademicoController::class, 'indexPeriodo']);
    Route::get('academico-periodo-ciclos', [DirectorAcademicoController::class, 'indexPeriodoCiclo']);
    Route::get('academico-periodo-ciclos/carreras-activas', [DirectorAcademicoController::class, 'carrerasActivas']);
    Route::get('academico-periodo-cursos', [DirectorAcademicoController::class, 'indexPeriodoCurso']);
    Route::get('academico-plan-estudios', [DirectorAcademicoController::class, 'indexPlanEstudio']);
    Route::get('asistencias/estadisticas', [DirectorAsistenciaController::class, 'estadisticas']);
});
Route::group(['prefix' => 'docente', 'middleware' => ['CheckUserRoleMW:docente']], function () {
    Route::get('academico-periodos', [DocenteAcademicoController::class, 'index']);
    Route::get('academico-periodo-ciclos', [DocenteAcademicoController::class, 'indexPeriodoCiclo']);
    Route::get('academico-periodo-cursos', [DocenteAcademicoController::class, 'indexPeriodoCurso']);
    Route::get('academico-periodo-cursos/{id_periodocurso}', [DocenteAcademicoController::class, 'showPeriodoCurso']);
    Route::get('academico-periodo-modulos', [DocenteAcademicoController::class, 'indexPeriodoModulo']);
    Route::post('academico-periodo-modulos', [DocenteAcademicoController::class, 'storePeriodoModulo']);
    Route::get('academico-periodo-temas', [DocenteAcademicoController::class, 'indexPeriodoTema']);
    Route::post('academico-periodo-temas', [DocenteAcademicoController::class, 'storePeriodoTema']);
    Route::get('academico-periodo-tareas', [DocenteAcademicoController::class, 'indexPeriodoTarea']);
    Route::get('academico-periodo-tareas/{id_periodotarea}', [DocenteAcademicoController::class, 'showPeriodoTarea']);
    Route::post('academico-periodo-tareas', [DocenteAcademicoController::class, 'storePeriodoTarea']);
    Route::put('academico-periodo-tareas/{id_periodotarea}', [DocenteAcademicoController::class, 'updatePeriodoTarea']);
    Route::patch('academico-periodo-tareas/{id_periodotarea}', [DocenteAcademicoController::class, 'patchPeriodoTarea']);
    Route::get('archivos', [DocenteArchivoController::class, 'index']);
    Route::post('archivos', [DocenteArchivoController::class, 'store']);
    Route::delete('archivos/{id_archivo}', [DocenteArchivoController::class, 'destroy']);
    Route::get('academico-periodo-videos', [DocenteAcademicoController::class, 'indexPeriodoVideo']);
    Route::post('academico-periodo-videos', [DocenteAcademicoController::class, 'storePeriodoVideo']);
    Route::put('academico-periodo-videos/{id_periodovideo}', [DocenteAcademicoController::class, 'updatePeriodoVideo']);
    Route::get('academico-periodo-cuestionarios', [DocenteAcademicoController::class, 'indexPeriodoCuestionario']);
    Route::get('academico-periodo-cuestionarios/{id_periodocuestionario}', [DocenteAcademicoController::class, 'showPeriodoCuestionario']);
    Route::post('academico-periodo-cuestionarios', [DocenteAcademicoController::class, 'storePeriodoCuestionario']);
    Route::put('academico-periodo-cuestionarios/{id_periodocuestionario}', [DocenteAcademicoController::class, 'updatePeriodoCuestionario']);
    Route::get('academico-periodo-preguntas', [DocenteAcademicoController::class, 'indexPeriodoPregunta']);
    Route::post('academico-periodo-preguntas', [DocenteAcademicoController::class, 'storePeriodoPregunta']);
    Route::put('academico-periodo-preguntas/{id_periodopregunta}', [DocenteAcademicoController::class, 'updatePeriodoPregunta']);
    Route::delete('academico-periodo-preguntas/{id_periodopregunta}', [DocenteAcademicoController::class, 'destroyPeriodoPregunta']);
    Route::get('academico-periodo-respuestas', [DocenteAcademicoController::class, 'indexPeriodoRespuesta']);
    Route::post('academico-periodo-respuestas', [DocenteAcademicoController::class, 'storePeriodoRespuesta']);
    Route::put('academico-periodo-respuestas/{id_periodorespuesta}', [DocenteAcademicoController::class, 'updatePeriodoRespuesta']);
    Route::get('academico-periodo-horarios', [DocenteAcademicoController::class, 'indexPeriodoHorario']);
    Route::get('mensajeria-grupos', [DocenteMensajeriaController::class, 'indexGrupo']);
    Route::get('mensajeria-personas', [DocenteMensajeriaController::class, 'indexPersona']);
    Route::get('mensajeria-mensajes', [DocenteMensajeriaController::class, 'indexMensaje']);
    Route::post('mensajeria-mensajes', [DocenteMensajeriaController::class, 'storeMensaje']);
    Route::get('asistencias/estudiantes', [DocenteAsistenciaController::class, 'indexEstudiante']);
    Route::post('asistencias/estudiantes', [DocenteAsistenciaController::class, 'storeEstudiante']);
    Route::post('asistencias/estudiantes/todos', [DocenteAsistenciaController::class, 'storeEstudianteTodos']);
    Route::get('mi-asistencias', [DocenteMiAsistenciaController::class, 'index']);
    Route::post('mi-asistencias', [DocenteMiAsistenciaController::class, 'store']);
    Route::get('mi-asistencias/porcentajes', [DocenteMiAsistenciaController::class, 'porcentajes']);
    Route::get('evaluacion-notas', [DocenteEvaluacionNotaController::class, 'index']);
    Route::post('evaluacion-notas', [DocenteEvaluacionNotaController::class, 'store']);
    Route::get('evaluacion-criterios', [DocenteEvaluacionCriterioController::class, 'index']);
    Route::post('evaluacion-criterios', [DocenteEvaluacionCriterioController::class, 'store']);
    Route::put('evaluacion-criterios/{id_evaluacioncriterio}', [DocenteEvaluacionCriterioController::class, 'update']);
});
Route::group(['prefix' => 'new-student'], function () {
    // route
});
Route::group(['prefix' => 'estudiante', 'middleware' => ['CheckUserRoleMW:superadministrador,admin,director,docente,student,padre,tutor,vendedor,bibliotecario,topicomedico,contador']], function () {
    Route::get('mi-carreras', [EstudianteMiCarreraController::class, 'index']);
    Route::get('mi-perfil', [EstudianteMiPerfilController::class, 'show']);
    Route::put('mi-perfil', [EstudianteMiPerfilController::class, 'update']);
    Route::post('mi-perfil/fotos', [EstudianteMiPerfilController::class, 'storeFoto']);
    Route::post('mi-perfil/baneres', [EstudianteMiPerfilController::class, 'storeBaner']);
    Route::get('mi-notas/matriculas', [EstudianteMiNotaController::class, 'matriculas']);
    Route::get('mi-notas/historial-academicos', [EstudianteMiNotaController::class, 'historialAcademico']);
    Route::get('mi-notas/promedios', [EstudianteMiNotaController::class, 'promedio']);
    Route::get('mi-matriculas', [EstudianteMiMatriculaController::class, 'index']);
    Route::get('mi-academico-periodos', [EstudianteMiAcademicoController::class, 'index']);
    Route::get('mi-academico-periodo-cursos', [EstudianteMiAcademicoController::class, 'indexPeriodoCurso']);
    Route::get('mi-academico-periodo-cursos/{id_periodocurso}', [EstudianteMiAcademicoController::class, 'showPeriodoCurso']);
    Route::get('mi-academico-periodo-modulos', [EstudianteMiAcademicoController::class, 'indexPeriodoModulo']);
    Route::get('mi-academico-periodo-temas', [EstudianteMiAcademicoController::class, 'indexPeriodoTema']);
    Route::get('mi-academico-periodo-tareas', [EstudianteMiAcademicoController::class, 'indexPeriodoTarea']);
    Route::get('mi-academico-entrega-tareas', [EstudianteMiAcademicoController::class, 'indexEntregaTarea']);
    Route::post('mi-academico-entrega-tareas', [EstudianteMiAcademicoController::class, 'storeEntregaTarea']);
    Route::get('tema-archivos', [EstudianteArchivoController::class, 'indexTema']);
    Route::get('mi-academico-periodo-videos', [EstudianteMiAcademicoController::class, 'indexPeriodoVideo']);
    Route::get('mi-academico-periodo-cuestionarios', [EstudianteMiAcademicoController::class, 'indexPeriodoCuestionario']);
    Route::get('mi-academico-periodo-cuestionarios/{id_periodocuestionario}', [EstudianteMiAcademicoController::class, 'showPeriodoCuestionario']);
    Route::get('mi-academico-periodo-preguntas', [EstudianteMiAcademicoController::class, 'indexPeriodoPregunta']);
    Route::post('mi-academico-entrega-respuestas', [EstudianteMiAcademicoController::class, 'storeEntregaRespuesta']);
    Route::get('reportes/notas', [EstudianteReporteController::class, 'notas']);
    Route::get('reportes/matriculas', [EstudianteReporteController::class, 'matriculas']);
    
    // Rutas de progreso y certificados
    Route::get('mi-progreso-curso/{id_periodocurso}', [EstudianteCertificadoController::class, 'getProgresoCurso']);
    Route::post('marcar-contenido-completado', [EstudianteCertificadoController::class, 'marcarContenidoCompletado']);
    Route::get('mis-certificados', [EstudianteCertificadoController::class, 'listarMisCertificados']);
    Route::get('mis-cursos-progreso', [EstudianteCertificadoController::class, 'listarMisCursosProgreso']);
    Route::post('generar-certificado/{id_periodocurso}', [EstudianteCertificadoController::class, 'generarCertificado']);
    Route::get('descargar-certificado/{id_certificado}', [EstudianteCertificadoController::class, 'descargarCertificado']);
});
Route::group(['prefix' => 'padre'], function () {
    // route
});
Route::group(['prefix' => 'tutor'], function () {
    // route
});
Route::group(['prefix' => 'vendedor'], function () {
    // route
});
Route::group(['prefix' => 'bibliotecario'], function () {
    // route
});
Route::group(['prefix' => 'topicomedico'], function () {
    // route
});
Route::group(['prefix' => 'contador'], function () {
    // route
});
Route::group(['prefix' => 'dashboard'], function () {
    //SUPERADMIN:
    Route::get('superadministrador/cantidad-sedes', [SetupDashboardController::class, 'getcantidadSedes']);
    Route::get('superadministrador/cantidad-docentes', [SetupDashboardController::class, 'getCantidadDocentes']);
    Route::get('superadministrador/cantidad-estudiantes', [SetupDashboardController::class, 'getCantidadEstudiantes']);
    Route::get('superadministrador/cantidad-padres', [SetupDashboardController::class, 'getCantidadPadres']);
    Route::get('superadministrador/cantidad-sede-padres', [SetupDashboardController::class, 'getCantidadSedePadres']);
    Route::get('superadministrador/cantidad-sede-estudiantes', [SetupDashboardController::class, 'getCantidadSedeEstudiantes']);
    Route::get('superadministrador/cantidad-sede-docentes', [SetupDashboardController::class, 'getCantidadSedeDocentes']);
    Route::get('superadministrador/lista-nombre-sedes', [SetupDashboardController::class, 'getNombreSedes']);
    Route::get('superadministrador/porcentaje-asistencia/{id_empresa}', [SetupDashboardController::class, 'getPorcentajesAsistencia']);
    Route::get('superadministrador/cantidad-matriculas-sede', [SetupDashboardController::class, 'getCantidadMatriculasPorSede']);

    //ADMIN:
    Route::get('admin/cantidad-sedes', [SetupDashboardController::class, 'getcantidadSedes']);
    Route::get('admin/cantidad-docentes', [SetupDashboardController::class, 'getCantidadDocentes']);
    Route::get('admin/cantidad-estudiantes', [SetupDashboardController::class, 'getCantidadEstudiantes']);
    Route::get('admin/cantidad-padres', [SetupDashboardController::class, 'getCantidadPadres']);
    Route::get('admin/reporte-notas', [SetupDashboardController::class, 'listarEvaluacionesNotas']);
    Route::get('admin/lista-nombre-periodo', [SetupDashboardController::class, 'getNombrePeriodo']);
    Route::get('admin/lista-nombre-ciclo', [SetupDashboardController::class, 'getNombreCiclo']);
    Route::get('admin/lista-nombre-curso', [SetupDashboardController::class, 'getNombreCurso']);
    Route::get('admin/balance-general', [SetupDashboardController::class, 'listarMovimientos']);
    Route::get('admin/lista-movimiento', [SetupDashboardController::class, 'listarMovimientoPorTipo']);
    Route::get('admin/lista-ingresos', [SetupDashboardController::class, 'ingresosPorMetodoPago']);
    Route::get('admin/porcentaje-ingresos', [SetupDashboardController::class, 'porcentajeIngresosPorMetodoPago']);
    Route::get('admin/porcentaje-egresos', [SetupDashboardController::class, 'porcentajeEgresosPorMetodoPago']);

    //DIRECTOR:
    Route::get('director/cantidad-sedes', [SetupDashboardController::class, 'getcantidadSedes']);
    Route::get('director/cantidad-docentes', [SetupDashboardController::class, 'getCantidadDocentes']);
    Route::get('director/cantidad-estudiantes', [SetupDashboardController::class, 'getCantidadEstudiantes']);
    Route::get('director/cantidad-padres', [SetupDashboardController::class, 'getCantidadPadres']);
    Route::get('director/porcentaje-ingresos', [SetupDashboardController::class, 'porcentajeIngresosPorMetodoPago']);
    Route::get('director/porcentaje-egresos', [SetupDashboardController::class, 'porcentajeEgresosPorMetodoPago']);

    //PADRE:
    Route::get('padre/cantidad-sedes', [SetupDashboardController::class, 'getcantidadSedes']);
    Route::get('padre/cantidad-docentes', [SetupDashboardController::class, 'getCantidadDocentes']);
    Route::get('padre/cantidad-estudiantes', [SetupDashboardController::class, 'getCantidadEstudiantes']);
    Route::get('padre/cantidad-padres', [SetupDashboardController::class, 'getCantidadPadres']);
    Route::get('padre/porcentaje-ingresos', [SetupDashboardController::class, 'porcentajeIngresosPorMetodoPago']);
    Route::get('padre/porcentaje-egresos', [SetupDashboardController::class, 'porcentajeEgresosPorMetodoPago']);

    //BIBLIOTECARIO:
    Route::get('bibliotecario/cant-reservas-estudiante', [SetupDashboardController::class, 'cantidadReservasEstudiantes']);
    Route::get('bibliotecario/cant-reservas-docente', [SetupDashboardController::class, 'cantidadReservasDocentes']);
    Route::get('bibliotecario/cant-devoluciones-atrasada', [SetupDashboardController::class, 'listarDevolucionesAtrasadas']);
    Route::get('bibliotecario/visitas', [SetupDashboardController::class, 'listarVisitasPorMes']);
    Route::get('bibliotecario/cant-reservas', [SetupDashboardController::class, 'reservasPorMesYTipo']);
    Route::get('bibliotecario/ult-reservas', [SetupDashboardController::class, 'ultimasReservas']);
    Route::get('bibliotecario/listar-libro', [SetupDashboardController::class, 'listarLibros']);

    //DOCENTE:
    Route::get('docente/listar-cursos/{idUsuario}', [SetupDashboardController::class, 'listarCursosPorDocente']);
    Route::get('docente/cantidad-alumnos/{idUsuario}', [SetupDashboardController::class, 'cantidadAlumnosCursoDocente']);
    Route::get('docente/listar-tareas/{idUsuario}', [SetupDashboardController::class, 'listarTareasPorDocente']);

    //ESTUDIANTE:
    Route::get('estudiante/listar-cursos/{idUsuario}', [SetupDashboardController::class, 'listarCursosPorEstudiante']);
    Route::get('estudiante/listar-notas/{idUsuario}', [SetupDashboardController::class, 'listarNotasPorEstudiante']);
    Route::get('estudiante/listar-tareas/{idUsuario}', [SetupDashboardController::class, 'listarTareasPorEstudiante']);
    Route::get('estudiante/listar-asistencia/{idUsuario}', [SetupDashboardController::class, 'listarAsistenciaDocentesPorEstudiante']);

    //TOPICO MEDICO:
    Route::get('topico/cantidad-paciente', [SetupDashboardController::class, 'getCantidadPaciente']);
    Route::get('topico/cantidad-doctor', [SetupDashboardController::class, 'getCantidadDoctor']);
});

Route::group(['prefix' => 'setup', 'middleware' => ['CheckUserMW:setup']], function () {
    // Route::get('visualizar-archivos/{id_archivo}', [SetupArchivoController::class, 'visualizar']);
    Route::get('tipo-modalidadestudios', [SetupTipoModalidadestudioController::class, 'index']);
    Route::get('tipo-especializaciones', [SetupTipoEspecializacionController::class, 'index']);
    Route::get('carreras/activos', [SetupCarreraController::class, 'activos']);
    Route::get('carreras', [SetupCarreraController::class, 'index']);
    Route::get('carreras/{id_carrera}', [SetupCarreraController::class, 'show']);
    Route::post('carreras', [SetupCarreraController::class, 'store']);
    Route::put('carreras/{id_carrera}', [SetupCarreraController::class, 'update']);
    Route::delete('carreras/{id_carrera}', [SetupCarreraController::class, 'destroy']);
    Route::get('ciclos/activos', [SetupCicloController::class, 'activos']);
    Route::get('ciclos', [SetupCicloController::class, 'index']);
    Route::get('ciclos/{id_ciclo}', [SetupCicloController::class, 'show']);
    Route::post('ciclos', [SetupCicloController::class, 'store']);
    Route::put('ciclos/{id_ciclo}', [SetupCicloController::class, 'update']);
    Route::delete('ciclos/{id_ciclo}', [SetupCicloController::class, 'destroy']);
    Route::get('cursos', [SetupCursoController::class, 'index']);
    Route::get('cursos/{id_curso}', [SetupCursoController::class, 'show']);
    Route::post('cursos', [SetupCursoController::class, 'store']);
    Route::post('cursos/{id_curso}', [SetupCursoController::class, 'update']);
    Route::delete('cursos/{id_curso}', [SetupCursoController::class, 'destroy']);
    // Route::get('tipo-categorias', [SetupCategoriaController::class, 'index']);
    Route::get('secciones', [SetupSeccionController::class, 'index']);
    Route::get('empresas', [SetupEmpresaController::class, 'index']);
    Route::get('roles', [SetupRolController::class, 'index']);
    Route::get('user-login', [SetupUsuarioController::class, 'userLogin']);
    Route::get('user-modulos', [SetupUsuarioController::class, 'userModulos']);
    Route::get('user-roles', [SetupUsuarioController::class, 'userRoles']);
    Route::post('user-roles/es-principales', [SetupUsuarioController::class, 'updateEsPrincipal']);
    Route::get('user-empresas', [SetupUsuarioController::class, 'userEmpresas']);
    Route::get('tipo-titulo-academicos/activos', [SetupTipoTituloAcademicoController::class, 'activos']);
    Route::get('titulo-academicos/activos', [SetupTituloAcademicoController::class, 'activos']);
    Route::get('tipo-preguntas', [SetupTipoPreguntaController::class, 'index']);
    Route::get('aulas/activos', [SetupAulaController::class, 'activos']);
    Route::get('aulas', [SetupAulaController::class, 'index']);
    Route::get('aulas/{id_aula}', [SetupAulaController::class, 'show']);
    Route::post('aulas', [SetupAulaController::class, 'store']);
    Route::put('aulas/{id_aula}', [SetupAulaController::class, 'update']);
    Route::delete('aulas/{id_aula}', [SetupAulaController::class, 'destroy']);
    Route::get('dias/activos', [SetupDiaController::class, 'activos']);
});
Route::group(['prefix' => 'setup', 'middleware' => ['auth.jwt']], function () {
    Route::get('tipo-niveleducativos', [SetupTipoNivelEducativoController::class, 'index']);
    Route::get('tipo-documentos', [SetupTipoDocumentoController::class, 'index']);
    Route::get('tipo-categorias', [SetupCategoriaController::class, 'index']);
    Route::get('visualizar-archivos/{id_archivo}', [SetupArchivoController::class, 'visualizar']);
    Route::get('archivos', [SetupArchivoController::class, 'index']);
    Route::get('descargar-archivos/{id_archivo}', [SetupArchivoController::class, 'descargar']);
    Route::get('archivos/{id_archivo}/visualizar-imagenes', [SetupArchivoController::class, 'imagen']);
});

Route::group(['prefix' => 'web'], function () {
    Route::get('matriculas/curso-libres', [WebMatriculaController::class, 'cursoLibres']);
    Route::get('matriculas/curso-libres/{id_periodocurso}', [WebMatriculaController::class, 'showCursoLibres']);
    Route::post('matriculas/curso-libres', [WebMatriculaController::class, 'storeCursoLibres']);
    Route::get('matriculas/precio-curso-libres', [WebMatriculaController::class, 'precioCursoLibres']);
    Route::get('matriculas/categoria-cursos-libres', [WebMatriculaController::class, 'categoriaCursosLibres']);
    Route::get('cursos/destacados', [WebCursoController::class, 'destacados']);
    Route::get('cursos/visibles-web', [WebCursoController::class, 'visiblesWeb']);
    Route::get('membresia-tipos/activos', [WebMembresiaTipoController::class, 'activos']);
    Route::get('membresia-tipos/{id_membresiatipo}', [WebMembresiaTipoController::class, 'show']);
    Route::post('membresias', [WebMembresiaController::class, 'store']);
    Route::post('solicitudes/empresas', [WebSolicitudController::class, 'storeEmpresa']);
    Route::post('solicitudes/contactos', [WebSolicitudController::class, 'storeContacto']);
    Route::get('licencias/tipo-activos', [WebLicenciaController::class, 'tipoActivos']);
    Route::post('licencias', [WebLicenciaController::class, 'store']);
    Route::get('carreras', [WebCarreraController::class, 'activas']);
    Route::get('carreras/tipo-titulo-academico', [WebCarreraController::class, 'tipoTituloAcademicos']);
    Route::get('tipo-categorias/por-temas', [WebTipoCategoriaController::class, 'porTemas']);
    Route::get('temas', [WebTemaController::class, 'index']);
    Route::get('empresas', [WebEmpresaController::class, 'index']);
});
Route::group(['prefix' => 'web', 'middleware' => ['CheckUserMW:web']], function () {
    Route::get('mis-cursos', [WebMisCursosController::class, 'index']);
    Route::get('mi-membresias/activas', [WebMiMembresiaController::class, 'activas']);
    Route::post('mi-membresias', [WebMiMembresiaController::class, 'store']);
    Route::post('mi-matriculas/con-membresias', [WebMiMatriculaController::class, 'storeConMembresia']);
    Route::post('mi-matriculas/sin-membresias', [WebMiMatriculaController::class, 'storeSinMembresia']);
});

//================================================================================================
// RUTAS AUTH

// RUTA PARA INICIAR SESION
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh-token', [AuthController::class, 'refreshToken']);
Route::post('update-activity', [AuthController::class, 'updateLastActivity']);

Route::post('/auth/google', [GoogleController::class, 'verifyGoogleToken']);

//================================================================================================

//================================================================================================
// RUTAS LIBRES

// RUTA PARA LISTAR ROLES
Route::get('listarRoles', [AdminController::class, 'listarRoles']);

// RUTA PARA LISTAR CURSOS
Route::get('listarCursos', [AdminController::class, 'listarCursos']);

// RUTA PARA LISTAR CICLOS
Route::get('listarCiclos', [AdminController::class, 'listarCiclos']);

// RUTA PARA LISTAR PERIODOS
Route::get('listarPeriodos', [AdminController::class, 'listarPeriodos']);

// RUTA PARA LISTAR MODALIDAD
Route::get('listarModalidad', [AdminController::class, 'listarModalidad']);

// RUTA PARA LISTAR DOCENTES
Route::get('listarDocentes', [AdminController::class, 'listarDocentes']);

//================================================================================================

//================================================================================================
// RUTAS PARA PROCESO DE MATRICULA

// RUTA PARA REGISTRAR LOS DATOS DE UN ESTUDIANTE
Route::post('matricula/datos-estudiante', [MatriculaController::class, 'registrarDatosEstudiante']);

// RUTA PARA REGISTRAR LOS DOCUMENTOS DE UN ESTUDIANTE
Route::post('matricula/documentacion', [MatriculaController::class, 'registrarDocumentacionEstudiante']);

// RUTA PARA GENERAR LA MATRICULA DE UN ESTUDIANTE
Route::post('matricula/generar', [MatriculaController::class, 'generarMatricula']);

// RUTA PARA PAGAR LA MATRICULA
Route::post('matricula/pago', [MatriculaController::class, 'pagarMatricula']);

// RUTA PARA EL REPORTE DE UNA MATRICULA
Route::get('matricula/reporte', [MatriculaController::class, 'reporteMatricula']);

// RUTA PARA DESCARGAR EL PAGO DE LA MATRICULA
Route::get('matricula/descargar-pago-matricula', [MatriculaController::class, 'descargarPagoMatricula']);

// RUTA PARA DESCARGAR LOS CURSOS MATRICULADOS
Route::get('matricula/descargar-cursos-matriculados', [MatriculaController::class, 'descargarCursosMatriculados']);
// RUTA PARA EL REPORTE DE ASISTENCIA PAI
Route::get('matricula/reporte_asistencia_pai', [MatriculaController::class, 'obtenerReporteAsistenciaPai']);

// RUTA PARA EL REPORTE DE ASISTENCIA POR CURSO Y DOCENTE
Route::get('matricula/reporte_asistencia_curso_docente', [DocenteController::class, 'obtenerReporteAsistenciaCursoDocente']);



//RUTA PARA LISTAR LOS CURSO CON CICLO Y REPITENCIAS EN MATRICULA ESTUDIANTE NUEVO
Route::get('/listar-cursos-matricula', [MatriculaController::class, 'listarCursosMatricula']);

//RUTA PARA LISTAR LAS SECCIONES CON DOCENTES HORARIOS VACANTES ETC
Route::get('/listar-secciones', [MatriculaController::class, 'listarSecciones']);

//RUTA PRA LISTAR LOS HORARIOS DE UN CURSO EN TEORIA Y PRACTICA POR DOS IDS
Route::get('/listar-horarios-cursos', [MatriculaController::class, 'listarHorariosCurso']);

//RUTA PARA OBTENER LA ESPECIALIDADES DEL ALUMNO
Route::get('/obtener-especialidad', [MatriculaController::class, 'obtenerEspecialidadUsuario']);

//RUTA PARA OBTENER LAS SECCIONES POR AÑO
Route::get('matricula/secciones/{idAnho}', [MatriculaController::class, 'obtenerSeccionesPorAnho']);

// RUTA PARA GENERAR COMPROBANTE DE MATRICULA PDF (Web)
Route::post('web/matriculas/generar-comprobante', [WebMatriculaController::class, 'generarComprobante']);

//================================================================================================

//================================================================================================
// RUTAS PARA LOS REPORTES
Route::get('superadministrador/reporte-usuario-sede', [SedeController::class, 'obtenerReporteUsuarioPorSede']);
Route::get('superadministrador/reporte-sede', [SedeController::class, 'obtenerReportePorSede']);
Route::get('superadministrador/reporte-matricula-sede', [SedeController::class, 'descargarMatriculasPorSede']);

Route::get('admin/lista-curso', [AdminController::class, 'generarPdfCursos']);
Route::get('admin/lista-periodo', [AdminController::class, 'generarPdfPeriodos']);
Route::get('admin/lista-ciclo', [AdminController::class, 'generarPdfCiclos']);

Route::get('docente/reporte-notas', [DocenteController::class, 'reporteNotasPorDocente']);
Route::get('docente/reporte-horario', [DocenteController::class, 'descargarReporteHorario']);

Route::get('estudiante/reporte-perfil', [EstudianteController::class, 'exportarPerfilPdf']);

Route::get('bibliotecario/reporte-visitas', [BibliotecaController::class, 'exportarVisitasPdf']);
Route::get('bibliotecario/reporte-reservas', [BibliotecaController::class, 'descargarPdfReservas']);

// RUTA PARA EL REPORTE DE CURSOS DE UN ESTUDIANTE
Route::get('estudiante/reporte-cursos', [EstudianteController::class, 'reporteCursosEstudiante']);

// RUTA PARA EL REPORTE DE NOTAS DE UN ESTUDIANTE
Route::get('estudiante/reporte-notas', [EstudianteController::class, 'reporteNotasEstudiante']);

// RUTA PARA EL REPORTE DE HORARIO DE UN ESTUDIANTE
Route::get('estudiante/reporte-horario', [EstudianteController::class, 'reporteHorarioEstudiante']);

// RUTA PARA EL REPORTE DE PAGOS DE UN ESTUDIANTE
Route::get('estudiante/reporte-pagos', [EstudianteController::class, 'reportePagosEstudiante']);

// RUTA PARA EL REPORTE DE ASISTENCIAS DE UN ESTUDIANTE POR CURSO
Route::get('estudiante/reporte-asistencia-curso', [EstudianteController::class, 'listarAsistenciaPorCurso']);

// RUTA PARA OBTENER PAGOS CON INFORMACIÓN DETALLADA
Route::get('contador/pagos', [ContadorController::class, 'obtenerPagos']);

// RUTA PARA EL REPORTE DE ASISTENCIA PAI
Route::get('estudiante/reporte-asistencia-pai', [EstudianteController::class, 'obtenerReporteAsistenciaPai']);

//RUTA PARA OBTENER EL PLAN DE ESTUDIOS
Route::get('estudiante/obtener-plan-estudios', [EstudianteController::class, 'obtenerPlanEstudios']);

//RUTA PARA OBTENER EL PLAN DE ESTUDIOS
Route::get('estudiante/descargar-plan-estudios', [EstudianteController::class, 'descargarPlanEstudios']);

//RUTA PARA OBTENER REPORTE DE PAGOS
Route::get('estudiante/reporte-pagos', [EstudianteController::class, 'reportePagos']);

//RUTA PARA OBTENER REPORTE DE INGRESOS Y EGRESOS
Route::get('contador/reporte/ingresos_egresos', [ContadorController::class, 'reporteIngresosEgresos']);

// RUTA PARA OBTENER DEUDAS PENDIENTES DE UN ESTUDIANTE
Route::get('contador/deudas-pendientes', [ContadorController::class, 'obtenerDeudasPendientes']);

//================================================================================================
// RUTAS PARA LA BIBLIOTECA

// RUTA PARA OBTENER LAS ESTADÍSTICAS DE RESERVAS DE LA BIBLIOTECA
Route::get('biblioteca/reporte/estadisticas-reservas', [BibliotecaController::class, 'obtenerEstadisticasReservas']);

// Ruta para registrar visitas y obtener el reporte
Route::get('biblioteca/reporte/frecuencia_visitas', [BibliotecaController::class, 'obtenerFrecuenciaYRegistrarVisita']);

// RUTA PARA OBTENER LOS LIBROS SEGÚN CATEGORÍA, GÉNERO Y ESTADO
Route::get('biblioteca/libros', [BibliotecaController::class, 'obtenerLibros']);

// RUTA PARA CREAR LIBROS
Route::post('biblioteca/libros', [BibliotecaController::class, 'crearLibro']);

// RUTA PARA ACTUALIZAR LIBRO
Route::put('biblioteca/libros/{id_libro}', [BibliotecaController::class, 'actualizarLibro']);

// RUTA PARA OBTENER UN LIBRO POR ID 
Route::get('biblioteca/libros/{id_libro}', [BibliotecaController::class, 'obtenerLibroPorId']);

// RUTA PARA ELIMINAR UN LIBRO POR ID
Route::delete('biblioteca/libros/{id_libro}', [BibliotecaController::class, 'eliminarLibro']);

// RUTA PARA OBTENER RESERVAS POR ESTUDIANTE
Route::get('biblioteca/operaciones/reserva-estudiantes', [BibliotecaController::class, 'index']);

//RUTA PARA DESCARGAR LAS RESERVAS DE ESTUDIANTES
Route::get('biblioteca/operaciones/pdf-reserva-estudiantes', [BibliotecaController::class, 'descargarPdfReservas']);





//================================================================================================
// RUTAS PARA LOS DOCENTES

// RUTA PARA OBTENER LOS CURSOS ASIGNADOS A UN DOCENTE
Route::get('docente/cursos-asignados', [DocenteController::class, 'obtenerCursosAsignados']);

// RUTA PARA OBTENER LAS NOTAS DE LOS ESTUDIANTES
Route::get('docente/reporte-notas-estudiantes', [DocenteController::class, 'reporteNotasEstudiantes']);

// RUTA PARA OBTENER EL HORARIO DE UN DOCENTE
Route::get('docente/reporte-horario', [DocenteController::class, 'reporteHorario']);

// RUTA PARA OBTENER LAS ASISTENCIAS DE UN DOCENTE POR FECHA
Route::get('docente/asistencias', [DocenteController::class, 'obtenerAsistenciasDocente']);

Route::get('docente/listar-cursos/{idUsuario}', [DocenteAcademicoController::class, 'listarCursosPorDocente']);

//================================================================================================

//================================================================================================
// RUTAS PARA LAS CARRERAS

// RUTA PARA LISTAR CARRERAS
Route::get('carreras/listar', [CarreraController::class, 'listarCarreras']);

//================================================================================================

//================================================================================================
// RUTAS PARA ADMINISTRADOR VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:admin'])->group(function () {

    // RUTA PARA QUE SOLO LOS USUARIOS CON ROL ADMIN AGREGUEN USUARIOS
    Route::post('agregarUsuario/{tipoUsuario?}', [AdminController::class, 'registrarUsuario']);

    // RUTA PARA QUE SOLO LOS USUARIOS CON ROL ADMIN CREEN ROL
    Route::post('agregarRol', [AdminController::class, 'agregarRol']);

    // RUTA PARA QUE SOLO LOS USUARIOS CON ROL ADMIN AGREGEN CURSO
    Route::post('agregarCurso', [AdminController::class, 'agregarCurso']);

    // RUTA PARA QUE SOLO LOS USUARIOS CON ROL ADMIN AGREGEN MODALIDAD
    Route::post('agregarModalidad', [AdminController::class, 'agregarModalidad']);

    // RUTA PARA QUE SOLO LOS USUARIOS CON ROL ADMIN AGREGEN PERIODOS
    Route::post('agregarPeriodo', [AdminController::class, 'agregarPeriodo']);

    // RUTA PARA QUE SOLO LOS USUARIOS CON ROL ADMIN AGREGEN CICLOS
    Route::post('agregarCiclo', [AdminController::class, 'agregarCiclo']);
});


// RUTAS PARA ESTUDIANTE VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:estudiante'])->group(function () {

    // RUTA PARA QUE SOLO LOS USUARIOS CON ROL ESTUDIANTE Suban documentos(PRoceso matricula estudiante nuevo)
    Route::post('subirDocumentos', [EstudianteController::class, 'subirDocumentos']);

    // RUTA PARA QUE SOLO LOS USUARIOS CON ROL ESTUDIANTE  consulten horarios por idCurso (PRoceso matricula estudiante nuevo)
    Route::get('/horariosdocentecurso/{idCurso}', [EstudianteController::class, 'obtenerHorarioYDocentesPorCurso']);

    // RUTA OBETENR EL CURSO ACTUAL POR LA HORA ACTUAL Y PONERL EN LA UI DEL ESTUDAINTE
    Route::get('cursos-por-hora', [EstudianteController::class, 'obtenerCursoPorHoraActual']);

    // RUTA OBETENER LOS HORARIOS DE CURSOS CON DOCENTES POR EL IDUSUARIO
    Route::post('/listar-horarios-curso', [EstudianteController::class, 'listarHorariosCurso']);

    // RUTA OBETENER LOS PROMEDIOS DE CURSOS DEL ESTUDIANTE
    Route::get('/promedio-por-curso', [EstudianteController::class, 'obtenerPromedioPorCurso']);
});


// RUTAS PARA TOPICOS VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:topico'])->group(function () {});


//================================================================================================

