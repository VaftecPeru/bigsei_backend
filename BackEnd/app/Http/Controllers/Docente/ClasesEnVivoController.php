<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClasesEnVivoController extends Controller
{
    /**
     * Listar clases/sesiones en vivo de un periodo-curso
     */
    public function index(Request $request)
    {
        $user = $request->sessionUser;
        $id_periodocurso = $request->id_periodocurso;

        try {
            $query = DB::table('sesion_sincrona as ss')
                ->where('ss.id_empresa', $user->id_empresa)
                ->select(
                    'ss.id_sesionsincrona',
                    'ss.titulo',
                    'ss.descripcion',
                    'ss.url_reunion',
                    'ss.fecha_inicio',
                    'ss.fecha_fin',
                    'ss.estado',
                    'ss.id_periodocurso'
                )
                ->orderBy('ss.fecha_inicio', 'desc');

            if ($id_periodocurso) {
                $query->where('ss.id_periodocurso', $id_periodocurso);
            }

            return response()->json($query->get());
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * Crear nueva clase en vivo / sesión sincrónica
     */
    public function store(Request $request)
    {
        $user = $request->sessionUser;

        $validator = Validator::make($request->all(), [
            'titulo'         => 'required|string|max:255',
            'url_reunion'    => 'required|string|max:500',
            'fecha_inicio'   => 'required|date',
            'id_periodocurso' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        try {
            $id = DB::table('sesion_sincrona')->insertGetId([
                'id_empresa'      => $user->id_empresa,
                'id_periodocurso' => $request->id_periodocurso,
                'titulo'          => $request->titulo,
                'descripcion'     => $request->descripcion ?? null,
                'url_reunion'     => $request->url_reunion,
                'fecha_inicio'    => $request->fecha_inicio,
                'fecha_fin'       => $request->fecha_fin ?? null,
                'estado'          => '1',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return response()->json(['id_sesionsincrona' => $id, 'message' => 'Clase en vivo creada']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear clase: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar clase en vivo
     */
    public function update(Request $request, $id)
    {
        $user = $request->sessionUser;

        try {
            $campos = [];
            if ($request->has('titulo'))       $campos['titulo']       = $request->titulo;
            if ($request->has('descripcion'))  $campos['descripcion']  = $request->descripcion;
            if ($request->has('url_reunion'))  $campos['url_reunion']  = $request->url_reunion;
            if ($request->has('fecha_inicio')) $campos['fecha_inicio'] = $request->fecha_inicio;
            if ($request->has('fecha_fin'))    $campos['fecha_fin']    = $request->fecha_fin;
            if ($request->has('estado'))       $campos['estado']       = $request->estado;

            $campos['updated_at'] = now();

            DB::table('sesion_sincrona')
                ->where('id_sesionsincrona', $id)
                ->where('id_empresa', $user->id_empresa)
                ->update($campos);

            return response()->json(['message' => 'Clase actualizada']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar clase en vivo
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->sessionUser;

        try {
            DB::table('sesion_sincrona')
                ->where('id_sesionsincrona', $id)
                ->where('id_empresa', $user->id_empresa)
                ->delete();

            return response()->json(['message' => 'Clase eliminada']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
