<?php

namespace App;

use App\Model;

class AppDailyStat extends Model {

	protected $guarded = ['id'];
	protected $dates = ['at'];

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

}
