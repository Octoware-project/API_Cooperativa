<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
	use HasFactory, Notifiable;



	public function persona()
	{
		return $this->hasOne(Persona::class, 'user_id', 'id');
	}

	public function planTrabajos()
	{
		return $this->hasMany(PlanTrabajo::class);
	}

	public function horasMensuales()
	{
		return $this->hasMany(Horas_Mensuales::class, 'email', 'email');
	}

	protected $fillable = [
		'name',
		'email',
		'password',
	];

	protected $hidden = [
		'password',
		'remember_token',
	];

	protected $casts = [
		'email_verified_at' => 'datetime',
	];
}
