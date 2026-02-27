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
use App\Http\Controllers\Superadministrador\ClienteController as SuperAdminClienteController;
use App\Http\Controllers\Superadministrador\LicenciaController as SuperAdminLicenciaController;
use App\Http\Controllers\Superadministrador\MembresiaController as SuperAdminMembresiaController;
use App\Http\Controllers\Superadministrador\PlanesController as SuperAdminPlanesController;
use App\Http\Controllers\Superadministrador\FacturacionController as SuperAdminFacturacionController;
use App\Http\Controllers\Superadministrador\AuditoriaController as SuperAdminAuditoriaController;
use App\Http\Controllers\Superadministrador\ConfiguracionController as SuperAdminConfiguracionController;
use App\Http\Controllers\Superadministrador\ReportesController as SuperAdminReportesController;
use App\Http\Controllers\Superadministrador\AcademicoController as SuperAdminAcademicoController;
use App\Http\Controllers\Superadministrador\AsistenciaController as SuperAdminAsistenciaController;
use App\Http\Controllers\Superadministrador\LicenciaDashboardController as SuperAdminLicenciaDashController;
use App\Http\Controllers\Web\RecuperarPasswordController;
use App\Http\Controllers\Web\MembresiaGestionController;
use App\Http\Controllers\Superadministrador\MatriculaController as SuperAdminMatriculaController;
use App\Http\Controllers\Superadministrador\TramiteSuperAdminController as SuperAdminTramiteController;
use App\Http\Controllers\Admin\EstudianteController as AdminEstudianteController;
use App\Http\Controllers\Admin\DocenteController as AdminDocenteController;
use App\Http\Controllers\Admin\UsuarioController as AdminUsuarioController;
use App\Http\Controllers\Admin\AcademicoController as AdminAcademicoController;
use App\Http\Controllers\Admin\MensajeriaController as AdminMensajeriaController;
use App\Http\Controllers\Admin\MembresiaAdminController as AdminMembresiaController;
use App\Http\Controllers\Admin\RolController as AdminRolController;
use App\Http\Controllers\Admin\ModuloController as AdminModuloController;
use App\Http\Controllers\Admin\PlanEstudioController as AdminPlanEstudioController;
use App\Http\Controllers\Admin\FacturacionAdminController as AdminFacturacionController;
use App\Http\Controllers\Admin\ConfigEmpresaController as AdminConfigEmpresaController;
use App\Http\Controllers\Director\EstudianteController as DirectorEstudianteController;
use App\Http\Controllers\Director\DocenteController as DirectorDocenteController;
use App\Http\Controllers\Director\AcademicoController as DirectorAcademicoController;
use App\Http\Controllers\Director\AsistenciaController as DirectorAsistenciaController;
use App\Http\Controllers\Director\PendienteController as DirectorPendienteController;
use App\Http\Controllers\Director\DirectorDashboardController;
use App\Http\Controllers\Director\TramiteDirectorController as DirectorTramiteController;
use App\Http\Controllers\Director\ReporteDirectorController as DirectorReporteController;
use App\Http\Controllers\Docente\AcademicoController as DocenteAcademicoController;
use App\Http\Controllers\Docente\ArchivoController as DocenteArchivoController;
use App\Http\Controllers\Docente\MensajeriaController as DocenteMensajeriaController;
use App\Http\Controllers\Docente\AsistenciaController as DocenteAsistenciaController;
use App\Http\Controllers\Docente\MiAsistenciaController as DocenteMiAsistenciaController;
use App\Http\Controllers\Docente\EvaluacionNotaController as DocenteEvaluacionNotaController;
use App\Http\Controllers\Docente\EvaluacionCriterioController as DocenteEvaluacionCriterioController;
use App\Http\Controllers\Docente\ForoController as DocenteForoController;
use App\Http\Controllers\Docente\ClasesEnVivoController as DocenteClasesEnVivoController;
use App\Http\Controllers\Docente\NotificacionController as DocenteNotificacionController;
use App\Http\Controllers\Docente\BancoPreguntasController as DocenteBancoPreguntasController;
use App\Http\Controllers\Estudiante\MiCarreraController as EstudianteMiCarreraController;
use App\Http\Controllers\Estudiante\MiPerfilController as EstudianteMiPerfilController;
use App\Http\Controllers\Estudiante\MiNotaController as EstudianteMiNotaController;
use App\Http\Controllers\Estudiante\MiAcademicoController as EstudianteMiAcademicoController;
use App\Http\Controllers\Estudiante\ArchivoController as EstudianteArchivoController;
use App\Http\Controllers\Estudiante\MiMatriculaController as EstudianteMiMatriculaController;
use App\Http\Controllers\Estudiante\ReporteController as EstudianteReporteController;
use App\Http\Controllers\Estudiante\CertificadoController as EstudianteCertificadoController;
use App\Http\Controllers\Estudiante\ResenaController as EstudianteResenaController;
use App\Http\Controllers\Estudiante\HistorialPagoController as EstudianteHistorialPagoController;
use App\Http\Controllers\Estudiante\ForoEstudianteController as EstudianteForoController;
use App\Http\Controllers\Estudiante\ListaDeseosController as EstudianteListaDeseosController;
use App\Http\Controllers\Padre\PadreController;
use App\Http\Controllers\Padre\MensajeriaDocenteController as PadreMensajeriaController;
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
use App\Http\Controllers\Superadministrador\UsuarioController as SuperAdminUsuarioController;
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
use App\Http\Controllers\Web\PlanEstudioController as WebPlanEstudioController;
use App\Http\Controllers\Web\MiMembresiaController as WebMiMembresiaController;
use App\Http\Controllers\Web\MiMatriculaController as WebMiMatriculaController;
use App\Http\Controllers\Web\ClienteController as ClienteController;


