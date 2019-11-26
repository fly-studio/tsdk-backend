<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Illuminate\Http\Request;
use Addons\Core\Exceptions\OutputResponseException;

use App\AppLaunch;
use App\Repositories\AppRepository;
use App\Repositories\AppLaunchRepository;

trait CensorTrait {

	protected function censorAppLaunch(Request $request)
	{
		$alid = $request->query('alid');

		if (empty($alid))
			$this->throwCensorException($request, 'sdk::common.lose_appLaunch', 4001);

		$appLaunch = (new AppLaunchRepository)->find($alid);

		if (empty($appLaunch))
			$this->throwCensorException($request, 'sdk::common.lose_appLaunch', 4002);

		if (empty($appLaunch->app))
			$this->throwCensorException($request, 'sdk::common.lose_app', 4003);

		return $appLaunch;
	}

	protected function censorApp(Request $request, int $app_id)
	{
		$app = (new AppRepository)->find($app_id);

		if (empty($app))
			$this->throwCensorException($request, 'sdk::common.lose_app', 4003);

		return $app;
	}

	protected function censorDevice(Request $request)
	{
		if (!is_array($request->device))
			$this->throwCensorException($request, 'sdk::common.lose_device', 4004);

		$data = $this->censor($request->device, 'sdk::device.fields', ['*']);

		return $data;
	}

	protected function censorProperty(Request $request)
	{
		if (!is_array($request->property))
			$this->throwCensorException($request, 'sdk::common.lose_device', 4005);

		$data = $this->censor($request->property, 'sdk::property.fields', ['*']);

		return $data;
	}

	private function throwCensorException(Request $request, string $message, int $code)
	{
		throw (new OutputResponseException($message))->request($request)->code($code);
	}
}
