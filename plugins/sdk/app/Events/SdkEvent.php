<?php

namespace Plugins\Sdk\App\Events;

use Illuminate\Database\Eloquent\Model;

use App\AppUser;
use App\AppLaunch;
use App\Repositories\AppEventRepository;

class SdkEvent {

	protected $appLaunch = null;
	protected $property = null;
	protected $appUser = null;
	protected $from = null;
	protected $value = null;

	public function __construct(AppLaunch $appLaunch, array $property)
	{
		$this->appLaunch = $appLaunch;
		$this->property = $property;
	}

	public static function create(AppLaunch $appLaunch, array $property)
	{
		return new SdkEvent();
	}

	public function appUser(?AppUser $value)
	{
		$this->appUser = $value;
		return $this;
	}

	public function from(?Model $value)
	{
		$this->from = $value;
		return $this;
	}

	public function value(?array $value)
	{
		$this->value = $value;
		return $this;
	}

	public function handle(string $event_type)
	{
		$type = catalog_search('fields.event_type.'.$event_type);

		if (empty($type))
			throw new \Exception('Invalid event_type: '.$event_type);

		$appEvent = new AppEventRepository;
		return $appEvent->handle($type, $this->appLaunch, $this->property, $this->value, $this->appUser, $this->from);
	}
}
