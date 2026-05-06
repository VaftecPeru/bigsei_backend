<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('factura', function (Blueprint $table) {
            $table->id('idFactura');

            $table->unsignedBigInteger('idPago');

            $table->string('numeroFactura');
            $table->string('cliente');
            $table->string('documento')->nullable();

            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);

            $table->string('estado'); // ACEPTADA
            $table->timestamp('fecha')->useCurrent();

            $table->foreign('idPago')
                ->references('idPago')
                ->on('pago')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura');
    }
};
