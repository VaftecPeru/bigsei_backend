<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('idUsuario');
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->string('dni', 8);
            $table->string('nombres', 60);
            $table->string('apellidoPaterno', 40);
            $table->string('apellidoMaterno', 40);
            $table->date('fechaNacimiento');
            $table->string('genero', 10);
            $table->string('telefono', 9);
            $table->string('correo', 50)->unique();
            $table->string('direccion', 60);
            $table->string('foto', 200)->nullable();
            $table->string('username', 60)->unique()->nullable();
            $table->string('password');
            $table->string('estado', 10)->default('activo');
            $table->unsignedBigInteger('id_usuarioreg')->nullable();
            $table->unsignedBigInteger('id_usuariomod')->nullable();
            $table->timestamp('fechareg')->useCurrent();
            $table->timestamp('fechamod')->nullable();
            $table->string('google_id')->nullable()->unique();

            $table->foreign('id_empresa')
                  ->references('id_empresa')
                  ->on('empresa')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['id_empresa']);
        });
        
        Schema::dropIfExists('usuarios');
    }
};