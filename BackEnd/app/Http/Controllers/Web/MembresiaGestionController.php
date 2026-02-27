<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Membresia;
use App\Models\MembresiaTipo;

class MembresiaGestionController extends Controller
{
    /**
     * GET /api/web/membresia/historial
     * Historial completo de membresías del usuario.
     */
    public function historial(Request $request)
    {
        $user = $request->sessionUser;

        $historial = DB::table('membresia as m')
            ->join('membresia_tipo as mt', 'm.id_membresiatipo', '=', 'mt.id_membresiatipo')
            ->where('m.id_persona', $user->id_usuario)
            ->select(
                'm.id_membresia',
                'mt.nombre as plan',
                'mt.descripcion',
                'mt.es_anual',
                'm.precio',
                'm.fecha_inicio',
                'm.fecha_fin',
                'm.estado',
                'm.fechareg as fecha_compra',
                DB::raw("CASE WHEN m.fecha_fin < NOW() THEN 'vencida' WHEN m.estado = '0' THEN 'cancelada' ELSE 'activa' END as estado_etiqueta"),
                DB::raw("DATEDIFF(m.fecha_fin, NOW()) as dias_restantes")
            )
            ->orderBy('m.fechareg', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $historial,
        ]);
    }

    /**
     * POST /api/web/membresia/{id}/renovar
     * Renueva una membresía activa extendiendo su fecha_fin.
     */
    public function renovar(Request $request, $id)
    {
        $user = $request->sessionUser;

        $membresia = DB::table('membresia as m')
            ->join('membresia_tipo as mt', 'm.id_membresiatipo', '=', 'mt.id_membresiatipo')
            ->where('m.id_membresia', $id)
            ->where('m.id_persona', $user->id_usuario)
            ->select('m.*', 'mt.es_anual', 'mt.nombre')
            ->first();

        if (!$membresia) {
            return response()->json('¡Membresía no encontrada o no te pertenece!', 404);
        }

        $validator = Validator::make($request->all(), [
            'precio'           => 'required|numeric|min:0',
            'numero_operacion' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        // Calcular nueva fecha_fin desde hoy o desde fecha_fin actual, lo que sea mayor
        $base = max(now(), \Carbon\Carbon::parse($membresia->fecha_fin));
        $nuevaFechaFin = $membresia->es_anual == '1'
            ? $base->addMonths(12)
            : $base->addMonth();

        DB::table('membresia')
            ->where('id_membresia', $id)
            ->update([
                'fecha_fin'  => $nuevaFechaFin,
                'estado'     => '1',
                'precio'     => $request->precio,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success'      => true,
            'message'      => "Membresía '{$membresia->nombre}' renovada correctamente",
            'fecha_fin'    => $nuevaFechaFin,
        ]);
    }

    /**
     * POST /api/web/membresia/{id}/cancelar
     * Cancela / desactiva una membresía.
     */
    public function cancelar(Request $request, $id)
    {
        $user = $request->sessionUser;

        $membresia = Membresia::where('id_membresia', $id)
            ->where('id_persona', $user->id_usuario)
            ->first();

        if (!$membresia) {
            return response()->json('¡Membresía no encontrada!', 404);
        }

        $membresia->update(['estado' => '0']);

        return response()->json([
            'success' => true,
            'message' => 'Membresía cancelada. Seguirás teniendo acceso hasta la fecha de vencimiento.',
        ]);
    }

    /**
     * POST /api/web/membresia/{id}/cambiar-plan
     * Cambia el tipo de membresía (upgrade/downgrade).
     */
    public function cambiarPlan(Request $request, $id)
    {
        $user = $request->sessionUser;

        $validator = Validator::make($request->all(), [
            'id_membresiatipo' => 'required|integer',
            'precio'           => 'required|numeric|min:0',
            'numero_operacion' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        $nuevoTipo = MembresiaTipo::find($request->id_membresiatipo);
        if (!$nuevoTipo) {
            return response()->json('¡El tipo de membresía no existe!', 400);
        }

        $membresia = Membresia::where('id_membresia', $id)
            ->where('id_persona', $user->id_usuario)
            ->first();

        if (!$membresia) {
            return response()->json('¡Membresía no encontrada!', 404);
        }

        $nuevaFechaFin = $nuevoTipo->es_anual == '1'
            ? now()->addMonths(12)
            : now()->addMonth();

        $membresia->update([
            'id_membresiatipo' => $request->id_membresiatipo,
            'precio'           => $request->precio,
            'fecha_inicio'     => now(),
            'fecha_fin'        => $nuevaFechaFin,
            'estado'           => '1',
        ]);

        return response()->json([
            'success'   => true,
            'message'   => "Plan cambiado a '{$nuevoTipo->nombre}' correctamente",
            'fecha_fin' => $nuevaFechaFin,
        ]);
    }

    /**
     * GET /api/web/membresia/verificar-activa
     * Verifica si el usuario tiene membresía activa y vigente (para bloquear matrícula).
     */
    public function verificarActiva(Request $request)
    {
        $user = $request->sessionUser;

        $activa = DB::table('membresia')
            ->where('id_persona', $user->id_usuario)
            ->where('estado', '1')
            ->where('fecha_fin', '>=', now())
            ->exists();

        return response()->json([
            'tiene_membresia_activa' => $activa,
            'puede_matricularse'     => $activa,
        ]);
    }

    /**
     * GET /api/web/membresia/periodo-gracia
     * Verifica si el usuario está en período de gracia (vencida hace menos de 15 días).
     */
    public function periodoGracia(Request $request)
    {
        $user = $request->sessionUser;

        $enGracia = DB::table('membresia')
            ->where('id_persona', $user->id_usuario)
            ->where('estado', '1')
            ->where('fecha_fin', '<', now())
            ->where('fecha_fin', '>=', now()->subDays(15))
            ->exists();

        return response()->json([
            'en_periodo_gracia' => $enGracia,
            'dias_gracia'       => 15,
            'message'           => $enGracia
                ? 'Tu membresía venció pero tienes 15 días de gracia. Renueva pronto.'
                : 'No estás en período de gracia.',
        ]);
    }
}
