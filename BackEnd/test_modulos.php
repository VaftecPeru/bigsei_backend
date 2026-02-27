<?php
$roles = [7 => 'Contador', 8 => 'Bibliotecario', 9 => 'Tópico Médico', 10 => 'Vendedor', 11 => 'Tutor'];
$out = "";
foreach($roles as $idRol => $nombreRol) {
    $out .= "--- ROL: $nombreRol ($idRol) ---\n";
    $modulos = \Illuminate\Support\Facades\DB::table('rol_modulo as rm')
        ->join('modulo as m', 'rm.id_modulo', '=', 'm.id_modulo')
        ->where('rm.id_rol', $idRol)
        ->select('m.id_modulo', 'm.nombre', 'm.url', 'm.id_modulosup')
        ->orderByRaw('COALESCE(m.id_modulosup, m.id_modulo), m.id_modulosup IS NOT NULL, m.orden')
        ->get();
    foreach($modulos as $mod) {
        $parent = $mod->id_modulosup ? " (Sub de {$mod->id_modulosup})" : "";
        $out .= " - [{$mod->id_modulo}] {$mod->nombre}: {$mod->url}{$parent}\n";
    }
}
file_put_contents('salida_modulos.txt', $out);
echo "OK\n";
