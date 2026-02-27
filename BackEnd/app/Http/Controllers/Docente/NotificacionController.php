<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificacionController extends Controller
{
    /**
     * Listar notificaciones del docente o sus estudiantes
     */
    public function index(Request $request)
    {
        $user = $request->sessionUser;

        try {
            $notificaciones = DB::table('notificacion as n')
                ->where('n.id_usuario_destino', $user->id_usuario)
                ->orWhere('n.id_empresa', $user->id_empresa)
                ->select(
                    'n.id_notificacion',
                    'n.titulo',
                    'n.mensaje',
                    'n.tipo',
                    'n.leida',
                    'n.created_at'
                )
                ->orderBy('n.created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json($notificaciones);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * Enviar notificación a los estudiantes de un curso
     */
    public function store(Request $request)
    {
        $user = $request->sessionUser;

        $validator = Validator::make($request->all(), [
            'titulo'           => 'required|string|max:255',
            'mensaje'          => 'required|string',
            'id_periodocurso'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        try {
            // Insertar notificación global de empresa
            $id = DB::table('notificacion')->insertGetId([
                'id_empresa'        => $user->id_empresa,
                'id_usuario_origen' => $user->id_usuario,
                'id_usuario_destino'=> null, // null = broadcast a empresa
                'titulo'            => $request->titulo,
                'mensaje'           => $request->mensaje,
                'tipo'              => $request->tipo ?? 'aviso',
                'id_periodocurso'   => $request->id_periodocurso ?? null,
                'leida'             => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            return response()->json(['id_notificacion' => $id, 'message' => 'Notificación enviada']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida(Request $request, $id)
    {
        $user = $request->sessionUser;

        try {
            DB::table('notificacion')
                ->where('id_notificacion', $id)
                ->where('id_usuario_destino', $user->id_usuario)
                ->update(['leida' => 1, 'updated_at' => now()]);

            return response()->json(['message' => 'Notificación marcada como leída']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Contar notificaciones no leídas
     */
    public function noLeidas(Request $request)
    {
        $user = $request->sessionUser;

        try {
            $count = DB::table('notificacion')
                ->where('id_usuario_destino', $user->id_usuario)
                ->where('leida', 0)
                ->count();

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['count' => 0]);
        }
    }
}
