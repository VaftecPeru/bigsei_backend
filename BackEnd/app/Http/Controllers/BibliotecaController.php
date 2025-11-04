<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Devolucion;
use App\Models\VisitasBiblioteca;
use Carbon\Carbon;
use App\Models\Libro;
use Barryvdh\DomPDF\Facade\Pdf;


class BibliotecaController extends Controller
{
    public function reporteEstadisticasReservas(Request $request)
    {

        $request->validate([
            'id_anho' => 'required|integer',
        ]);

        $idAnho = $request->input('id_anho');

        // Cantidad de reservas por estudiantes
        $cantReservasEstudiante = Reserva::where('tipo_usuario', 'estudiante')
            ->whereYear('fecha', $idAnho)
            ->count();

        // Porcentaje de reservas estudiantiles esta semana
        $reservasEstSemana = Reserva::where('tipo_usuario', 'estudiante')
            ->whereYear('fecha', $idAnho)
            ->whereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $porcReservasEstSemana = $cantReservasEstudiante > 0
            ? round(($reservasEstSemana / $cantReservasEstudiante) * 100, 2) : 0;

        // Cantidad de reservas por docentes
        $cantReservasDocente = Reserva::where('tipo_usuario', 'docente')
            ->whereYear('fecha', $idAnho)
            ->count();

        // Porcentaje de reservas docentes esta semana
        $reservasDocSemana = Reserva::where('tipo_usuario', 'docente')
            ->whereYear('fecha', $idAnho)
            ->whereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $porcReservasDocSemana = $cantReservasDocente > 0
            ? round(($reservasDocSemana / $cantReservasDocente) * 100, 2) : 0;

        // Cantidad de devoluciones atrasadas
        $cantDevolucionesAtrasadas = Devolucion::where('estado', 'atrasada')
            ->whereYear('fecha_devolucion', $idAnho)
            ->count();

        // Porcentaje de devoluciones atrasadas esta semana
        $devolucionesAtrSemana = Devolucion::where('estado', 'atrasada')
            ->whereYear('fecha_devolucion', $idAnho)
            ->whereBetween('fecha_devolucion', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $porcDevolucionesAtrSemana = $cantDevolucionesAtrasadas > 0
            ? round(($devolucionesAtrSemana / $cantDevolucionesAtrasadas) * 100, 2) : 0;

        // Respuesta JSON con las estadísticas
        return response()->json([
            'data' => [
                'cant_reservas_estudiante' => $cantReservasEstudiante,
                'porc_reservas_est_semana' => $porcReservasEstSemana,
                'cant_reservas_docente' => $cantReservasDocente,
                'porc_reservas_doc_semana' => $porcReservasDocSemana,
                'cant_devoluciones_atrasadas' => $cantDevolucionesAtrasadas,
                'porc_devoluciones_atr_semana' => $porcDevolucionesAtrSemana,
            ]
        ], 200);
    }

    public function obtenerFrecuenciaYRegistrarVisita(Request $request)
    {
        // Obtener la fecha actual
        $fechaActual = Carbon::now();
        $idAnho = $fechaActual->year; // Año actual
        $idMes = $fechaActual->month; // Mes actual (1-12)
        $mesNombre = $fechaActual->format('F'); // Nombre del mes (Ej. January)

        // Buscar si ya existe un registro para este mes y año
        $registro = VisitasBiblioteca::where('id_anho', $idAnho)
            ->where('id_mes', $idMes)
            ->first();

        if ($registro) {
            // Incrementar el contador de visitas si ya existe
            $registro->increment('cant_visitas');
        } else {
            // Crear un nuevo registro si no existe
            VisitasBiblioteca::create([
                'id_anho' => $idAnho,
                'id_mes' => $idMes,
                'mes_nombre' => $mesNombre,
                'cant_visitas' => 1, // Inicia con 1 visita
            ]);
        }

        // Obtener el reporte de visitas agrupado por mes para el año actual
        $frecuenciaVisitas = VisitasBiblioteca::where('id_anho', $idAnho)
            ->select('cant_visitas', 'id_mes', 'mes_nombre')
            ->orderBy('id_mes')
            ->get();

        // Retornar la respuesta en formato JSON
        return response()->json([
            'data' => $frecuenciaVisitas,
        ], 200);
    }

    public function obtenerLibros(Request $request)
    {
        // Obtener los parámetros de filtro
        $idCategoria = $request->input('id_categoria');
        $idGenero = $request->input('id_genero');
        $estado = $request->input('estado');

        // Crear la consulta base
        $query = Libro::with(['categoria', 'genero']);

        // Filtro por categoría si se envió
        if ($idCategoria) {
            $query->where('id_categoria', $idCategoria);
        }

        // Filtro por género si se envió
        if ($idGenero) {
            $query->where('id_genero', $idGenero);
        }

        // Filtro por estado si se envió
        if (!is_null($estado)) {
            $query->where('estado', $estado);
        }

        // Obtener los datos
        $libros = $query->get()->map(function ($libro) {
            return [
                'cantidad_total' => $libro->cantidad_total,
                'cantidad_disponible' => $libro->cantidad_disponible,
                'nombre' => $libro->nombre,
                'autor' => $libro->autor,
                'categoria' => $libro->categoria ? $libro->categoria->nombre : null,
                'genero' => $libro->genero ? $libro->genero->nombre : null,
                'estado' => $libro->estado ? 'Activo' : 'Inactivo',
            ];
        });

        // Retornar la respuesta en formato JSON
        return response()->json([
            'data' => $libros,
        ], 200);
    }

    // Método para registrar un nuevo libro
    public function crearLibro(Request $request)
    {
        // Validar los datos enviados
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255', // Corresponde al campo "nombre" en el modelo
            'autor' => 'required|string|max:255',
            'id_categoria' => 'required|exists:categorias,id',
            'id_genero' => 'required|exists:generos,id',
            'cantidad_total' => 'required|integer|min:0',
            'cantidad_disponible' => 'required|integer|min:0',
            'estado' => 'required|boolean',
        ]);

