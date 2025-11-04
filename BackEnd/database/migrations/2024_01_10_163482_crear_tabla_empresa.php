<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id('id_empresa'); 
            $table->string('id_tipodocumento');
            $table->string('numero_documento');
            $table->string('razon_social');
            $table->string('condicion_sunat');
            $table->string('estado_contribuyente');
            $table->string('tipo_relacion');
            $table->string('id_vendedor');
            $table->string('correo');
            $table->string('contacto');
            $table->string('direccion_fiscal');
            $table->string('telefono');
            $table->string('departamento');
            $table->string('provincia');
            $table->string('distrito');
            $table->string('direccion');
            $table->time('atencion_desde');
            $table->time('atencion_hasta');
            $table->string('atencion_dias');
            $table->string('url_maps');
        });
    }

    public function down()
    {
        Schema::dropIfExists('empresa');
    }
};