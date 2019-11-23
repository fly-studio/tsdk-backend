<?php

namespace App;

use App\Model;

class AppDevice extends Model
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

AppDevice::creating(function($model) {
	$model->uuid_binary = hex2bin($model->uuid);
});
