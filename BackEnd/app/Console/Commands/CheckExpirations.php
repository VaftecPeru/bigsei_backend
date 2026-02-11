<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckExpirations extends Command
{
    /**
     * Bug 7: Tarea automática que desactiva membresías y licencias vencidas.
     */
    protected $signature = 'app:check-expirations';
    protected $description = 'Revisa y desactiva membresías y licencias vencidas';

    public function handle(): int
    {
        // Desactivar membresías vencidas
        $membresiasDesactivadas = DB::table('membresia')
            ->where('estado', '1')
            ->where('fecha_fin', '<', now())
            ->update(['estado' => '0']);

        // Desactivar licencias vencidas
        $licenciasDesactivadas = DB::table('licencia')
            ->where('estado', '1')
            ->where('fecha_fin', '<', now())
            ->update(['estado' => '0']);

        $mensaje = "CheckExpirations: {$membresiasDesactivadas} membresías y {$licenciasDesactivadas} licencias desactivadas.";

        Log::info($mensaje);
        $this->info($mensaje);

        return Command::SUCCESS;
    }
}
