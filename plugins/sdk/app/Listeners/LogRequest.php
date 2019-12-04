<?php

namespace Plugins\Sdk\App\Listeners;

use Addons\Core\Contracts\Events\ControllerEvent as ControllerEventContract;

class LogRequest {

	public function handle($controllerName, ControllerEventContract $event)
	{
		$request = $event->getSerializedRequest();
		$controller = $event->getControllerName();
		$method = $event->getMethod();

		logs('sdk')->debug('Request: '.print_r(compact('request', 'controller', 'method'), true));
	}
}
