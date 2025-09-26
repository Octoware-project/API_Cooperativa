<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_horas', function (Blueprint $table) {
            $table->id();
            $table->decimal('valor_por_hora', 10, 2)->default(0)->comment('Valor en pesos por hora');
            $table->boolean('activo')->default(true)->comment('Configuración activa');
            $table->text('observaciones')->nullable()->comment('Notas sobre el cambio de valor');
            $table->timestamps();
            
            // Índice para búsquedas rápidas
            $table->index(['activo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_horas');
    }
};