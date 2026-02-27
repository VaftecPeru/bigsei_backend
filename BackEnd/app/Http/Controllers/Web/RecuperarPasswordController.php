<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RecuperarPasswordController extends Controller
{
    /**
     * POST /api/web/recuperar-password
     * Genera un token de recuperación y devuelve el enlace (en producción se enviaría por email).
     */
    public function solicitar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'El correo electrónico es requerido',
            'email.email'    => 'El correo no es válido',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        // Buscar usuario por email/correo
        $usuario = DB::table('persona')
            ->where('correo', $request->email)
            ->orWhere('email', $request->email)
            ->first();

        if (!$usuario) {
            // Por seguridad, devolver el mismo mensaje aunque no exista
            return response()->json([
                'success' => true,
                'message' => 'Si el correo existe, recibirás las instrucciones en breve.',
            ]);
        }

        // Generar token único
        $token = Str::random(64);
        $expira = now()->addHour();

        // Guardar o actualizar token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // En producción aquí se enviaría el email con el enlace
        // Mail::to($request->email)->send(new RecuperarPasswordMail($token));

        return response()->json([
            'success' => true,
            'message' => 'Si el correo existe, recibirás las instrucciones en breve.',
            // Solo en desarrollo: devolver el token directamente
            'debug_token' => config('app.debug') ? $token : null,
        ]);
    }

    /**
     * POST /api/web/recuperar-password/resetear
     * Restablece la contraseña con el token recibido.
     */
    public function resetear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'email.required'    => 'El correo es requerido',
            'token.required'    => 'El token es requerido',
            'password.required' => 'La contraseña es requerida',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed'=> 'Las contraseñas no coinciden',
        ]);

        if ($validator->fails()) {
            return response()->json(implode(', ', $validator->messages()->all()), 400);
        }

        // Verificar token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json('¡Token inválido o expirado! Solicita un nuevo enlace.', 400);
        }

        // Verificar que el token no haya expirado (1 hora)
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        if ($createdAt->diffInMinutes(now()) > 60) {
            return response()->json('¡El enlace ha expirado! Solicita uno nuevo.', 400);
        }

        // Actualizar contraseña
        $updated = DB::table('persona')
            ->where('correo', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        if (!$updated) {
            // Intentar con campo 'email'
            DB::table('persona')
                ->where('email', $request->email)
                ->update(['password' => Hash::make($request->password)]);
        }

        // Eliminar token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.',
        ]);
    }

    /**
     * POST /api/web/recuperar-password/verificar-token
     * Verifica si un token es válido (para validar antes de mostrar el formulario).
     */
    public function verificarToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['valid' => false], 400);
        }

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json(['valid' => false]);
        }

        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        $valid = $createdAt->diffInMinutes(now()) <= 60;

        return response()->json(['valid' => $valid]);
    }
}
