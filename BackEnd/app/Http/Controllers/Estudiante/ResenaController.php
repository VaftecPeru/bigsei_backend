<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\ResenaCurso;
use App\Models\PeriodoCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResenaController extends Controller
{
    /**
     * Obtiene el idUsuario del estudiante autenticado mediante JWT.
     */
    private function getIdUsuario(): int
    {
        $payload = JWTAuth::parseToken()->getPayload();
        return $payload->get('idUsuario');
    }

    /**
     * GET /estudiante/mis-reseñas
     * Devuelve todas las reseñas del estudiante.
     */
    public function index(Request $request)
    {
        $idUsuario = $this->getIdUsuario();

        $reseñas = ResenaCurso::where('idUsuario', $idUsuario)
            ->with([
                'periodoCurso.curso:idCurso,nombreCurso,codigoCurso',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'idResena'      => $r->idResena,
                    'idPeriodoCurso'=> $r->idPeriodoCurso,
                    'nombreCurso'   => $r->periodoCurso?->curso?->nombreCurso ?? 'Curso desconocido',
                    'codigoCurso'   => $r->periodoCurso?->curso?->codigoCurso ?? '',
                    'calificacion'  => $r->calificacion,
                    'comentario'    => $r->comentario,
                    'fecha'         => $r->created_at?->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $reseñas,
        ]);
    }

    /**
     * POST /estudiante/mis-reseñas
     * Crea o actualiza la reseña del estudiante para un curso dado.
     * Body: { idPeriodoCurso, calificacion (1-5), comentario (opcional) }
     */
    public function store(Request $request)
    {
        $idUsuario = $this->getIdUsuario();

        $validator = Validator::make($request->all(), [
            'idPeriodoCurso' => 'required|integer|exists:periodo_cursos,idPeriodoCurso',
            'calificacion'   => 'required|integer|min:1|max:5',
            'comentario'     => 'nullable|string|max:1000',
        ], [
            'idPeriodoCurso.required'  => 'El curso es requerido',
            'idPeriodoCurso.exists'    => 'El curso no existe',
            'calificacion.required'    => 'La calificación es requerida',
            'calificacion.min'         => 'La calificación mínima es 1',
            'calificacion.max'         => 'La calificación máxima es 5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $reseña = ResenaCurso::updateOrCreate(
            [
                'idUsuario'      => $idUsuario,
                'idPeriodoCurso' => $request->idPeriodoCurso,
            ],
            [
                'calificacion' => $request->calificacion,
                'comentario'   => $request->comentario,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Reseña guardada exitosamente',
            'data'    => $reseña,
        ], 201);
    }
}
