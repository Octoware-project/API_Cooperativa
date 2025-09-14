<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('horas_mensuales', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->integer('anio');
            $table->integer('mes');
            $table->integer('dia');
            $table->integer('Cantidad_Horas')->nullable();
            $table->string('Motivo_Falla')->nullable();
            $table->string('Tipo_Justificacion')->nullable();
            $table->float('Monto_Compensario', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('horas_mensuales');
    }
};
