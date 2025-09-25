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
        Schema::create('Factura', function (Blueprint $table) {
            $table->id();
            $table->string("email");
            $table->decimal("Monto");
            $table->string("Archivo_Comprobante")->nullable();
            $table->string("Estado_Pago")->nullable();
            $table->string("tipo_pago")->nullable();
            $table->string('fecha_pago')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Factura');
    }
};