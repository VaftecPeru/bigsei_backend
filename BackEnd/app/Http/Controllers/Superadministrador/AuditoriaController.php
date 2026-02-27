<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AuditoriaController — Registro de actividad de usuarios en el sistema para el Superadministrador.
 * Usa la tabla `actividad_usuario` que ya existe en la BD.
 */
class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('actividad_usuario as a')
            ->join('usuario as b', 'a.id_usuario', 'b.id_usuario')
            ->join('empresa as c', 'a.id_empresa', 'c.id_empresa')
            ->select(
                'a.id_actividadusuario',
                'b.username',
                'b.email',
                'c.razon_social as empresa',
                'a.pantalla',
                'a.descripcion',
                'a.ip',
                'a.fechareg',
            );

        if (isset($request->text_search) && strlen($request->text_search) > 0) {
            $texto = '%' . str_replace(' ', '%', $request->text_search) . '%';
            $result->whereRaw(
                "upper(concat(b.username, b.email, c.razon_social, a.pantalla)) LIKE upper(?)",
                [$texto]
            );
        }

        if (isset($request->id_empresa) && $request->id_empresa) {
            $result->where('a.id_empresa', $request->id_empresa);
        }

        if (isset($request->fecha_desde) && $request->fecha_desde) {
            $result->where('a.fechareg', '>=', $request->fecha_desde);
        }

        if (isset($request->fecha_hasta) && $request->fecha_hasta) {
            $result->where('a.fechareg', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $result->orderBy('a.fechareg', 'desc');

        $perPage = $request->per_page ?? 50;
        return response()->json($result->paginate($perPage));
    }

    public function stats()
    {
        $hoy       = DB::table('actividad_usuario')->whereDate('fechareg', today())->count();
        $semana    = DB::table('actividad_usuario')->where('fechareg', '>=', now()->subWeek())->count();
        $mes       = DB::table('actividad_usuario')->where('fechareg', '>=', now()->subMonth())->count();
        $empresas  = DB::table('actividad_usuario')->distinct('id_empresa')->count('id_empresa');

        $masActivos = DB::table('actividad_usuario as a')
            ->join('usuario as b', 'a.id_usuario', 'b.id_usuario')
            ->select('b.username', 'b.email', DB::raw('COUNT(*) as total'))
            ->groupBy('a.id_usuario', 'b.username', 'b.email')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return response()->json(compact('hoy', 'semana', 'mes', 'empresas', 'masActivos'));
    }
}
