<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForoEstudianteController extends Controller
{
    /**
     * GET /estudiante/foro-temas
     * Listar temas de foro disponibles en los cursos del estudiante.
     */
    public function indexTema(Request $request)
    {
        $user = $request->sessionUser;
        $id_periodocurso = $request->id_periodocurso;

        try {
            $query = DB::table('foro_tema as ft')
                ->join('persona as p', 'ft.id_usuario', '=', 'p.id_persona')
                ->select(
                    'ft.id_forotema',
                    'ft.titulo',
                    'ft.descripcion',
                    'ft.id_periodocurso',
                    DB::raw("COALESCE(p.nombre_completo, p.nombre) as autor"),
                    'ft.created_at',
                    DB::raw("(SELECT COUNT(*) FROM foro_respuesta fr WHERE fr.id_forotema = ft.id_forotema) as total_respuestas")
                )
                ->orderBy('ft.created_at', 'desc');

            if ($id_periodocurso) {
                $query->where('ft.id_periodocurso', $id_periodocurso);
            }

            return response()->json($query->get());
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * GET /estudiante/foro-respuestas
     * Listar respuestas de un tema.
     */
    public function indexRespuesta(Request $request)
    {
        $id_forotema = $request->id_forotema;

        if (!$id_forotema) {
            return response()->json(['error' => 'id_forotema es requerido'], 400);
        }

        try {
            $respuestas = DB::table('foro_respuesta as fr')
                ->join('persona as p', 'fr.id_usuario', '=', 'p.id_persona')
                ->where('fr.id_forotema', $id_forotema)
                ->select(
                    'fr.id_fororespuesta',
                    'fr.texto',
                    'fr.id_usuario',
                    DB::raw("COALESCE(p.nombre_completo, p.nombre) as autor"),
                    'fr.created_at'
                )
                ->orderBy('fr.created_at', 'asc')
                ->get();

            return response()->json($respuestas);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * POST /estudiante/foro-respuestas
     * Participar en un foro respondiendo a un tema.
     */
    public function storeRespuesta(Request $request)
    {
        $user = $request->sessionUser;

        $validator = Validator::make($request->all(), [
            'id_forotema' => 'required',
            'texto'       => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        try {
            $id = DB::table('foro_respuesta')->insertGetId([
                'id_forotema' => $request->id_forotema,
                'id_usuario'  => $user->id_usuario,
                'texto'       => $request->texto,
                'estado'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json(['id_fororespuesta' => $id, 'message' => 'Respuesta publicada']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al publicar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /estudiante/mis-tramites
     * Trámites del estudiante autenticado.
     */
    public function misTramites(Request $request)
    {
        $user = $request->sessionUser;

        try {
            $tramites = DB::table('tramite as t')
                ->where('t.id_usuario', $user->id_usuario)
                ->select(
                    't.id as idTramite',
                    't.tipo_tramite',
                    't.estado',
                    't.mensaje',
                    't.observacion',
                    DB::raw("DATE_FORMAT(t.created_at, '%Y-%m-%d %H:%i') as fecha_solicitud"),
                    DB::raw("CONCAT('TKT-', LPAD(t.id, 5, '0')) as ticket")
                )
                ->orderBy('t.created_at', 'desc')
                ->get();

            return response()->json($tramites);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
