<?php

namespace App;

use App\Model;

class AppLaunch extends Model
{
	protected $dates = ['expired_at'];

	public function device()
	{
		return $this->hasOne('App\\Device', 'id', 'did');
	}

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

}