//RUTAS

Route::group(['prefix' => 'superadministrador', 'middleware' => ['CheckUserRoleMW:superadministrador']], function () {
    //membresias
    Route::get('/membresias/tipo/{id}', [MembresiaTipoController::class, 'show']);
    Route::get('/membresias/tipo', [MembresiaTipoController::class, 'activos']);
    Route::post('/membresias/tipo', [MembresiaTipoController::class, 'store']); // <-- crear nuevo


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
    Route::get('vendedores/{id_vendedor}', [SuperAdminVendedorController::class, 'show']);
    Route::post('vendedores', [SuperAdminVendedorController::class, 'store']);
    Route::put('vendedores/{id_vendedor}', [SuperAdminVendedorController::class, 'update']);
    Route::delete('vendedores/{id_vendedor}', [SuperAdminVendedorController::class, 'destroy']);
    // Clientes
    Route::get('clientes', [SuperAdminClienteController::class, 'index']);
    Route::get('clientes/{id_cliente}', [SuperAdminClienteController::class, 'show']);
    Route::post('clientes', [SuperAdminClienteController::class, 'store']);
    Route::put('clientes/{id_cliente}', [SuperAdminClienteController::class, 'update']);
    Route::delete('clientes/{id_cliente}', [SuperAdminClienteController::class, 'destroy']);
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
    // Rutas de gestión de usuarios (NUEVO - UsuarioController implementado)
    Route::get('usuarios', [SuperAdminUsuarioController::class, 'index']);
    Route::get('usuarios/{id}', [SuperAdminUsuarioController::class, 'show']);
    Route::post('usuarios', [SuperAdminUsuarioController::class, 'store']);
    Route::put('usuarios/{id}', [SuperAdminUsuarioController::class, 'update']);
    Route::delete('usuarios/{id}', [SuperAdminUsuarioController::class, 'destroy']);
    Route::post('usuarios/{id}/asignar-rol', [SuperAdminUsuarioController::class, 'asignarRol']);
    Route::post('usuarios/{id}/cambiar-sede', [SuperAdminUsuarioController::class, 'cambiarSede']);
    Route::post('usuarios/{id}/reset-password', [SuperAdminUsuarioController::class, 'resetPassword']);
    Route::post('usuarios/{id}/toggle-estado', [SuperAdminUsuarioController::class, 'toggleEstado']);
    Route::get('roles', [SuperAdminUsuarioController::class, 'listarRoles']);
    Route::get('sedes-listado', [SuperAdminUsuarioController::class, 'listarSedes']);
    //Route::get('tramites', [SuperAdminTramiteController::class, 'index']);
    //Route::post('tramites', [SuperAdminTramiteController::class, 'store']);
    //Route::get('tramites/{id}', [SuperAdminTramiteController::class, 'show']);
    //Route::put('tramites/{id}', [SuperAdminTramiteController::class, 'update']);
    //Route::delete('tramites/{id}', [SuperAdminTramiteController::class, 'destroy']);
    //Route::get('tramites/estado/{estado}', [SuperAdminTramiteController::class, 'porEstado']);
    // Licencias
    Route::get('licencias', [SuperAdminLicenciaController::class, 'index']);
    Route::get('licencias/stats', [SuperAdminLicenciaController::class, 'stats']);
    Route::get('licencias/{id}', [SuperAdminLicenciaController::class, 'show']);
    Route::post('licencias/{id}/activar', [SuperAdminLicenciaController::class, 'activar']);
    Route::post('licencias/{id}/desactivar', [SuperAdminLicenciaController::class, 'desactivar']);
    Route::post('licencias/{id}/renovar', [SuperAdminLicenciaController::class, 'renovar']);
    // Dashboard de uso y gestión avanzada de licencias
    Route::get('licencias/dashboard-uso', [SuperAdminLicenciaDashController::class, 'dashboardUso']);
    Route::get('licencias/alertas', [SuperAdminLicenciaDashController::class, 'alertas']);
    Route::get('licencias/modulos', [SuperAdminLicenciaDashController::class, 'modulos']);
    Route::post('licencias/{id}/modulos', [SuperAdminLicenciaDashController::class, 'toggleModulo']);
    Route::get('licencias/{id}/onboarding', [SuperAdminLicenciaDashController::class, 'onboarding']);
    Route::get('licencias/{id}/limites', [SuperAdminLicenciaDashController::class, 'limites']);
    // Membresias
    Route::get('membresias', [SuperAdminMembresiaController::class, 'index']);
    Route::get('membresias/stats', [SuperAdminMembresiaController::class, 'stats']);
    Route::get('membresias/{id}', [SuperAdminMembresiaController::class, 'show']);
    Route::post('membresias/{id}/activar', [SuperAdminMembresiaController::class, 'activar']);
    Route::post('membresias/{id}/desactivar', [SuperAdminMembresiaController::class, 'desactivar']);
    // Planes (tipos)
    Route::get('planes/membresia-tipos', [SuperAdminPlanesController::class, 'indexMembresiaTipo']);
    Route::post('planes/membresia-tipos', [SuperAdminPlanesController::class, 'storeMembresiaTipo']);
    Route::put('planes/membresia-tipos/{id}', [SuperAdminPlanesController::class, 'updateMembresiaTipo']);
    Route::get('planes/licencia-tipos', [SuperAdminPlanesController::class, 'indexLicenciaTipo']);
    Route::post('planes/licencia-tipos', [SuperAdminPlanesController::class, 'storeLicenciaTipo']);
    Route::put('planes/licencia-tipos/{id}', [SuperAdminPlanesController::class, 'updateLicenciaTipo']);
    // Facturación
    Route::get('facturacion', [SuperAdminFacturacionController::class, 'index']);
    Route::get('facturacion/resumen', [SuperAdminFacturacionController::class, 'resumen']);
    // Auditoría
    Route::get('auditoria', [SuperAdminAuditoriaController::class, 'index']);
    Route::get('auditoria/stats', [SuperAdminAuditoriaController::class, 'stats']);
    // Configuración global
    Route::get('configuracion', [SuperAdminConfiguracionController::class, 'index']);
    Route::put('configuracion', [SuperAdminConfiguracionController::class, 'update']);
    Route::get('configuracion/stats', [SuperAdminConfiguracionController::class, 'stats']);
    // Reportes de crecimiento
    Route::get('reportes/crecimiento', [SuperAdminReportesController::class, 'crecimiento']);
    Route::get('reportes/dashboard', [SuperAdminReportesController::class, 'dashboard']);
    // Usuarios pendientes
    Route::get('usuarios-pendientes', [SuperAdminReportesController::class, 'pendientesUsuarios']);
    Route::post('usuarios-pendientes/{id}/aprobar', [SuperAdminReportesController::class, 'aprobarUsuario']);
    Route::delete('usuarios-pendientes/{id}/rechazar', [SuperAdminReportesController::class, 'rechazarUsuario']);
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
    Route::get('tramites', [SuperAdminTramiteController::class, 'index']);
    Route::post('tramites', [SuperAdminTramiteController::class, 'store']);
    Route::get('tramites/{id}', [SuperAdminTramiteController::class, 'show']);
    Route::put('tramites/{id}', [SuperAdminTramiteController::class, 'update']);
    Route::delete('tramites/{id}', [SuperAdminTramiteController::class, 'destroy']);
    Route::get('tramites/estado/{estado}', [SuperAdminTramiteController::class, 'porEstado']);
    Route::get('matriculas/estudiantes-activos', [SuperAdminMatriculaController::class, 'estudiantesActivos']);
    Route::get('matriculas/cursos-activos', [SuperAdminMatriculaController::class, 'cursosActivos']);
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
    Route::get('membresias-sede', [AdminMembresiaController::class, 'listarPorSede']);
    // Facturación Admin
    Route::get('facturacion', [AdminFacturacionController::class, 'index']);
    Route::get('facturacion/resumen', [AdminFacturacionController::class, 'resumen']);
    // Configuración de empresa
    Route::get('config-empresa', [AdminConfigEmpresaController::class, 'show']);
    Route::put('config-empresa', [AdminConfigEmpresaController::class, 'update']);
    Route::get('config-empresa/stats', [AdminConfigEmpresaController::class, 'stats']);
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

    // Pendientes CRUD
    Route::get('pendientes', [DirectorPendienteController::class, 'index']);
    Route::post('pendientes', [DirectorPendienteController::class, 'store']);
    Route::put('pendientes/{id}', [DirectorPendienteController::class, 'update']);
    Route::delete('pendientes/{id}', [DirectorPendienteController::class, 'destroy']);

    // Dashboard indicadores educativos
    Route::get('dashboard/indicadores', [DirectorDashboardController::class, 'indicadoresEducativos']);
    Route::get('dashboard/comparar-periodos', [DirectorDashboardController::class, 'compararPeriodos']);
    Route::get('dashboard/progreso-carrera', [DirectorDashboardController::class, 'progresoCarrera']);
    Route::get('dashboard/rendimiento-docentes', [DirectorDashboardController::class, 'rendimientoDocentes']);
    Route::get('dashboard/estudiantes-riesgo', [DirectorDashboardController::class, 'estudiantesEnRiesgo']);

    // Tramites (solo lectura)
    Route::get('tramites', [DirectorTramiteController::class, 'index']);
    Route::get('tramites/estadisticas', [DirectorTramiteController::class, 'estadisticas']);

    // Reportes descargables
    Route::get('reportes/estudiantes/pdf', [DirectorReporteController::class, 'reporteEstudiantesPdf']);
    Route::get('reportes/estudiantes/excel', [DirectorReporteController::class, 'reporteEstudiantesExcel']);
    Route::get('reportes/docentes/pdf', [DirectorReporteController::class, 'reporteDocentesPdf']);
    Route::get('reportes/docentes/excel', [DirectorReporteController::class, 'reporteDocentesExcel']);
    Route::get('reportes/rendimiento/pdf', [DirectorReporteController::class, 'reporteRendimientoPdf']);
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
    // Foros de discusión
    Route::get('foro-temas', [DocenteForoController::class, 'indexTema']);
    Route::post('foro-temas', [DocenteForoController::class, 'storeTema']);
    Route::get('foro-respuestas', [DocenteForoController::class, 'indexRespuesta']);
    Route::post('foro-respuestas', [DocenteForoController::class, 'storeRespuesta']);
    // Clases en vivo
    Route::get('clases-vivo', [DocenteClasesEnVivoController::class, 'index']);
    Route::post('clases-vivo', [DocenteClasesEnVivoController::class, 'store']);
    Route::put('clases-vivo/{id}', [DocenteClasesEnVivoController::class, 'update']);
    Route::delete('clases-vivo/{id}', [DocenteClasesEnVivoController::class, 'destroy']);
    // Notificaciones
    Route::get('notificaciones', [DocenteNotificacionController::class, 'index']);
    Route::post('notificaciones', [DocenteNotificacionController::class, 'store']);
    Route::put('notificaciones/{id}/leer', [DocenteNotificacionController::class, 'marcarLeida']);
    Route::get('notificaciones/no-leidas', [DocenteNotificacionController::class, 'noLeidas']);
    // Banco de preguntas
    Route::get('banco-preguntas', [DocenteBancoPreguntasController::class, 'index']);
    Route::post('banco-preguntas', [DocenteBancoPreguntasController::class, 'store']);
    Route::get('banco-preguntas/{id}', [DocenteBancoPreguntasController::class, 'show']);
    Route::put('banco-preguntas/{id}', [DocenteBancoPreguntasController::class, 'update']);
    Route::delete('banco-preguntas/{id}', [DocenteBancoPreguntasController::class, 'destroy']);
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

    // Hito 5: Validación de acceso al curso pagado
    Route::get('courses/{id}/access', [EstudianteCertificadoController::class, 'checkCourseAccess']);

    // Hito 6: Registro de progreso por lección
    Route::post('progress/lesson', [EstudianteCertificadoController::class, 'registerLessonProgress']);

    // Hito 11: Descarga de certificado por course_id
    Route::get('certificates/{course_id}/download', [EstudianteCertificadoController::class, 'downloadCertificateByCourse']);

    // Reseñas de cursos
    Route::get('mis-resenas', [EstudianteResenaController::class, 'index']);
    Route::post('mis-resenas', [EstudianteResenaController::class, 'store']);
    // Historial de pagos
    Route::get('historial-pagos', [EstudianteHistorialPagoController::class, 'index']);
    Route::get('historial-pagos/resumen', [EstudianteHistorialPagoController::class, 'resumen']);
    // Foros del estudiante
    Route::get('foro-temas', [EstudianteForoController::class, 'indexTema']);
    Route::get('foro-respuestas', [EstudianteForoController::class, 'indexRespuesta']);
    Route::post('foro-respuestas', [EstudianteForoController::class, 'storeRespuesta']);
    // Seguimiento de trámites propios
    Route::get('mis-tramites', [EstudianteForoController::class, 'misTramites']);
    // Lista de deseos
    Route::get('lista-deseos', [EstudianteListaDeseosController::class, 'index']);
    Route::post('lista-deseos', [EstudianteListaDeseosController::class, 'store']);
    Route::delete('lista-deseos/{id}', [EstudianteListaDeseosController::class, 'destroy']);
});
Route::group(['prefix' => 'padre', 'middleware' => ['CheckUserRoleMW:padre']], function () {
    Route::get('dashboard', [PadreController::class, 'dashboard']);
    Route::get('mis-hijos', [PadreController::class, 'misHijos']);
    Route::get('mis-hijos/{idHijo}/cursos', [PadreController::class, 'cursosHijo']);
    Route::get('mis-hijos/{idHijo}/notas', [PadreController::class, 'notasHijo']);
    Route::get('mis-hijos/{idHijo}/asistencia', [PadreController::class, 'asistenciaHijo']);
    Route::get('mis-hijos/{idHijo}/pagos', [PadreController::class, 'pagosHijo']);
    // Mensajería con docentes
    Route::get('mensajes', [PadreMensajeriaController::class, 'index']);
    Route::get('mensajes/docentes', [PadreMensajeriaController::class, 'docentesHijos']);
    Route::post('mensajes', [PadreMensajeriaController::class, 'store']);
    Route::put('mensajes/{id}/leer', [PadreMensajeriaController::class, 'marcarLeido']);
});
Route::group(['prefix' => 'tutor', 'middleware' => ['CheckUserRoleMW:tutor']], function () {
    // Rutas específicas para el rol Tutor
    // TODO: Implementar endpoints según necesidades del rol
});
Route::group(['prefix' => 'vendedor', 'middleware' => ['CheckUserRoleMW:vendedor']], function () {
    // Rutas específicas para el rol Vendedor
    // TODO: Implementar endpoints según necesidades del rol
});
Route::group(['prefix' => 'bibliotecario', 'middleware' => ['CheckUserRoleMW:bibliotecario']], function () {
    // Rutas específicas para el rol Bibliotecario
    // TODO: Implementar endpoints según necesidades del rol
});
Route::group(['prefix' => 'topicomedico', 'middleware' => ['CheckUserRoleMW:topicomedico']], function () {
    // Rutas específicas para el rol Tópico Médico
    // TODO: Implementar endpoints según necesidades del rol
});
Route::group(['prefix' => 'contador', 'middleware' => ['CheckUserRoleMW:contador']], function () {
    // Rutas específicas para el rol Contador
    // TODO: Implementar endpoints según necesidades del rol
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

    Route::get('tipo-categorias', [SetupCategoriaController::class, 'index']);
    Route::get('visualizar-archivos/{id_archivo}', [SetupArchivoController::class, 'visualizar']);
    Route::get('archivos', [SetupArchivoController::class, 'index']);
    Route::get('descargar-archivos/{id_archivo}', [SetupArchivoController::class, 'descargar']);
    Route::get('archivos/{id_archivo}/visualizar-imagenes', [SetupArchivoController::class, 'imagen']);
});

