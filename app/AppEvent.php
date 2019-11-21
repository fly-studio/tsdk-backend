<?php

namespace App;

use App\Model;

class AppEvent extends Model
{
	protected $casts = [
		'event_type' => 'catalog',
	];

	public function device()
	{
		return $this->hasOne('App\\Device', 'id', 'did');
	}

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

	public function app_user()
	{
		return $this->hasOne('App\\AppUser', 'id', 'auid');
	}

	public function table()
	{
		return $this->morphTo('from');
	}

}
