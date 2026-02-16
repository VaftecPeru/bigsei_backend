<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Tramite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TramiteDirectorController extends Controller
{
    /**
     * Lista trámites (solo lectura para el director)
     */
    public function index(Request $request)
    {
        $query = DB::table('tramites')
            ->select(
                'id',
                'nombre',
                'matricula',
                'tipo_tramite',
                'fecha_solicitud',
                'estado'
            );

        // Filtro por estado
        if ($request->has('estado') && $request->estado !== '') {
            $query->where('estado', $request->estado);
        }

        // Filtro por tipo
        if ($request->has('tipo_tramite') && $request->tipo_tramite !== '') {
            $query->where('tipo_tramite', $request->tipo_tramite);
        }

        // Búsqueda por nombre o matrícula
        if ($request->has('text_search') && $request->text_search !== '') {
            $texto = str_replace(' ', '%', $request->text_search);
            $query->where(function ($q) use ($texto) {
                $q->whereRaw("upper(nombre) LIKE upper(?)", ['%' . $texto . '%'])
                    ->orWhereRaw("upper(matricula) LIKE upper(?)", ['%' . $texto . '%']);
            });
        }

        $result = $query->orderBy('fecha_solicitud', 'desc')->get();

        return response()->json($result);
    }

    /**
     * Estadísticas de trámites
     */
    public function estadisticas(Request $request)
    {
        // Total por estado
        $porEstado = DB::table('tramites')
            ->select('estado', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('estado')
            ->get();

        // Total por tipo
        $porTipo = DB::table('tramites')
            ->select('tipo_tramite', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('tipo_tramite')
            ->get();

        // Recientes (últimos 30 días)
        $recientes = DB::table('tramites')
            ->where('fecha_solicitud', '>=', now()->subDays(30))
            ->count();

        // Total general
        $total = DB::table('tramites')->count();

        return response()->json([
            'total' => $total,
            'recientes_30_dias' => $recientes,
            'por_estado' => $porEstado,
            'por_tipo' => $porTipo,
        ]);
    }
}
