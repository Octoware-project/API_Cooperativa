<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('Horas_Mensuales', function (Blueprint $table) {
            $table->string("mail");
            $table->integer("ID_Registro_Horas")->unique();
            $table->string("Semana");
            $table->String("Cantidad_Horas");
            $table->string("Motivo_Falla")->nullable();
            $table->string("Tipo_Justificacion")->nullable();
            $table->integer("Monto_Compensario");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Horas_Mensuales');
    }
};