        // Crear el libro en la base de datos
        $libro = Libro::create($validatedData);

        // Responder con los datos creados
        return response()->json([
            'data' => [
                'id_libro' => $libro->id,
                'nombre' => $libro->nombre,
                'autor' => $libro->autor,
                'id_categoria' => $libro->id_categoria,
                'id_genero' => $libro->id_genero,
                'cantidad_total' => $libro->cantidad_total,
                'cantidad_disponible' => $libro->cantidad_disponible,
                'estado' => $libro->estado,
            ]
        ], 201);
    }

    public function actualizarLibro(Request $request, $idLibro)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'titulo' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'editorial' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'id_categoria' => 'required|integer|exists:categorias,id',
            'id_genero' => 'required|integer|exists:generos,id',
        ]);

        $libro = Libro::find($idLibro);

        // Verificar si el libro existe
        if (!$libro) {
            return response()->json([
                'message' => 'Libro no encontrado',
            ], 404);
        }

        // Actualizar los datos del libro
        $libro->update([
            'nombre' => $request->input('titulo'),
            'autor' => $request->input('autor'),
            'editorial' => $request->input('editorial'),
            'descripcion' => $request->input('descripcion'),
            'id_categoria' => $request->input('id_categoria'),
            'id_genero' => $request->input('id_genero'),
        ]);

        // Retornar la respuesta con el libro actualizado
        return response()->json([
            'data' => [
                'id_libro' => $libro->id,
                'titulo' => $libro->nombre,
                'autor' => $libro->autor,
                'editorial' => $libro->editorial,
                'descripcion' => $libro->descripcion,
                'id_categoria' => $libro->id_categoria,
                'id_genero' => $libro->id_genero,
            ],
        ], 200);
    }

    public function obtenerLibroPorId($idLibro)
    {
        // Buscar el libro por su ID junto con sus relaciones (categoría y género)
        $libro = Libro::with(['categoria', 'genero'])->find($idLibro);

        // Verificar si el libro existe
        if (!$libro) {
            return response()->json([
                'message' => 'Libro no encontrado',
            ], 404);
        }

        // Retornar los detalles del libro en formato JSON
        return response()->json([
            'data' => [
                'id_libro' => $libro->id,
                'titulo' => $libro->nombre,
                'autor' => $libro->autor,
                'editorial' => $libro->editorial,
                'descripcion' => $libro->descripcion,
                'id_categoria' => $libro->id_categoria,
                'id_genero' => $libro->id_genero,
            ],
        ], 200);
    }

    public function eliminarLibro($idLibro)
    {
        // Buscar el libro por su ID
        $libro = Libro::find($idLibro);

        // Verificar si el libro existe
        if (!$libro) {
            return response()->json([
                'message' => 'Libro no encontrado',
            ], 404);
        }

        // Eliminar el libro
        $libro->delete();

        // Retornar una respuesta exitosa
        return response()->json([
            'message' => 'Libro eliminado exitosamente',
            'data' => []
        ], 200);
    }

    // Obtener reservas con filtro de texto
    public function index(Request $request)
    {
        $texto_buscar = $request->input('texto_buscar', '');

        $reservas = Reserva::with(['libro', 'usuario'])
            ->whereHas('libro', function ($query) use ($texto_buscar) {
                $query->where('titulo', 'like', "%$texto_buscar%");
            })
            ->get()
            ->map(function ($reserva) {
                return [
                    'id_libro' => $reserva->libro->id,
                    'titulo' => $reserva->libro->titulo,
                    'codigo' => $reserva->libro->id, // Asignamos el ID del libro como código
                    'estudiante_dni' => $reserva->usuario->dni,
                    'estudiante_nombre_completo' => $reserva->usuario->nombres . ' ' . $reserva->usuario->apellidoPaterno . ' ' . $reserva->usuario->apellidoMaterno,
                    'estudiante_foto_url' => $reserva->usuario->foto,
                ];
            });

        return response()->json(['data' => $reservas]);
    }

    public function descargarPdfReservas(Request $request)
    {
        $textoBuscar = $request->input('texto_buscar');

        // Filtramos las reservas con el texto proporcionado
        $reservas = Reserva::with(['libro', 'usuario'])
            ->whereHas('libro', function ($query) use ($textoBuscar) {
                if ($textoBuscar) {
                    $query->where('titulo', 'like', "%$textoBuscar%");
                }
            })
            ->get();

        // Preparamos los datos para el PDF
        $data = $reservas->map(function ($reserva) {
            return [
                'id_libro' => $reserva->libro->id,
                'titulo' => $reserva->libro->titulo,
                'codigo' => $reserva->libro->codigo ?? 'N/A',
                'estudiante_dni' => $reserva->usuario->dni,
                'estudiante_nombre_completo' => $reserva->usuario->nombres . ' ' . $reserva->usuario->apellidoPaterno . ' ' . $reserva->usuario->apellidoMaterno,
                'estudiante_foto_url' => $reserva->usuario->foto,
            ];
        });

        // Generamos el PDF usando una vista
        $pdf = Pdf::loadView('exports.reporte-reservas.pdf', ['data' => $data]);

        return $pdf->download('reporte-reservas.pdf');
    }

    public function exportarVisitasPdf(Request $request)
    {
        // Consulta base
        $query = VisitasBiblioteca::query();

        // Aplicar filtros
        if ($request->has('id_anho')) {
            $query->where('id_anho', $request->id_anho);
        }

        if ($request->has('id_mes')) {
            $query->where('id_mes', $request->id_mes);
        }

        // Ordenar por año y mes
        $visitas = $query->orderBy('id_anho', 'desc')
            ->orderBy('id_mes', 'asc')
            ->get();

        // Generar PDF
        $pdf = Pdf::loadView('exports.reporte-visitas_biblioteca', [
            'visitas' => $visitas,
            'filtros' => $request->all(),
            'titulo' => 'Reporte de Visitas a la Biblioteca'
        ]);

        return $pdf->download('visitas_biblioteca_' . now()->format('Ymd_His') . '.pdf');
    }
}
