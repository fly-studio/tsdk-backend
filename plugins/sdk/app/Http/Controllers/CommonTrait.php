<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Illuminate\Http\Request;
use Addons\Core\Exceptions\OutputResponseException;

use App\AppLaunch;
use App\Repositories\AppRepository;
use App\Repositories\AppLaunchRepository;

trait CommonTrait {

	protected function censorAppLaunch(Request $request)
	{
		$alid = $request->query('alid');

		if (empty($alid))
			$this->throwException($request, 'sdk::common.lose_appLaunch', 4001);

		$appLaunch = (new AppLaunchRepository)->find($alid);

		if (empty($appLaunch))
			$this->throwException($request, 'sdk::common.lose_appLaunch', 4002);

		if (empty($appLaunch->app))
			$this->throwException($request, 'sdk::common.lose_app', 4003);

		return $appLaunch;
	}

	protected function censorApp(int $app_id)
	{
		$app = (new AppRepository)->find($app_id);

		if (empty($app))
			$this->throwException($request, 'sdk::common.lose_app', 4003);

		return $app;
	}

	protected function censorProperty(Request $request)
	{
		$data = $this->censor($request, 'sdk::common.fields', ['property']);

		return $data['property'];
	}

	private function throwException(Request $request, string $message, int $code)
	{
		throw (new OutputResponseException($message))->request($request)->code($code);
	}
}
