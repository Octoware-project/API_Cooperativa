<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('configuracion_horas', function (Blueprint $table) {
            $table->index(['activo', 'created_at'], 'idx_activo_created_at');
        });
    }


    public function down(): void
    {
        Schema::table('configuracion_horas', function (Blueprint $table) {
            $table->dropIndex('idx_activo_created_at');
        });
    }
};
