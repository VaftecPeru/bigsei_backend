<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ListaDeseosController extends Controller
{
    /**
     * GET /estudiante/lista-deseos
     * Lista de cursos guardados por el estudiante.
     */
    public function index(Request $request)
    {
        $user = $request->sessionUser;

        try {
            $items = DB::table('lista_deseos as ld')
                ->leftJoin('curso as c', 'ld.id_curso', '=', 'c.id_curso')
                ->leftJoin('periodo_curso as pc', function ($join) use ($user) {
                    $join->on('pc.id_curso', '=', 'c.id_curso')
                         ->where('pc.id_empresa', '=', $user->id_empresa);
                })
                ->where('ld.id_usuario', $user->id_usuario)
                ->select(
                    'ld.id_listadeseos',
                    'ld.id_curso',
                    'c.nombre as curso_nombre',
                    'c.codigo as curso_codigo',
                    'c.descripcion as curso_descripcion',
                    'pc.id_periodocurso',
                    'ld.created_at'
                )
                ->orderBy('ld.created_at', 'desc')
                ->get();

            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * POST /estudiante/lista-deseos
     * Agregar un curso a la lista de deseos.
     */
    public function store(Request $request)
    {
        $user = $request->sessionUser;

        $validator = Validator::make($request->all(), [
            'id_curso' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        try {
            // Verificar si ya existe
            $existe = DB::table('lista_deseos')
                ->where('id_usuario', $user->id_usuario)
                ->where('id_curso', $request->id_curso)
                ->exists();

            if ($existe) {
                return response()->json(['message' => 'El curso ya está en tu lista de deseos'], 200);
            }

            $id = DB::table('lista_deseos')->insertGetId([
                'id_usuario'  => $user->id_usuario,
                'id_curso'    => $request->id_curso,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json(['id_listadeseos' => $id, 'message' => 'Curso agregado a lista de deseos']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al agregar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /estudiante/lista-deseos/{id}
     * Quitar un curso de la lista de deseos.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->sessionUser;

        try {
            DB::table('lista_deseos')
                ->where('id_listadeseos', $id)
                ->where('id_usuario', $user->id_usuario)
                ->delete();

            return response()->json(['message' => 'Curso eliminado de lista de deseos']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
