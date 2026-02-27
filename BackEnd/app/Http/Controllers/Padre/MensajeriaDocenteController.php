<?php

namespace App\Http\Controllers\Padre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class MensajeriaDocenteController extends Controller
{
    private function getIdPadre(): int
    {
        $payload = JWTAuth::parseToken()->getPayload();
        return $payload->get('idUsuario');
    }

    /**
     * GET /padre/mensajes
     * Listar conversaciones/mensajes del padre con docentes de sus hijos.
     */
    public function index(Request $request)
    {
        $idPadre = $this->getIdPadre();

        try {
            $mensajes = DB::table('mensajeria_padre as mp')
                ->leftJoin('persona as p', 'mp.id_destinatario', '=', 'p.id_persona')
                ->where('mp.id_padre', $idPadre)
                ->select(
                    'mp.id_mensaje',
                    'mp.texto',
                    'mp.id_destinatario',
                    DB::raw("COALESCE(p.nombre_completo, p.nombre) as nombre_docente"),
                    'mp.leido',
                    'mp.created_at'
                )
                ->orderBy('mp.created_at', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $mensajes]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * GET /padre/mensajes/docentes
     * Listar los docentes de los hijos del padre (para poder enviarles mensajes).
     */
    public function docentesHijos(Request $request)
    {
        $idPadre = $this->getIdPadre();

        try {
            // Buscar hijos del padre
            $hijos = DB::table('padre_hijo')
                ->where('id_padre', $idPadre)
                ->pluck('id_hijo');

            if ($hijos->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            // Buscar docentes de los cursos de esos hijos
            $docentes = DB::table('periodo_curso as pc')
                ->join('matricula_curso as mc', 'pc.id_periodocurso', '=', 'mc.id_periodocurso')
                ->join('matricula as m', 'mc.id_matricula', '=', 'm.id_matricula')
                ->join('persona as p', 'pc.id_docente', '=', 'p.id_persona')
                ->join('curso as c', 'pc.id_curso', '=', 'c.id_curso')
                ->whereIn('m.id_usuario', $hijos)
                ->select(
                    'p.id_persona as id_docente',
                    DB::raw("COALESCE(p.nombre_completo, p.nombre) as nombre_docente"),
                    'p.correo',
                    'c.nombre as curso_nombre'
                )
                ->distinct()
                ->get();

            return response()->json(['success' => true, 'data' => $docentes]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * POST /padre/mensajes
     * Enviar mensaje a un docente.
     */
    public function store(Request $request)
    {
        $idPadre = $this->getIdPadre();

        $validator = Validator::make($request->all(), [
            'id_destinatario' => 'required|integer',
            'texto'           => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => implode(', ', $validator->messages()->all())], 400);
        }

        try {
            $id = DB::table('mensajeria_padre')->insertGetId([
                'id_padre'        => $idPadre,
                'id_destinatario' => $request->id_destinatario,
                'texto'           => $request->texto,
                'leido'           => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return response()->json([
                'success'    => true,
                'id_mensaje' => $id,
                'message'    => 'Mensaje enviado correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PUT /padre/mensajes/{id}/leer
     * Marcar mensaje como leído.
     */
    public function marcarLeido(Request $request, $id)
    {
        $idPadre = $this->getIdPadre();

        try {
            DB::table('mensajeria_padre')
                ->where('id_mensaje', $id)
                ->where('id_padre', $idPadre)
                ->update(['leido' => 1, 'updated_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Mensaje marcado como leído']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
