<?php

namespace App;

use App\Model;

class AppLaunch extends Model
{
	public function device()
	{
		return $this->hasOne('App\\Device', 'id', 'did');
	}

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

}
