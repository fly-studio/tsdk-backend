<?php

namespace App;

use App\Model;

class Device extends Model
{
	protected $dates = ['last_at'];
	protected $appends = ['brand_model'];

	public function getBrandModelAttribute()
	{
		return $this->brand.' '.$this->model;
	}
}
