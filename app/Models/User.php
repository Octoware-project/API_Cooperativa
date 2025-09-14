<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
	use HasFactory, Notifiable;

	public function planTrabajos()
	{
		return $this->hasMany(PlanTrabajo::class);
	}

	public function horasMensuales()
	{
		return $this->hasMany(Horas_Mensuales::class, 'email', 'email');
	}
}
