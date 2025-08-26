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
        Schema::create('Pago_Mensual', function (Blueprint $table) {
            $table->string("mail");
            $table->unsignedBigInteger("ID_Pago_Mensual")->unique();
            $table->string("Mes")->nullable();
            $table->decimal("Monto");
            $table->binary("Archivo_Comprobante")->nullable();
            $table->date("Fecha_Subida");
            $table->binary("Estado_Pago")->nullable();
            $table->binary("Comprobante_Inicial")->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Pago_Mensual');
    }
};