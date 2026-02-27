<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForoController extends Controller
{
    /**
     * Listar temas del foro de un periodo-curso
     */
    public function indexTema(Request $request)
    {
        $user = $request->sessionUser;
        $id_periodocurso = $request->id_periodocurso;

        if (!$id_periodocurso) {
            return response()->json(['error' => 'El id_periodocurso es requerido'], 400);
        }

        try {
            $temas = DB::table('foro_tema as ft')
                ->join('persona as p', 'ft.id_usuario', '=', 'p.id_persona')
                ->where('ft.id_periodocurso', $id_periodocurso)
                ->select(
                    'ft.id_forotema',
                    'ft.titulo',
                    'ft.descripcion',
                    'ft.id_usuario',
                    DB::raw("COALESCE(p.nombre_completo, p.nombre) as autor"),
                    'ft.created_at',
                    DB::raw("(SELECT COUNT(*) FROM foro_respuesta fr WHERE fr.id_forotema = ft.id_forotema) as total_respuestas")
                )
                ->orderBy('ft.created_at', 'desc')
                ->get();

            return response()->json($temas);
        } catch (\Exception $e) {
            // Si la tabla no existe aún, devolver vacío
            return response()->json([]);
        }
    }

    /**
     * Crear nuevo tema de foro
     */
    public function storeTema(Request $request)
    {
        $user = $request->sessionUser;

        $validator = Validator::make($request->all(), [
            'id_periodocurso' => 'required',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        try {
            $id = DB::table('foro_tema')->insertGetId([
                'id_periodocurso' => $request->id_periodocurso,
                'id_usuario' => $user->id_usuario,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['id_forotema' => $id, 'message' => 'Tema creado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear tema: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Listar respuestas de un tema
     */
    public function indexRespuesta(Request $request)
    {
        $id_forotema = $request->id_forotema;

        if (!$id_forotema) {
            return response()->json(['error' => 'El id_forotema es requerido'], 400);
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
     * Agregar respuesta a un tema
     */
    public function storeRespuesta(Request $request)
    {
        $user = $request->sessionUser;

        $validator = Validator::make($request->all(), [
            'id_forotema' => 'required',
            'texto' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        try {
            $id = DB::table('foro_respuesta')->insertGetId([
                'id_forotema' => $request->id_forotema,
                'id_usuario' => $user->id_usuario,
                'texto' => $request->texto,
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['id_fororespuesta' => $id, 'message' => 'Respuesta agregada']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al agregar respuesta: ' . $e->getMessage()], 500);
        }
    }
}