// Ruta pública para visualizar imágenes de cursos (sin autenticación)
Route::get('setup/archivos/{id_archivo}/imagen-publica', [SetupArchivoController::class, 'imagenPublica']);

// Ruta pública para listar tipos de documentos
Route::get('setup/tipo-documentos', [SetupTipoDocumentoController::class, 'index']);

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
    //Licencia
    Route::get('licencias/tipo-activos', [WebLicenciaController::class, 'tipoActivos']);
    Route::post('licencias', [WebLicenciaController::class, 'store']);
    // Activar / Desactivar licencia
    Route::patch('licencias/{id}/toggle', [WebLicenciaController::class, 'toggle']);
    // Renovar licencia
    Route::patch('licencias/{id}/renew', [WebLicenciaController::class, 'renew']);
    // Crear un nuevo tipo de licencia
    Route::post('licencias/tipo', [WebLicenciaController::class, 'storeTipo']);
    // Listar licencias
    Route::get('licencias', [WebLicenciaController::class, 'index']);

    Route::get('carreras', [WebCarreraController::class, 'activas']);
    Route::get('carreras/tipo-titulo-academico', [WebCarreraController::class, 'tipoTituloAcademicos']);
    Route::get('tipo-categorias/por-temas', [WebTipoCategoriaController::class, 'porTemas']);
    Route::get('temas', [WebTemaController::class, 'index']);
    Route::get('empresas', [WebEmpresaController::class, 'index']);
    Route::get('planes-publicados', [WebPlanEstudioController::class, 'publicados']);
    Route::get('planes-publicados/{id_planestudio}', [WebPlanEstudioController::class, 'showPublicado']);
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

