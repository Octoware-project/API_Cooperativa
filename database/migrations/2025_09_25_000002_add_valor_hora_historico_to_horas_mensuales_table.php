<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horas_mensuales', function (Blueprint $table) {
            // Valor por hora que se usó al momento de crear este registro
            $table->decimal('valor_hora_al_momento', 10, 2)->nullable()
                  ->after('Monto_Compensario')
                  ->comment('Valor por hora usado para calcular horas equivalentes');
            
            // Horas equivalentes ya calculadas (para no recalcular)
            $table->decimal('horas_equivalentes_calculadas', 8, 2)->nullable()
                  ->after('valor_hora_al_momento')
                  ->comment('Total de horas reales + horas de justificación calculadas');
            
            // Índices para optimizar consultas
            $table->index(['email', 'anio', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::table('horas_mensuales', function (Blueprint $table) {
            $table->dropColumn(['valor_hora_al_momento', 'horas_equivalentes_calculadas']);
            $table->dropIndex(['email', 'anio', 'mes']);
        });
    }
};