<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('plan_trabajos', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->integer('mes');
			$table->integer('anio');
			$table->integer('horas_requeridas');
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
			$table->unique(['user_id', 'mes', 'anio']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('plan_trabajos');
	}
};