// RUTA PARA INICIAR SESION (con rate limiting: 5 intentos por 5 minutos)
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,5');
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

// RUTA PARA OBTENER TRAMITES DEL ESTUDIANTE
Route::get('estudiante/mis-tramites', [EstudianteController::class, 'misTramites']);

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
// RUTAS PARA GESTIÓN DE USUARIO LOGEADO (NUEVO - Reemplaza window.location.reload)
//================================================================================================

use App\Http\Controllers\UserController;

Route::group(['prefix' => 'user', 'middleware' => ['CheckUserMW:user']], function () {
    // Cambiar sede sin recargar página
    Route::post('change-empresa', [UserController::class, 'changeEmpresa']);
    // Cambiar rol sin recargar página
    Route::post('change-rol', [UserController::class, 'changeRol']);
    // Cambiar sede y rol simultáneamente
    Route::post('change-sede-rol', [UserController::class, 'changeSedeAndRol']);
    // Obtener datos actualizados del usuario
    Route::get('me', [UserController::class, 'me']);
});

//================================================================================================

//================================================================================================
// RUTAS PÚBLICAS (SIN AUTENTICACIÓN) - Portada
//================================================================================================
Route::get('plan-estudios-publicados', [WebPlanEstudioController::class, 'publicados']);
// Recuperar contraseña (rutas públicas, sin autenticación)
Route::post('recuperar-password/solicitar', [RecuperarPasswordController::class, 'solicitar']);
Route::post('recuperar-password/verificar-token', [RecuperarPasswordController::class, 'verificarToken']);
Route::post('recuperar-password/resetear', [RecuperarPasswordController::class, 'resetear']);

// Membresía personal (usuario web autenticado)
Route::middleware(['CheckUserMW:user'])->group(function () {
    Route::get('mi-membresia/historial', [MembresiaGestionController::class, 'historial']);
    Route::get('mi-membresia/verificar-activa', [MembresiaGestionController::class, 'verificarActiva']);
    Route::get('mi-membresia/periodo-gracia', [MembresiaGestionController::class, 'periodoGracia']);
    Route::post('mi-membresia/{id}/renovar', [MembresiaGestionController::class, 'renovar']);
    Route::post('mi-membresia/{id}/cancelar', [MembresiaGestionController::class, 'cancelar']);
    Route::post('mi-membresia/{id}/cambiar-plan', [MembresiaGestionController::class, 'cambiarPlan']);
});

