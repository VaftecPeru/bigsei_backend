<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Empresa;

/**
 * ClienteController — CRUD completo para la gestión de clientes del Superadministrador.
 * Los clientes son empresas/entidades que contratan los servicios o membresías de BigSei.
 */
class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $result = Cliente::with([
                'empresa:id_empresa,id_tipodocumento,numero_documento,razon_social,correo,telefono,direccion',
                'empresa.tipoDocumento:id_tipodocumento,nombre,siglas',
                'persona:id_persona,nombre,apellido_paterno,apellido_materno,nombre_completo,id_tipodocumento,numero_documento,correo,telefono,direccion',
                'persona.tipoDocumento:id_tipodocumento,nombre,siglas'
            ])
            ->leftJoin('persona as b', 'cliente.id_persona', 'b.id_persona')
            ->leftJoin('empresa as c', 'cliente.id_empresa', 'c.id_empresa')
            ->select(
                'cliente.id_cliente',
                'cliente.id_persona',
                'cliente.id_empresa',
                'cliente.tipo',
                'cliente.estado'
            );

        if (isset($request->text_search) && strlen($request->text_search) > 0) {
            $texto = str_replace(' ', '%', $request->text_search);
            $result->whereRaw("
                upper(concat(
                    coalesce(b.nombre_completo, ''),
                    ' ',
                    coalesce(b.correo, ''),
                    ' ',
                    coalesce(b.numero_documento, ''),
                    ' ',
                    coalesce(b.telefono, ''),
                    ' ',
                    coalesce(c.numero_documento, ''),
                    ' ',
                    coalesce(c.razon_social, ''),
                    ' ',
                    coalesce(c.correo, ''),
                    ' ',
                    coalesce(c.telefono, '')
                )) LIKE upper(?)
            ", ['%' . $texto . '%']);
        }
        $result->orderBy('b.nombre_completo', 'asc');
        return response()->json($result->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tipodocumento'  => 'required',
            'numero_documento'  => 'required|string|max:20',
            'nombre'            => 'nullable|string|max:150',
            'apellido_paterno'  => 'nullable|string|max:150',
            'apellido_materno'  => 'nullable|string|max:150',
            'correo'            => 'nullable|email|max:255',
            'telefono'          => 'nullable|string|max:20',
            'direccion'         => 'nullable|string|max:255',
            'sexo'              => 'nullable|string|max:1',
            'fecha_nacimiento'  => 'nullable|date',
            'razon_social'      => 'nullable|string|max:255',
            'tipo'              => 'required|string|max:1',
            'estado'            => 'required|string|max:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if($request->tipo == 'N') {
            $persona = Persona::where('id_tipodocumento',$request->id_tipodocumento)
                ->where('numero_documento',$request->numero_documento)
                ->first();
            if ($persona) {
                return response()->json(['errors' => 'Error: El número de documento esta registrado.'], 422);
            }
        } else if($request->tipo == 'J') {
            $empresa = Empresa::where('id_tipodocumento',$request->id_tipodocumento)
                ->where('numero_documento',$request->numero_documento)
                ->first();
            if ($empresa) {
                return response()->json(['errors' => 'Error: El número de documento esta registrado.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $id_persona = null;
            $id_empresa = null;
            if($request->tipo == 'N') {
                $nombreCompleto = implode(' ', array_filter([
                    $request->nombre,
                    $request->apellido_paterno,
                    $request->apellido_materno
                ]));
                $persona = Persona::create([
                    'id_tipodocumento' => $request->id_tipodocumento,
                    'numero_documento' => $request->numero_documento,
                    'nombre' => $request->nombre,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'correo' => $request->correo,
                    'nombre_completo' => $nombreCompleto,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'sexo' => $request->sexo,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'fechareg' => now()
                ]);
                $id_persona = $persona->id_persona;
            } else if($request->tipo == 'J') {
                $empresa = Empresa::create([
                    'id_tipodocumento' => $request->id_tipodocumento,
                    'numero_documento' => $request->numero_documento,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'razon_social' => $request->razon_social
                ]);
                $id_empresa = $empresa->id_empresa;
            }
            $cliente = Cliente::create([
                'id_empresa' => $id_empresa,
                'id_persona' => $id_persona,
                'estado' => $request->estado,
                'tipo' => $request->tipo

            ]);
            DB::commit();
            return response()->json(['message' => 'Cliente creado correctamente', 'data' => $cliente], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear cliente: '.$e->getMessage()], 500);
        }
    }

    public function show($id_cliente)
    {
        $cliente = Cliente::with([
                'empresa:id_empresa,id_tipodocumento,numero_documento,razon_social,correo,telefono,direccion',
                'empresa.tipoDocumento:id_tipodocumento,nombre,siglas',
                'persona:id_persona,nombre,apellido_paterno,apellido_materno,nombre_completo,id_tipodocumento,numero_documento,correo,telefono,direccion,fecha_nacimiento',
                'persona.tipoDocumento:id_tipodocumento,nombre,siglas'
            ])
            ->leftJoin('persona', 'cliente.id_persona', 'persona.id_persona')
            ->leftJoin('empresa', 'cliente.id_empresa', 'empresa.id_empresa')
            ->select(
                'cliente.id_cliente',
                'cliente.id_persona',
                'cliente.id_empresa',
                'cliente.tipo',
                'cliente.estado'
            )
            ->where('cliente.id_cliente', $id_cliente)
            ->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        return response()->json($cliente);
    }

    public function update(Request $request, $id_cliente)
    {
        $validator = Validator::make($request->all(), [
            'id_tipodocumento'  => 'required',
            'numero_documento'  => 'required|string|max:20',
            'nombre'            => 'nullable|string|max:150',
            'apellido_paterno'  => 'nullable|string|max:150',
            'apellido_materno'  => 'nullable|string|max:150',
            'correo'            => 'nullable|email|max:255',
            'telefono'          => 'nullable|string|max:20',
            'direccion'         => 'nullable|string|max:255',
            'sexo'              => 'nullable|string|max:1',
            'fecha_nacimiento'  => 'nullable|date',
            'razon_social'      => 'nullable|string|max:255',
            'tipo'              => 'required|string|max:1',
            'estado'            => 'required|string|max:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente = Cliente::find($id_cliente);
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        if($cliente->tipo == 'N') {
            $persona = Persona::where('id_tipodocumento',$request->id_tipodocumento)
                ->where('numero_documento',$request->numero_documento)
                ->where('id_persona','!=',$cliente->id_persona)
                ->first();
            if ($persona) {
                return response()->json(['errors' => 'Error: El número de documento esta registrado.'], 422);
            }
        } else if($cliente->tipo == 'J') {
            $empresa = Empresa::where('id_tipodocumento',$request->id_tipodocumento)
                ->where('numero_documento',$request->numero_documento)
                ->where('id_empresa','!=',$cliente->id_empresa)
                ->first();
            if ($empresa) {
                return response()->json(['errors' => 'Error: El número de documento esta registrado.'], 422);
            }
        }

        if($cliente->tipo == 'N') {
            $persona = Persona::find($cliente->id_persona);
            if (!$persona) {
                return response()->json(['errors' => 'Error: Persona no encontrada.'], 422);
            }
        } else if($cliente->tipo == 'J') {
            $empresa = Empresa::find($cliente->id_empresa);
            if (!$empresa) {
                return response()->json(['errors' => 'Error: Empresa no encontrada.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            if($cliente->tipo == 'N') {
                $nombreCompleto = implode(' ', array_filter([
                    $request->nombre,
                    $request->apellido_paterno,
                    $request->apellido_materno
                ]));
                $persona->update([
                    'id_tipodocumento' => $request->id_tipodocumento,
                    'numero_documento' => $request->numero_documento,
                    'nombre' => $request->nombre,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'correo' => $request->correo,
                    'nombre_completo' => $nombreCompleto,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'sexo' => $request->sexo,
                    'fecha_nacimiento' => $request->fecha_nacimiento
                ]);
            } else if($cliente->tipo == 'J') {
                $empresa->update([
                    'id_tipodocumento' => $request->id_tipodocumento,
                    'numero_documento' => $request->numero_documento,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'razon_social' => $request->razon_social
                ]);
            }

            $cliente->update([
                'estado' => $request->estado,
            ]);

            DB::commit();
            return response()->json(['message' => 'Cliente actualizado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar cliente: '.$e->getMessage()], 500);
        }
    }

    public function destroy($id_cliente)
    {
        $cliente = Cliente::find($id_cliente);
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        if($cliente->tipo == 'N') {
            $persona = Persona::find($cliente->id_persona);
            if (!$persona) {
                return response()->json(['errors' => 'Error: Persona no encontrada.'], 422);
            }
        } else if($cliente->tipo == 'J') {
            $empresa = Empresa::find($cliente->id_empresa);
            if (!$empresa) {
                return response()->json(['errors' => 'Error: Empresa no encontrada.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            if($cliente->tipo == 'N') {
                $persona->delete();
            } else if($cliente->tipo == 'J') {
                $empresa->delete();
            }
            $cliente->delete();

            DB::commit();
            return response()->json(['message' => 'Cliente desactivado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al desactivar cliente: '.$e->getMessage()], 500);
        }
    }
}
