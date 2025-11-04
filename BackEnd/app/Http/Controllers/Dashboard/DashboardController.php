<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Sedes;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use App\Models\Rol;
use App\Models\UsuarioRol;
use App\Models\UsuarioSede;
use App\Models\Asistencia;
use App\Models\AsistenciaDocente;
use App\Models\AsistenciaEstudiante;
use Illuminate\Support\Facades\DB;
use App\Models\Matricula_Sede;
use App\Models\Periodo;
use App\Models\Ciclo;
use App\Models\CicloCursos;
use App\Models\Curso;
use App\Models\CursoDocentes;
use App\Models\CursoEstudiantes;
use App\Models\CursoHorarioEstudiantes;
use App\Models\Deuda;
use App\Models\Devolucion;
use App\Models\Doctor;
use App\Models\Especialidad;
use App\Models\EvaluacionesNotas;
use App\Models\HorarioCurso;
use App\Models\HorarioDoctor;
use App\Models\Libro;
use App\Models\MatriculaPagos;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\PlanEstudio;
use App\Models\PlanEstudioCiclo;
use App\Models\PlanEstudioCurso;
use App\Models\Reserva;
use App\Models\TareasAlumno;
use App\Models\TareasCurso;
use App\Models\Tramite;
use App\Models\Usuario;
use App\Models\VisitasBiblioteca;
use App\Http\Controllers\Controller;
use App\Models\CursoAsistencia;
use App\Models\Matricula;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //ROL SUPER-ADMIN:
    public function getcantidadSedes(): JsonResponse
    {
        $cantidad = Empresa::count();
        return response()->json(['cantidad' => $cantidad]);
    }

    public function getCantidadDocentes(): JsonResponse
    {
        return $this->getCantidadPorRol('docente');
    }

    public function getCantidadEstudiantes(): JsonResponse
    {
        return $this->getCantidadPorRol('student');
    }

    public function getCantidadPadres(): JsonResponse
    {
        return $this->getCantidadPorRol('padre');
    }

    public function getCantidadPorRol(string $nombre): JsonResponse
    {
        $rol = Rol::where('nombre', $nombre)->firstOrFail();

        $cantidad = UsuarioRol::where('id_rol', $rol->id_rol)->count();

        return response()->json(['cantidad' => $cantidad]);
    }

    public function getCantidadUsuariosPorSede(string $nombre): JsonResponse
    {
        $rol = Rol::where('nombre', $nombre)->firstOrFail();

        $resultados = UsuarioRol::join('usuario', 'usuario_rol.id_usuario', '=', 'usuario.id_usuario')
            ->join('empresa', 'usuario.id_empresa', '=', 'empresa.id_empresa')
            ->where('usuario_rol.id_rol', $rol->id_rol)
            ->select(
                'usuario.id_empresa',
                'empresa.razon_social as nombre_sede',
                DB::raw('COUNT(DISTINCT usuario.id_usuario) as cantidad')
            )
            ->groupBy('usuario.id_empresa', 'empresa.razon_social')
            ->get()
            ->map(function ($item) {
                return [
                    'id_sede' => $item->id_empresa,
                    'nombre_sede' => $item->nombre_sede,
                    'cantidad' => $item->cantidad
                ];
            });

        return response()->json([
            'data' => $resultados
        ]);
    }

    public function getCantidadSedeDocentes(): JsonResponse
    {
        return $this->getCantidadUsuariosPorSede('docente');
    }

    public function getCantidadSedeEstudiantes(): JsonResponse
    {
        return $this->getCantidadUsuariosPorSede('student');
    }

    public function getCantidadSedePadres(): JsonResponse
    {
        return $this->getCantidadUsuariosPorSede('padre');
    }

    public function getNombreSedes(): JsonResponse
    {
        $empresas = Empresa::all();

        return response()->json([
            'data' => $empresas
        ]);
    }

    public function getPorcentajesAsistencia($id_empresa): JsonResponse
    {
        $asistio = DB::table('asistencia')
            ->where('tipo', 'D') // solo docentes
            ->where('id_empresa', $id_empresa)
            ->whereIn('estado', ['P', 'T']) // presente o tarde
            ->count();

        $noAsistio = DB::table('asistencia')
            ->where('tipo', 'D') // solo docentes
            ->where('id_empresa', $id_empresa)
            ->whereNotIn('estado', ['P', 'T']) // falta o justificado
            ->count();

        $total = $asistio + $noAsistio;

        $porcentajeAsistio = $total > 0 ? round(($asistio / $total) * 100, 2) : 0;
        $porcentajeNoAsistio = $total > 0 ? round(($noAsistio / $total) * 100, 2) : 0;

        return response()->json([
            'porcentaje_asistio' => $porcentajeAsistio,
            'porcentaje_no_asistio' => $porcentajeNoAsistio
        ]);
    }

    public function getCantidadMatriculasPorSede(Request $request): JsonResponse
    {
        $filter = $request->input('text_search', 'mensual');

        $query = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id_estudiante')
            ->join('empresa', 'estudiante.id_empresa', '=', 'empresa.id_empresa'); // Obtener nombre de sede

        // Aplicar filtro por fecha
        switch (strtolower($filter)) {
            case 'hoy':
                $query->whereDate('matricula.fechareg', Carbon::today());
                break;

            case '15dias':
                $query->where('matricula.fechareg', '>=', Carbon::now()->subDays(15)->startOfDay());
                break;

            case 'mensual':
                $query->whereBetween('matricula.fechareg', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]);
                break;

            case 'trimestral':
                $query->whereBetween('matricula.fechareg', [
                    Carbon::now()->startOfQuarter(),
                    Carbon::now()->endOfQuarter()
                ]);
                break;

            case 'anual':
                $query->whereBetween('matricula.fechareg', [
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfYear()
                ]);
                break;
        }

        // Agrupar por sede y contar matrículas
        $matriculasPorSede = $query
            ->select('empresa.razon_social', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('empresa.razon_social')
            ->orderByDesc('cantidad') // Opcional: ordenar por más matrículas
            ->get();

        return response()->json($matriculasPorSede);
    }

    //ROL ADMIN
    public function getNombrePeriodo(): JsonResponse
    {
        try {
            $periodos = Periodo::select('id_periodo', 'nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $periodos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los periodos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getNombreCiclo(Request $request): JsonResponse
    {
        try {
            $idPeriodo = $request->input('idPeriodo');

            $ciclos = Ciclo::select('id_ciclo', 'nombre')
                ->where('idPeriodo', $idPeriodo)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $ciclos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los ciclos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getNombreCurso(): JsonResponse
    {
        try {
            $cursos = Curso::select('id_curso', 'nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cursos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los cursos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarEvaluacionesNotas(): JsonResponse
    {
        $fechaInicio = now()->subMonths(5)->startOfMonth();

        // Join evaluaciones con notas
        $evaluaciones = DB::table('evaluaciones_notas')
            ->join('curso_evaluaciones', 'evaluaciones_notas.idEvaluacionNota', '=', 'curso_evaluaciones.idCursoEvaluacion')
            ->where('curso_evaluaciones.fechaEvaluacion', '>=', $fechaInicio)
            ->select(
                'curso_evaluaciones.fechaEvaluacion',
                'evaluaciones_notas.nota'
            )
            ->get()
            ->groupBy(function ($item) {
                return $item->fechaEvaluacion;
            });

        $resultado = [];
        $totalAprobados = 0;
        $totalDesaprobados = 0;

        foreach ($evaluaciones as $fecha => $notas) {
            $aprobados = $notas->where('nota', '>=', 15)->count();
            $desaprobados = $notas->where('nota', '<', 15)->count();

            $resultado[] = [
                'fecha' => $fecha,
                'aprobados' => $aprobados,
                'desaprobados' => $desaprobados
            ];

            $totalAprobados += $aprobados;
            $totalDesaprobados += $desaprobados;
        }

        usort($resultado, function ($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'evaluaciones_por_mes' => $resultado,
                'totales' => [
                    'aprobados' => $totalAprobados,
                    'desaprobados' => $totalDesaprobados
                ]
            ]
        ]);
    }


    public function listarMovimientos(Request $request): JsonResponse
    {
        $filter = $request->input('text_search', 'mensual');

        $query = DB::table('movimientos');

        switch (strtolower($filter)) {
            case 'hoy':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                $groupByFormat = '%Y-%m-%d';
                break;

            case '15dias':
                $startDate = Carbon::now()->subDays(15);
                $endDate = Carbon::now();
                $groupByFormat = '%Y-%m-%d';
                break;

            case 'mensual':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $groupByFormat = '%Y-%m-%d';
                break;

            case 'trimestral':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                $groupByFormat = '%Y-%m-%d';
                break;

            case 'anual':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                $groupByFormat = '%Y-%m';
                break;

            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $groupByFormat = '%Y-%m-%d';
                break;
        }

        $query->whereBetween('fecha', [$startDate, $endDate]);

        // Totales
        $resultados = (clone $query)->select(
            DB::raw('SUM(CASE WHEN tipo = "I" THEN monto ELSE 0 END) as ingresos'),
            DB::raw('SUM(CASE WHEN tipo = "E" THEN monto ELSE 0 END) as egresos')
        )->first();

        // Datos agrupados para el gráfico
        $chartData = (clone $query)
            ->selectRaw("DATE_FORMAT(fecha, '{$groupByFormat}') as period")
            ->selectRaw('SUM(CASE WHEN tipo = "I" THEN monto ELSE 0 END) as income')
            ->selectRaw('SUM(CASE WHEN tipo = "E" THEN monto ELSE 0 END) as expense')
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'filtro_aplicado' => $filter,
            'ingresos' => (float) ($resultados->ingresos ?? 0),
            'egresos' => (float) ($resultados->egresos ?? 0),
            'data' => $chartData
        ]);
    }


    public function listarMovimientoPorTipo(Request $request)
    {
        $request->validate([
            'tipo' => 'nullable|in:I,E,todos',
        ]);

        $tipoFiltro = $request->input('tipo', 'todos');

        $query = DB::table('movimientos')
            ->leftJoin('metodo_pago', 'movimientos.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
            ->select([
                DB::raw('movimientos.descripcion as Descripción'),
                'movimientos.fecha as Fecha',
                'movimientos.monto as Monto',
                'metodo_pago.nombre as Método de pago',
                'movimientos.tipo as Tipo'
            ]);

        if ($tipoFiltro !== 'todos') {
            $query->where('movimientos.tipo', $tipoFiltro);
        }

        $movimientos = $query->orderBy('movimientos.fecha', 'desc')->get();

        return response()->json([
            'success' => true,
            'filtro_aplicado' => $tipoFiltro,
            'data' => $movimientos
        ]);
    }

    public function ingresosPorMetodoPago()
    {
        $ingresos = DB::table('movimientos')
            ->join('metodo_pago', 'movimientos.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
            ->where('movimientos.tipo', 'I')
            ->select('metodo_pago.nombre as metodopago_descripcion', DB::raw('SUM(movimientos.monto) as total'))
            ->groupBy('metodo_pago.nombre')
            ->orderBy('metodo_pago.nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ingresos
        ]);
    }


    public function porcentajeIngresosPorMetodoPago()
    {
        $ingresos = DB::table('movimientos')
            ->join('metodo_pago', 'movimientos.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
            ->where('movimientos.tipo', 'I')
            ->select('metodo_pago.nombre as metodopago_descripcion', DB::raw('SUM(movimientos.monto) as total'))
            ->groupBy('metodo_pago.nombre')
            ->orderBy('metodo_pago.nombre')
            ->get();

        $totalGeneral = $ingresos->sum('total');

        $ingresosConPorcentaje = $ingresos->map(function ($item) use ($totalGeneral) {
            $porcentaje = $totalGeneral > 0 ? ($item->total / $totalGeneral) * 100 : 0;

            return [
                'metodo_pago' => $item->metodopago_descripcion,
                'porcentaje' => round($porcentaje, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $ingresosConPorcentaje,
        ]);
    }


    public function porcentajeEgresosPorMetodoPago()
    {
        $egresos = DB::table('movimientos')
            ->join('metodo_pago', 'movimientos.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
            ->where('movimientos.tipo', 'E')
            ->select('metodo_pago.nombre as metodopago_descripcion', DB::raw('SUM(movimientos.monto) as total'))
            ->groupBy('metodo_pago.nombre')
            ->orderBy('metodo_pago.nombre')
            ->get();

        $totalGeneral = $egresos->sum('total');

        $egresosConPorcentaje = $egresos->map(function ($item) use ($totalGeneral) {
            $porcentaje = $totalGeneral > 0 ? ($item->total / $totalGeneral) * 100 : 0;

            return [
                'metodo_pago' => $item->metodopago_descripcion,
                'porcentaje' => round($porcentaje, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $egresosConPorcentaje,
        ]);
    }


    /* ROL BIBLIOTECARIO */
    public function cantidadReservasEstudiantes(): JsonResponse
    {
        $cantidadReservas = DB::table('reservas')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('estudiante')
                    ->whereColumn('estudiante.id_estudiante', 'reservas.idUsuario');
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'cantidad_reservas' => $cantidadReservas,
            ]
        ]);
    }

    public function cantidadReservasDocentes(): JsonResponse
    {
        $cantidadReservas = DB::table('reservas')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('docente')
                    ->whereColumn('docente.id_docente', 'reservas.idUsuario');
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'cantidad_reservas' => $cantidadReservas,
            ]
        ]);
    }

    public function listarDevolucionesAtrasadas(): JsonResponse
    {
        $totalDevoluciones = DB::table('devoluciones')->count();

        $devolucionesAtrasadas = DB::table('devoluciones')
            ->where('estado', 1) // 1 = Atrasado
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_devoluciones' => $totalDevoluciones,
                'total_atrasadas' => $devolucionesAtrasadas,
                'porcentaje_atrasadas' => $totalDevoluciones > 0
                    ? round(($devolucionesAtrasadas / $totalDevoluciones) * 100, 2)
                    : 0
            ]
        ]);
    }

    public function listarVisitasPorMes(): JsonResponse
    {
        $visitasPorMes = DB::table('visitas_biblioteca')
            ->selectRaw('YEAR(fecha_visita) as anho, MONTH(fecha_visita) as mes, COUNT(*) as total_visitas')
            ->whereNotNull('fecha_visita')
            ->groupBy('anho', 'mes')
            ->orderByDesc('anho')
            ->orderBy('mes')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $visitasPorMes
        ]);
    }

    public function reservasPorMesYTipo(): JsonResponse
    {
        // Paso 1: Obtener todos los tipos de usuarios
        $estudiantes = DB::table('estudiante')->pluck('id_estudiante')->toArray();
        $docentes = DB::table('docente')->pluck('id_docente')->toArray();

        // Paso 2: Obtener las reservas agrupadas
        $reservas = DB::table('reservas')
            ->selectRaw('YEAR(fecha) as year, MONTH(fecha) as month, idUsuario, COUNT(*) as cantidad')
            ->groupBy(DB::raw('YEAR(fecha)'), DB::raw('MONTH(fecha)'), 'idUsuario')
            ->get();

        // Paso 3: Procesar resultados
        $resultados = [];

        foreach ($reservas as $reserva) {
            $mes = $reserva->year . '-' . str_pad($reserva->month, 2, '0', STR_PAD_LEFT);
            $tipo = null;

            if (in_array($reserva->idUsuario, $estudiantes)) {
                $tipo = 'estudiante';
            } elseif (in_array($reserva->idUsuario, $docentes)) {
                $tipo = 'docente';
            }

            if (!$tipo) continue;

            if (!isset($resultados[$mes])) {
                $resultados[$mes] = [
                    'mes' => $mes,
                    'estudiante' => 0,
                    'docente' => 0
                ];
            }

            $resultados[$mes][$tipo] += $reserva->cantidad;
        }

        $datos = array_values($resultados);

        return response()->json([
            'success' => true,
            'data' => $datos
        ]);
    }

    public function ultimasReservas(): JsonResponse
    {
        $reservas = DB::table('reservas as r')
            ->leftJoin('libro as l', 'r.idLibro', '=', 'l.id_libro')
            ->leftJoin('usuario as u', 'r.idUsuario', '=', 'u.id_usuario')
            ->orderBy('r.fecha', 'desc')
            ->limit(5)
            ->get([
                'r.id',
                'r.fecha',
                'r.estado',
                'l.id_libro as idLibro',
                'l.titulo',
                'l.autor',
                'u.id_usuario as idUsuario',
                'u.nombres'
            ]);

        return response()->json([
            'success' => true,
            'data' => $reservas
        ]);
    }


    public function listarLibros(): JsonResponse
    {
        $libros = DB::table('libro')
            ->leftJoin('libro_categoria', 'libro.id_libro', '=', 'libro_categoria.id_libro')
            ->leftJoin('libro_genero', 'libro.id_libro', '=', 'libro_genero.id_libro')
            ->orderBy('libro.titulo')
            ->get([
                'libro.id_libro as id',
                'libro.titulo',
                'libro.autor',
                'libro_categoria.id_tipocategoria as id_categoria',
                'libro_genero.id_tipogenero as id_genero',
            ]);

        return response()->json([
            'success' => true,
            'data' => $libros
        ]);
    }


    //ROL DOCENTE
    public function listarCursosPorDocente($idUsuario): JsonResponse
    {
        $cursos = CursoDocentes::with([
            'curso:id_curso,nombre',
            'cursoHorario:idCursoDocente,aula,dia,hora_ini,hora_fin'
        ])
            ->where('idUsuario', $idUsuario)
            ->get()
            ->map(function ($cursoDocente) {
                return [
                    'idCurso' => $cursoDocente->curso->id_curso,
                    'nombreCurso' => $cursoDocente->curso->nombre,
                    'horarios' => $cursoDocente->cursoHorario->map(function ($horario) {
                        return [
                            'aula' => $horario->aula,
                            'dia' => $horario->dia,
                            'hora_ini' => $horario->hora_ini,
                            'hora_fin' => $horario->hora_fin,
                        ];
                    })
                ];
            });

        return response()->json([
            'cursos' => $cursos
        ]);
    }

    public function cantidadAlumnosCursoDocente($idUsuario): JsonResponse
    {
        $cursos = CursoDocentes::with([
            'curso:id_curso,nombre',
            'cursoHorario:idCursoDocente,aula,dia,hora_ini,hora_fin,vacantes',
            'curso.cursoEstudiantes'
        ])
            ->where('idUsuario', $idUsuario)
            ->get()
            ->map(function ($cursoDocente) {
                return [
                    'idCurso' => $cursoDocente->curso->id_curso,
                    'nombreCurso' => $cursoDocente->curso->nombre,
                    'cantidadEstudiantes' => $cursoDocente->curso->cursoEstudiantes->count(),
                    'horarios' => $cursoDocente->cursoHorario->map(function ($horario) {
                        return [
                            'aula' => $horario->aula,
                            'dia' => $horario->dia,
                            'hora_ini' => $horario->hora_ini,
                            'hora_fin' => $horario->hora_fin,
                            'vacantes' => $horario->vacantes
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $cursos
        ]);
    }

    public function listarTareasPorDocente($idUsuario): JsonResponse
    {
        $cursosDocente = CursoDocentes::where('idUsuario', $idUsuario)
            ->pluck('idCurso')
            ->toArray();

        if (empty($cursosDocente)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $tareas = TareasCurso::whereIn('idCurso', $cursosDocente)
            ->get()
            ->map(function ($tarea) {
                return [
                    'idTareaCurso' => $tarea->idTareaCurso,
                    'idCurso' => $tarea->idCurso,
                    'descripcion' => $tarea->descripcion,
                    'fecha_inicio' => $tarea->fecha_inicio,
                    'fecha_fin' => $tarea->fecha_fin,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tareas
        ]);
    }

    /* ROL ESTUDIANTE */
    public function listarCursosPorEstudiante($idUsuario): JsonResponse
    {
        $cursos = CursoEstudiantes::with([
            'curso:id_curso,nombre,codigo',
            'curso.cursoDocentes.usuario:id_usuario,nombres,apellidoPaterno,apellidoMaterno',
            'curso.cursoDocentes.cursoHorario:idCursoDocente,aula,dia,hora_ini,hora_fin'
        ])
            ->where('idUsuario', $idUsuario)
            ->get()
            ->map(function ($item) {
                // Formatear los docentes con sus horarios
                $docentes = $item->curso->cursoDocentes->map(function ($docente) {
                    $horarios = $docente->cursoHorario->map(function ($horario) {
                        return [
                            'aula' => $horario->aula,
                            'dia' => $horario->dia,
                            'horaInicio' => $horario->hora_ini,
                            'horaFin' => $horario->hora_fin
                        ];
                    });

                    return [
                        'nombres' => $docente->usuario->nombres,
                        'apellidoPaterno' => $docente->usuario->apellidoPaterno,
                        'apellidoMaterno' => $docente->usuario->apellidoMaterno,
                        'horarios' => $horarios
                    ];
                });

                // Obtener todos los horarios para el primer aula
                $todosHorarios = $docentes->flatMap(function ($docente) {
                    return $docente['horarios'];
                });

                $primerHorario = $todosHorarios->first();

                return [
                    'idCursoEstudiante' => $item->idCursoEstudiante,
                    'cantidadRepitencias' => $item->cantidadRepitencias,
                    'curso' => [
                        'idCurso' => $item->curso->id_curso,
                        'nombreCurso' => $item->curso->nombre,
                        'codigoCurso' => $item->curso->codigo,
                        'aula' => $primerHorario['aula'] ?? null,
                        'docentes' => $docentes,
                        'horarios' => $todosHorarios
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $cursos
        ]);
    }

    public function listarNotasPorEstudiante($idUsuario): JsonResponse
    {
        // Obtenemos las notas del estudiante con sus relaciones
        $notas = EvaluacionesNotas::with([
            'cursoEvaluacion:idCursoEvaluacion,idCurso,idEvaluacion,porcentaje,fechaEvaluacion',
            'cursoEvaluacion.evaluacion:idEvaluacion,nombre',
            'cursoEvaluacion.curso:id_curso,nombre,codigo'
        ])
            ->where('idUsuario', $idUsuario)
            ->get()
            ->map(function ($item) {
                // Verificamos que todas las relaciones necesarias estén cargadas
                if (!$item->cursoEvaluacion || !$item->cursoEvaluacion->evaluacion || !$item->cursoEvaluacion->curso) {
                    return null;
                }

                return [
                    'curso' => [
                        'idCurso' => $item->cursoEvaluacion->curso->id_curso,
                        'nombre' => $item->cursoEvaluacion->curso->nombre,
                        'codigo' => $item->cursoEvaluacion->curso->codigo
                    ],
                    'evaluacion' => [
                        'nombre' => $item->cursoEvaluacion->evaluacion->nombre,
                        'porcentaje' => $item->cursoEvaluacion->porcentaje,
                        'fecha' => $item->cursoEvaluacion->fechaEvaluacion
                    ],
                    'nota' => $item->nota
                ];
            })
            ->filter()
            ->values(); // Convertimos a array indexado

        return response()->json([
            'success' => true,
            'data' => $notas
        ]);
    }

    public function listarTareasPorEstudiante($idUsuario): JsonResponse
    {
        // Primero obtenemos los cursos del estudiante
        $cursosEstudiante = CursoEstudiantes::where('idUsuario', $idUsuario)
            ->pluck('idCurso')
            ->toArray();

        // Luego obtenemos las tareas de esos cursos
        $tareas = TareasCurso::with([
            'curso:id_curso,nombre,codigo',
            'tareasAlumnos' => function ($query) use ($idUsuario) {
                $query->where('idUsuario', $idUsuario)
                    ->select('idTarea', 'idTareaCurso', 'idUsuario', 'archivo_nombre', 'fecha_subida');
            }
        ])
            ->whereIn('idCurso', $cursosEstudiante)
            ->orderBy('fecha_fin', 'asc')
            ->get()
            ->map(function ($tarea) {
                $tareaAlumno = $tarea->tareasAlumnos->first();

                return [
                    'idTarea' => $tarea->idTareaCurso,
                    'curso' => [
                        'idCurso' => $tarea->curso->id_curso,
                        'nombre' => $tarea->curso->nombre,
                        'codigo' => $tarea->curso->codigo
                    ],
                    'descripcion' => $tarea->descripcion,
                    'fecha_inicio' => $tarea->fecha_inicio,
                    'fecha_fin' => $tarea->fecha_fin,
                    'mi_entrega' => $tareaAlumno ? [
                        'fechaSubida' => $tareaAlumno->fecha_subida,
                        'estado' => $tareaAlumno->fecha_subida ? 'Entregado' : 'Pendiente'
                    ] : [
                        'fechaSubida' => null,
                        'estado' => 'Pendiente'
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tareas
        ]);
    }

    public function listarAsistenciaDocentesPorEstudiante($idUsuario): JsonResponse
    {
        // Obtener cursos del estudiante
        $cursos = DB::table('curso_estudiantes')
            ->where('idUsuario', $idUsuario)
            ->pluck('idCurso');

        // Obtener docentes asignados a esos cursos
        $docentesPorCurso = DB::table('curso_docentes')
            ->whereIn('idCurso', $cursos)
            ->get();

        // Obtener los periodo_curso para esos cursos y docentes
        $periodosCurso = DB::table('periodo_curso')
            ->whereIn('id_curso', $cursos)
            ->whereIn('id_docente', $docentesPorCurso->pluck('idUsuario'))
            ->pluck('id_periodocurso');

        // Obtener asistencias de tipo docente (D), estado P o T
        $asistencias = DB::table('asistencia')
            ->whereIn('id_periodocurso', $periodosCurso)
            ->where('tipo', 'D')
            ->whereIn('estado', ['P', 'T'])
            ->select('id_persona', DB::raw('COUNT(*) as total_asistencias'))
            ->groupBy('id_persona')
            ->get();

        // Obtener nombres de docentes
        $docentes = DB::table('persona')
            ->whereIn('id_persona', $asistencias->pluck('id_persona'))
            ->get()
            ->keyBy('id_persona');

        // Armar respuesta
        $resultado = $asistencias->map(function ($asistencia) use ($docentes) {
            $docente = $docentes[$asistencia->id_persona] ?? null;
            return [
                'docente_id' => $asistencia->id_persona,
                'nombre_docente' => $docente ? $docente->nombre_completo : 'Desconocido',
                'total_asistencias' => $asistencia->total_asistencias,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $resultado
        ]);
    }

    //ROL TOPICO MEDICO
    public function getCantidadPaciente(): JsonResponse
    {
        $cantidad = Paciente::count();
        return response()->json(['cantidad' => $cantidad]);
    }

    public function getCantidadDoctor(): JsonResponse
    {
        $cantidad = Doctor::count();
        return response()->json(['cantidad' => $cantidad]);
    }
};
