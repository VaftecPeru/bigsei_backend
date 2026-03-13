<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VendedorRolController extends Controller
{
    /**
     * Helper: obtener id_vendedor del usuario autenticado
     */
    private function getVendedorId(Request $request)
    {
        $user = $request->get('sessionUser');
        return $user->id_usuario ?? null;
    }

    /**
     * Dashboard del vendedor: estadísticas principales
     */
    public function dashboard(Request $request)
    {
        $idVendedor = $this->getVendedorId($request);

        // Total clientes (empresas asignadas)
        $totalClientes = DB::table('empresa')
            ->where('id_vendedor', $idVendedor)
            ->count();

        // Clientes activos (con licencia activa)
        $clientesActivos = DB::table('empresa as e')
            ->join('licencia as l', 'e.id_empresa', '=', 'l.id_empresa')
            ->where('e.id_vendedor', $idVendedor)
            ->where('l.estado', '1')
            ->where('l.fecha_fin', '>=', Carbon::now())
            ->distinct('e.id_empresa')
            ->count('e.id_empresa');

        // Ventas del mes (licencias creadas este mes para empresas del vendedor)
        $ventasMes = DB::table('licencia as l')
            ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
            ->where('e.id_vendedor', $idVendedor)
            ->whereMonth('l.fecha_inicio', Carbon::now()->month)
            ->whereYear('l.fecha_inicio', Carbon::now()->year)
            ->count();

        // Renovaciones próximas (30 días)
        $renovacionesProximas = DB::table('licencia as l')
            ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
            ->where('e.id_vendedor', $idVendedor)
            ->where('l.estado', '1')
            ->whereBetween('l.fecha_fin', [Carbon::now(), Carbon::now()->addDays(30)])
            ->count();

        // Datos del vendedor (comisión, cuota)
        $vendedor = DB::table('vendedor as v')
            ->join('persona as p', 'v.id_vendedor', '=', 'p.id_persona')
            ->where('v.id_vendedor', $idVendedor)
            ->select('v.comision', 'v.zona_ventas', 'v.cuota_mensual', 'p.nombre_completo')
            ->first();

        // Comisiones del mes (estimado)
        $ingresosMes = DB::table('licencia as l')
            ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
            ->join('licencia_tipo as lt', 'l.id_licenciatipo', '=', 'lt.id_licenciatipo')
            ->where('e.id_vendedor', $idVendedor)
            ->whereMonth('l.fecha_inicio', Carbon::now()->month)
            ->whereYear('l.fecha_inicio', Carbon::now()->year)
            ->sum('lt.precio');

        $comisionPorcentaje = $vendedor ? ($vendedor->comision ?? 0) : 0;
        $comisionesMes = $ingresosMes * ($comisionPorcentaje / 100);

        return response()->json([
            'total_clientes' => $totalClientes,
            'clientes_activos' => $clientesActivos,
            'ventas_mes' => $ventasMes,
            'renovaciones_proximas' => $renovacionesProximas,
            'comisiones_mes' => round($comisionesMes, 2),
            'ingresos_mes' => round($ingresosMes, 2),
            'vendedor' => $vendedor,
        ]);
    }

    /**
     * Mis clientes: empresas asignadas al vendedor
     */
    public function misClientes(Request $request)
    {
        $idVendedor = $this->getVendedorId($request);

        $query = DB::table('empresa as e')
            ->leftJoin('licencia as l', function ($join) {
                $join->on('e.id_empresa', '=', 'l.id_empresa')
                    ->where('l.estado', '=', '1');
            })
            ->where('e.id_vendedor', $idVendedor)
            ->select(
                'e.id_empresa',
                'e.razon_social',
                'e.numero_documento as ruc',
                'e.telefono',
                'e.correo',
                'e.estado as empresa_estado',
                'l.fecha_inicio as licencia_inicio',
                'l.fecha_fin as licencia_fin',
                'l.estado as licencia_estado'
            );

        if ($request->text_search) {
            $texto = str_replace(' ', '%', $request->text_search);
            $query->whereRaw("UPPER(CONCAT(COALESCE(e.razon_social,''), COALESCE(e.numero_documento,''))) LIKE UPPER(?)", ['%' . $texto . '%']);
        }

        $query->orderBy('e.razon_social', 'asc');

        return response()->json($query->get());
    }

    /**
     * Mis suscripciones: membresías de personas asociadas a las empresas del vendedor
     */
    public function misSuscripciones(Request $request)
    {
        $idVendedor = $this->getVendedorId($request);

        $query = DB::table('membresia as m')
            ->join('persona as p', 'm.id_persona', '=', 'p.id_persona')
            ->join('membresia_tipo as mt', 'm.id_membresiatipo', '=', 'mt.id_membresiatipo')
            ->leftJoin('empresa as e', function ($join) use ($idVendedor) {
                $join->where('e.id_vendedor', '=', $idVendedor);
            })
            ->select(
                'm.id_membresia',
                'p.nombre_completo',
                'p.correo',
                'mt.nombre as tipo_membresia',
                'mt.precio',
                'm.fecha_inicio',
                'm.fecha_fin',
                'm.estado'
            )
            ->orderBy('m.fecha_inicio', 'desc');

        if ($request->estado) {
            $query->where('m.estado', $request->estado);
        }

        return response()->json($query->get());
    }

    /**
     * Registrar empresa nueva con licencia
     */
    public function registrarEmpresa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'razon_social'    => 'required|string|max:255',
            'ruc'             => 'nullable|string|max:20',
            'telefono'        => 'nullable|string|max:20',
            'correo'          => 'nullable|email|max:100',
            'direccion'       => 'nullable|string|max:255',
            'id_licenciatipo' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $idVendedor = $this->getVendedorId($request);

        DB::beginTransaction();
        try {
            // Crear empresa
            $idEmpresa = DB::table('empresa')->insertGetId([
                'razon_social'    => $request->razon_social,
                'numero_documento' => $request->ruc ?? '',
                'telefono'        => $request->telefono ?? '',
                'correo'          => $request->correo ?? '',
                'direccion'       => $request->direccion ?? '',
                'id_vendedor'     => $idVendedor,
                'estado'          => '1',
            ]);

            // Obtener tipo de licencia
            $tipoLicencia = DB::table('licencia_tipo')
                ->where('id_licenciatipo', $request->id_licenciatipo)
                ->first();

            if (!$tipoLicencia) {
                DB::rollBack();
                return response()->json(['error' => 'Tipo de licencia no encontrado'], 404);
            }

            // Crear licencia
            DB::table('licencia')->insert([
                'id_empresa'      => $idEmpresa,
                'id_licenciatipo' => $request->id_licenciatipo,
                'fecha_inicio'    => Carbon::now(),
                'fecha_fin'       => Carbon::now()->addYear(),
                'estado'          => '1',
            ]);

            DB::commit();

            return response()->json([
                'message'    => 'Empresa registrada con licencia correctamente',
                'id_empresa' => $idEmpresa,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar empresa: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Renovaciones: licencias por vencer en los próximos N días
     */
    public function renovaciones(Request $request)
    {
        $idVendedor = $this->getVendedorId($request);
        $dias = (int) ($request->dias ?? 30);

        $result = DB::table('licencia as l')
            ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
            ->leftJoin('licencia_tipo as lt', 'l.id_licenciatipo', '=', 'lt.id_licenciatipo')
            ->where('e.id_vendedor', $idVendedor)
            ->where('l.estado', '1')
            ->whereBetween('l.fecha_fin', [Carbon::now(), Carbon::now()->addDays($dias)])
            ->select(
                'l.id_licencia',
                'e.id_empresa',
                'e.razon_social',
                'e.numero_documento as ruc',
                'lt.nombre as tipo_licencia',
                'l.fecha_inicio',
                'l.fecha_fin',
                DB::raw('DATEDIFF(l.fecha_fin, NOW()) as dias_restantes')
            )
            ->orderBy('l.fecha_fin', 'asc')
            ->get();

        return response()->json($result);
    }

    /**
     * Comisiones: ganancias por venta del vendedor
     */
    public function comisiones(Request $request)
    {
        $idVendedor = $this->getVendedorId($request);
        $mes = $request->mes ?? Carbon::now()->month;
        $anio = $request->anio ?? Carbon::now()->year;

        // Datos del vendedor
        $vendedor = DB::table('vendedor')->where('id_vendedor', $idVendedor)->first();
        $porcentajeComision = $vendedor ? ($vendedor->comision ?? 0) : 0;

        // Ventas del periodo
        $ventas = DB::table('licencia as l')
            ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
            ->join('licencia_tipo as lt', 'l.id_licenciatipo', '=', 'lt.id_licenciatipo')
            ->where('e.id_vendedor', $idVendedor)
            ->whereMonth('l.fecha_inicio', $mes)
            ->whereYear('l.fecha_inicio', $anio)
            ->select(
                'e.razon_social',
                'lt.nombre as tipo_licencia',
                'lt.precio',
                'l.fecha_inicio',
                DB::raw("ROUND(lt.precio * {$porcentajeComision} / 100, 2) as comision")
            )
            ->orderBy('l.fecha_inicio', 'desc')
            ->get();

        $totalVentas = $ventas->sum('precio');
        $totalComisiones = $ventas->sum('comision');

        return response()->json([
            'porcentaje_comision' => $porcentajeComision,
            'total_ventas'        => round($totalVentas, 2),
            'total_comisiones'    => round($totalComisiones, 2),
            'detalle'             => $ventas,
            'mes'                 => $mes,
            'anio'                => $anio,
        ]);
    }

    /**
     * Estadísticas de ventas (dashboard avanzado)
     */
    public function estadisticasVentas(Request $request)
    {
        $idVendedor = $this->getVendedorId($request);

        // Ventas por mes (últimos 6 meses)
        $ventasPorMes = DB::table('licencia as l')
            ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
            ->join('licencia_tipo as lt', 'l.id_licenciatipo', '=', 'lt.id_licenciatipo')
            ->where('e.id_vendedor', $idVendedor)
            ->where('l.fecha_inicio', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('YEAR(l.fecha_inicio) as anio'),
                DB::raw('MONTH(l.fecha_inicio) as mes'),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(lt.precio) as ingresos')
            )
            ->groupBy('anio', 'mes')
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        // Conversiones (empresas creadas vs licencias activas)
        $empresasTotal = DB::table('empresa')->where('id_vendedor', $idVendedor)->count();
        $empresasActivas = DB::table('empresa as e')
            ->join('licencia as l', 'e.id_empresa', '=', 'l.id_empresa')
            ->where('e.id_vendedor', $idVendedor)
            ->where('l.estado', '1')
            ->distinct('e.id_empresa')
            ->count('e.id_empresa');

        $tasaConversion = $empresasTotal > 0 ? round(($empresasActivas / $empresasTotal) * 100, 1) : 0;

        return response()->json([
            'ventas_por_mes'   => $ventasPorMes,
            'empresas_total'   => $empresasTotal,
            'empresas_activas' => $empresasActivas,
            'tasa_conversion'  => $tasaConversion,
        ]);
    }
}
