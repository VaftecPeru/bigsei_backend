<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Persona;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Matricula;
use App\Models\PeriodoCursoPrecio;
use App\Models\PeriodoCurso;
use App\Models\MatriculaCurso;
use App\Models\UsuarioRol;
use App\Models\Pago;
use App\Models\MatriculaPagos;
use App\Http\Controllers\AuthController;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MatriculaController extends Controller
{
    public function cursoLibres(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("periodo_curso as a")
            ->join("periodo as b", "a.id_periodo", "b.id_periodo")
            ->join("empresa as d", "a.id_empresa", "d.id_empresa")
            ->join("curso as e", "a.id_curso", "e.id_curso")
            ->join("tipo_categoria as f", "a.id_tipocategoria", "f.id_tipocategoria")
            ->leftJoin("resena as i", "a.id_periodocurso", "i.id_periodocurso")
            ->select(
                "a.id_periodocurso",
                "a.detalle",
                "e.url_img",
                "e.id_archivo",
                DB::raw("d.razon_social as empresa_razon_social"),
                DB::raw("e.nombre as curso_nombre"),
                DB::raw("round(
                    sum(case when i.id_resena is not null then i.rating else 0 end)
                    /
                    if(
                        sum(case when i.id_resena is not null then 1 else 0 end) = 0,
                        1,
                        sum(case when i.id_resena is not null then 1 else 0 end)
                    )
                , 1) as rating"),
                DB::raw("sum(case when i.id_resena is not null then 1 else 0 end) as reviews"),
                "a.es_sincrono"
            )
            ->where("a.estado", "1")
            ->where("b.esta_abierto", "1")
            ->whereRaw("(a.es_sincrono = '0' || f.visible_web = '1')");

        if(isset($request->id_tipocategoria)) {
            $paginate->where("a.id_tipocategoria", $request->id_tipocategoria);
        }

        $paginate = $paginate->groupBy("a.id_periodocurso", "a.detalle", "e.url_img", "d.razon_social", "e.nombre", "e.id_archivo", "a.es_sincrono")
            ->orderBy("empresa_razon_social", "asc")
            ->orderBy("curso_nombre", "asc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function showCursoLibres($id_periodocurso)
    {
        $curso = DB::table("periodo_curso as a")
            ->join("periodo as b", "a.id_periodo", "b.id_periodo")
            ->join("empresa as d", "a.id_empresa", "d.id_empresa")
            ->join("curso as e", "a.id_curso", "e.id_curso")
            ->join("tipo_categoria as f", "a.id_tipocategoria", "f.id_tipocategoria")
            ->join("periodo_ciclo as z", "a.id_periodociclo", "z.id_periodociclo")
            ->join("tipo_titulo_academico as x", "z.id_tipotituloacademico", "x.id_tipotituloacademico")
            ->join("titulo_academico as y", "z.id_tituloacademico", "y.id_tituloacademico")
            ->join("carrera as w", "z.id_carrera", "w.id_carrera")
            ->leftJoin("persona as g", "a.id_docente", "g.id_persona")
            ->leftJoin("tipo_modalidadestudio as m", "a.id_tipomodalidadestudio", "m.id_tipomodalidadestudio")
            ->select(
                "a.id_periodocurso",
                "a.detalle",
                "a.es_sincrono",
                "a.fecha_inicio",
                "a.fecha_fin",
                DB::raw("d.razon_social as empresa_razon_social"),
                DB::raw("e.nombre as curso_nombre"),
                DB::raw("concat((datediff(a.fecha_fin, a.fecha_inicio) div 30), ' meses medio-tiempo') as tiempo_de_duracion"),
                DB::raw("g.nombre_completo as docente_nombre"),
                DB::raw("x.nombre as tipotituloacademico_nombre"),
                DB::raw("y.nombre as tituloacademico_nombre"),
                DB::raw("w.nombre as carrera_nombre"),
                DB::raw("d.id_archivo as empresa_id_archivo"),
                DB::raw("e.id_archivo as curso_id_archivo"),
                DB::raw("m.nombre as modalidadestudio_nombre"),
                DB::raw("case when a.es_sincrono = '1' then 'Sincrónico' else 'Asincrónico' end as tipo_modalidad"),
                DB::raw("case when a.es_sincrono = '1' then 'Clases en vivo con horario fijo' else 'Aprende a tu propio ritmo' end as modalidad_descripcion"),
                DB::raw("f.nombre as categoria_nombre")
            )
            ->where("a.id_periodocurso", $id_periodocurso)
            ->where("a.estado", "1")
            ->where("b.estado", "1")
            ->whereRaw("(a.es_sincrono = '0' || f.visible_web = '1')")
            ->first();

        if($curso) {
            $curso->precios = DB::table("periodo_curso_precio")
                ->select(
                    "id_periodocursoprecio",
                    "importe",
                    "tipo",
                    DB::raw("case when tipo = '1' then 'Anual' else 'Mensual' end as tipo_descripcion")
                )
                ->where("id_periodocurso", $id_periodocurso)
                ->where("estado", "1")
                ->get();
        }

        return response()->json($curso);
    }

    public function storeCursoLibres(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_periodocursoprecio' => 'required',
            'importe' => 'required|numeric|min:0|not_in:0',
            'id_tipodocumento' => 'required',
            'numero_documento' => 'required|string|max:20|min:8',
            'nombre_completo' => 'required|string|max:450',
            'telefono' => 'required|string|max:20|min:7',
            'direccion' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255',
            'numero_operacion' => 'required|max:50',
            'importe_operacion' => 'required|numeric|not_in:0|min:1',
        ], [
            'id_periodocursoprecio' => 'El precio es requerido',
            'importe' => 'El importe es requerido',
            'id_tipodocumento' => 'El tipo documento es requerido',
            'numero_documento' => 'El número documento es requerido',
            'nombre_completo' => 'El nombre es requerido',
            'telefono' => 'El teléfono es requerido',
            'direccion' => 'El dirección es requerido',
            'correo' => 'El correo es requerido',
            'numero_operacion' => 'El número operación es requerido',
            'importe_operacion' => 'El importe operacion es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(",", $validator->messages()->all()), 400);
        }

        try {
            DB::beginTransaction();

            // 1. Validar Precio y Curso
            $periodoCursoPrecio = PeriodoCursoPrecio::find($request->id_periodocursoprecio);
            if (!$periodoCursoPrecio) {
                return response()->json('¡Atención! El precio del curso no existe.', 400);
            }
            $periodoCurso = PeriodoCurso::find($periodoCursoPrecio->id_periodocurso);

            // 2. Buscar o Crear Persona
            $esNuevoUsuario = false;
            $persona = Persona::where("numero_documento", $request->numero_documento)
                ->where("id_tipodocumento", $request->id_tipodocumento)
                ->first();
//1.
            if (!$persona) {
                // Si la persona no existe por documento, verificar correo
                $correoEnUso = Usuario::where("email", $request->correo)->exists();
                if ($correoEnUso) {
                     return response()->json('¡Atención! El correo ya está registrado por otro usuario.', 400);
                }

                $persona = Persona::create([
                    "id_tipodocumento" => $request->id_tipodocumento,
                    "numero_documento" => $request->numero_documento,
                    "nombre" => $request->nombre_completo,
                    "nombre_completo" => $request->nombre_completo,
                    "telefono" => $request->telefono,
                    "direccion" => $request->direccion,
                    "correo" => $request->correo,
                    "id_tiponiveleducativo" => $request->id_tiponiveleducativo ?? null,
                    "programa_estudios" => $request->programa_estudios ?? null,
                    "id_tiponiveleducativo_formativo" => $request->id_tiponiveleducativo_formativo ?? null,
                    "fechareg" => now(),
                    "estado" => '1'
                ]);
                $esNuevoUsuario = true;
            } else {
                 // Si existe, actualizamos datos de contacto si vinieron
                 $persona->update([
                     "nombre_completo" => $request->nombre_completo,
                     "telefono" => $request->telefono,
                     "direccion" => $request->direccion,
                     "id_tiponiveleducativo" => $request->id_tiponiveleducativo ?? $persona->id_tiponiveleducativo,
                     "programa_estudios" => $request->programa_estudios ?? $persona->programa_estudios,
                     "id_tiponiveleducativo_formativo" => $request->id_tiponiveleducativo_formativo ?? $persona->id_tiponiveleducativo_formativo,
                 ]);
            }

            // 3. Asegurar Estudiante
            if (!Estudiante::find($persona->id_persona)) {
                Estudiante::create([
                    "id_estudiante" => $persona->id_persona,
                    "estado" => "1",
                    "fechareg" => now()
                ]);
            }

            // 4. Asegurar Usuario
            if (!Usuario::find($persona->id_persona)) {
                Usuario::create([
                    "id_usuario" => $persona->id_persona,
                    "email" => $request->correo,
                    "username" => $request->correo,
                    "password" => Hash::make("123"),
                    "estado" => "1",
                    "fechareg" => now()
                ]);
            }

            // 5. Asegurar Rol de Estudiante
            $idRolStudent = 5;
            $usuarioRol = UsuarioRol::where('id_usuario', $persona->id_persona)
                ->where('id_rol', $idRolStudent)
                ->first();

            if (!$usuarioRol) {
                UsuarioRol::create([
                    "id_empresa" => $periodoCurso->id_empresa,
                    "id_usuario" => $persona->id_persona,
                    "id_rol" => $idRolStudent,
                    "es_principal" => "1"
                ]);
            }

            // 6. Verificar Idempotencia de Compra (Evitar Duplicados)
            $matriculaExistente = DB::table('matricula as m')
                ->join('matricula_curso as mc', 'm.id_matricula', '=', 'mc.id_matricula')
                ->where('m.id_estudiante', $persona->id_persona)
                ->where('mc.id_periodocurso', $periodoCurso->id_periodocurso)
                ->where('m.estado', '1')
                ->select('m.id_matricula')
                ->first();

            if ($matriculaExistente) {
                DB::commit();
                $token = AuthController::resetToken($persona->id_persona, $periodoCurso->id_empresa, $idRolStudent);
                $result = Matricula::find($matriculaExistente->id_matricula);
                $result->token = $token;
                $result->mensaje = "Usuario ya matriculado previamente";
                return response()->json($result);
            }

            // 7. Crear Matrícula
            $matricula = Matricula::create([
                "id_empresa" => $periodoCurso->id_empresa,
                "id_periodo" => $periodoCurso->id_periodo,
                "id_estudiante" => $persona->id_persona,
                "importe" => $request->importe,
                "tipo" => "1", // sin membresia
                "estado" => "1", // Activo/Pagado
                "fechareg" => now()
            ]);

            // 8. Registrar Pago y Vincular
            $metodoPago = \App\Models\MetodoPago::where('nombre', 'like', '%transferencia%')->first();

            if ($metodoPago) {
                $idMetodoPago = $metodoPago->idMetodoPago;
            } else {
                 $firstMp = \App\Models\MetodoPago::first();
                 $idMetodoPago = $firstMp ? $firstMp->idMetodoPago : 1;
            }

            // Calcular montos (Asumiendo que el monto ingresado es el Total)
            $total = $request->importe_operacion;
            $subtotal = $total / 1.18;
            $igv = $total - $subtotal;

            $pago = Pago::create([
                "idUsuario" => $persona->id_persona,
                "idMetodoPago" => $idMetodoPago,
                "importe" => $subtotal,
                "igv" => $igv,
                "total" => $total,
                "descripcion" => "Curso Libre - Operación: " . $request->numero_operacion,
                "fechaPago" => now()
            ]);

            MatriculaPagos::create([
                "idMatricula" => $matricula->id_matricula,
                "idPago" => $pago->idPago
            ]);

            // 9. Crear Detalle del Curso
            MatriculaCurso::create([
                "id_empresa" => $periodoCurso->id_empresa,
                "id_matricula" => $matricula->id_matricula,
                "id_periodocurso" => $periodoCurso->id_periodocurso,
                "id_curso" => $periodoCurso->id_curso,
                "fechareg" => now()
            ]);

            // Generar Token
            $token = AuthController::resetToken($persona->id_persona, $periodoCurso->id_empresa, $idRolStudent);

            DB::commit();

            $result = Matricula::find($matricula->id_matricula); // Recarga para asegurar datos frescos

            $response = $result->toArray();
            $response['token'] = $token;
            $response['es_nuevo_usuario'] = $esNuevoUsuario;
            if (isset($result->mensaje)) $response['mensaje'] = $result->mensaje; // Para el caso de idempotencia

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json("Error en el proceso de matrícula: " . $e->getMessage(), 500);
        }
    }

    public function precioCursoLibres(Request $request)
    {
        $result = DB::table('periodo_curso_precio as a')
            ->select(
                'a.id_periodocursoprecio',
                'a.importe',
                'a.tipo',
                DB::raw("case when a.importe >= 100 then round((a.importe/12), 2) else null end as importe_mes"),
                DB::raw("case
                    when a.importe >= 100 then 'Acceso Anual'
                    else 'Acceso Mensual'
                end as tipo_descripcion"),
                DB::raw("case when a.importe >= 100 then 'año' else 'mes' end as tipo_nombre")
            )
            ->where("a.estado", "1");

        if(isset($request->id_periodocurso)) {
            $result->where("a.id_periodocurso", $request->id_periodocurso);
        }

        $result->orderBy("a.importe", "desc");
        $result = $result->get();

        return response()->json($result);
    }

    public function categoriaCursosLibres()
    {
        $result = DB::table('periodo_curso as a')
            ->join('periodo as b', 'a.id_periodo', DB::raw("b.id_periodo and a.estado = '1' and b.esta_abierto = '1'"))
            ->join('tipo_categoria as c', 'a.id_tipocategoria', DB::raw("c.id_tipocategoria and (a.es_sincrono = '0' || c.visible_web = '1')"))
            ->rightJoin('tipo_categoria as d', 'a.id_tipocategoria', 'd.id_tipocategoria')
            ->select(
                "d.id_tipocategoria",
                "d.orden",
                "d.nombre",
                DB::raw("sum(case when a.id_tipocategoria is not null then 1 else 0 end) as cant_cursos")
            )
            ->where("d.tipo", "1");

        $result->groupBy("d.id_tipocategoria", "d.orden", "d.nombre");
        $result = $result->get();

        return response()->json($result);
    }

    /**
     * Genera y descarga el comprobante de matrícula en PDF
     */
    public function generarComprobante(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_estudiante' => 'required|string',
                'dni_estudiante' => 'required|string',
                'correo_estudiante' => 'required|email',
                'nombre_curso' => 'required|string',
                'precio' => 'nullable|numeric',
                'duracion_curso' => 'nullable|string',
                'docente_nombre' => 'nullable|string',
                'modalidad' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errores' => $validator->errors()], 422);
            }

            // Generar código único para el comprobante
            $codigoComprobante = 'MAT-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

            // Convertir logo a base64 para DomPDF
            $logoPath = public_path('img/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoBase64 = 'data:image/png;base64,' . $logoData;
            }

            $dataPdf = [
                'nombre_estudiante' => $request->nombre_estudiante,
                'dni_estudiante' => $request->dni_estudiante,
                'correo_estudiante' => $request->correo_estudiante,
                'nombre_curso' => $request->nombre_curso,
                'precio' => $request->precio,
                'duracion_curso' => $request->duracion_curso,
                'docente_nombre' => $request->docente_nombre,
                'modalidad' => $request->modalidad ?? 'Virtual',
                'fecha_matricula' => Carbon::now()->format('d/m/Y'),
                'codigo_comprobante' => $codigoComprobante,
                'empresa_nombre' => 'BIGSEI',
                'logo_base64' => $logoBase64,
            ];

            $pdf = Pdf::loadView('pdf.comprobante_matricula', $dataPdf);
            $pdf->setPaper('A4', 'portrait');

            $nombreArchivo = 'comprobante_matricula_' . $codigoComprobante . '.pdf';

            return $pdf->download($nombreArchivo);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error Generar Comprobante: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            return response()->json(['mensaje' => 'Error al generar el comprobante: ' . $e->getMessage()], 500);
        }
    }
}
