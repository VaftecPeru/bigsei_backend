<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BancoPreguntasController extends Controller
{
    /**
     * Listar preguntas del banco del docente (independientes de cuestionarios)
     */
    public function index(Request $request)
    {
        $user = $request->sessionUser;

        try {
            $query = DB::table('banco_pregunta as bp')
                ->where('bp.id_empresa', $user->id_empresa)
                ->select(
                    'bp.id_bancopregunta',
                    'bp.enunciado',
                    'bp.tipo',
                    'bp.dificultad',
                    'bp.categoria',
                    'bp.estado',
                    'bp.created_at',
                    DB::raw("(SELECT COUNT(*) FROM banco_respuesta br WHERE br.id_bancopregunta = bp.id_bancopregunta) as total_opciones")
                )
                ->orderBy('bp.created_at', 'desc');

            if ($request->has('tipo')) {
                $query->where('bp.tipo', $request->tipo);
            }
            if ($request->has('categoria')) {
                $query->where('bp.categoria', $request->categoria);
            }
            if ($request->has('text_search')) {
                $query->where('bp.enunciado', 'like', '%' . $request->text_search . '%');
            }

            return response()->json($query->get());
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * Crear pregunta en el banco
     */
    public function store(Request $request)
    {
        $user = $request->sessionUser;

        $validator = Validator::make($request->all(), [
            'enunciado'  => 'required|string',
            'tipo'       => 'required|in:opcion_multiple,verdadero_falso,texto_libre',
            'dificultad' => 'nullable|in:facil,media,dificil',
            'categoria'  => 'nullable|string|max:100',
            'opciones'   => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        try {
            $id = DB::table('banco_pregunta')->insertGetId([
                'id_empresa'  => $user->id_empresa,
                'id_docente'  => $user->id_usuario,
                'enunciado'   => $request->enunciado,
                'tipo'        => $request->tipo,
                'dificultad'  => $request->dificultad ?? 'media',
                'categoria'   => $request->categoria ?? 'General',
                'estado'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Guardar opciones si vienen
            if ($request->has('opciones') && is_array($request->opciones)) {
                foreach ($request->opciones as $opcion) {
                    DB::table('banco_respuesta')->insert([
                        'id_bancopregunta' => $id,
                        'texto'            => $opcion['texto'] ?? '',
                        'es_correcta'      => $opcion['es_correcta'] ?? 0,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            return response()->json(['id_bancopregunta' => $id, 'message' => 'Pregunta creada en el banco']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ver detalle de una pregunta con sus opciones
     */
    public function show(Request $request, $id)
    {
        $user = $request->sessionUser;

        try {
            $pregunta = DB::table('banco_pregunta')
                ->where('id_bancopregunta', $id)
                ->where('id_empresa', $user->id_empresa)
                ->first();

            if (!$pregunta) {
                return response()->json(['error' => 'Pregunta no encontrada'], 404);
            }

            $opciones = DB::table('banco_respuesta')
                ->where('id_bancopregunta', $id)
                ->get();

            return response()->json([
                'pregunta' => $pregunta,
                'opciones' => $opciones,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar pregunta del banco
     */
    public function update(Request $request, $id)
    {
        $user = $request->sessionUser;

        try {
            $campos = [];
            if ($request->has('enunciado'))  $campos['enunciado']  = $request->enunciado;
            if ($request->has('tipo'))       $campos['tipo']       = $request->tipo;
            if ($request->has('dificultad')) $campos['dificultad'] = $request->dificultad;
            if ($request->has('categoria'))  $campos['categoria']  = $request->categoria;
            if ($request->has('estado'))     $campos['estado']     = $request->estado;
            $campos['updated_at'] = now();

            DB::table('banco_pregunta')
                ->where('id_bancopregunta', $id)
                ->where('id_empresa', $user->id_empresa)
                ->update($campos);

            return response()->json(['message' => 'Pregunta actualizada']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar pregunta del banco
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->sessionUser;

        try {
            DB::table('banco_respuesta')->where('id_bancopregunta', $id)->delete();
            DB::table('banco_pregunta')
                ->where('id_bancopregunta', $id)
                ->where('id_empresa', $user->id_empresa)
                ->delete();

            return response()->json(['message' => 'Pregunta eliminada del banco']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
