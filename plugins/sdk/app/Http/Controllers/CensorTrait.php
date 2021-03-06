<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Plugins\Attachment\App\Tools\Helpers;
use Addons\Core\Http\Output\ResponseFactory;
use Addons\Core\Exceptions\OutputResponseException;

use App\AppLaunch;
use App\Repositories\AppRepository;
use App\Repositories\UserRepository;
use App\Repositories\AppUserRepository;
use App\Repositories\AppOrderRepository;
use App\Repositories\AppLaunchRepository;

trait CensorTrait {

	protected $appLaunch;
	protected $user;
	protected $appUser;
	private static $cipher;

	protected function censorAppLaunch(Request $request)
	{
		$alid = Helpers::decode($request->route('appLaunch'));

		if (empty($alid))
			$this->throwCensorException($request, 'sdk::app.lose_appLaunch', 4001);

		$appLaunch = (new AppLaunchRepository)->find($alid);

		if (empty($appLaunch))
			$this->throwCensorException($request, 'sdk::app.lose_appLaunch', 4002);

		if ($appLaunch->expired_at->lt(Carbon::now()))
			$this->throwCensorException($request, 'sdk::app.appLaunch_expired', 4003);

		if (empty($appLaunch->app))
			$this->throwCensorException($request, 'sdk::app.lose_app', 4004);

		return $appLaunch;
	}

	protected function censorApp(Request $request, int $app_id)
	{
		$app = (new AppRepository)->find($app_id);

		if (empty($app))
			$this->throwCensorException($request, 'sdk::app.lose_app', 4004);

		if (empty($app->app_status) || $app->app_status->name != 'enabled')
			$this->throwCensorException($request, 'sdk::app.app_disabled', 4005);

		return $app;
	}

	protected function censorDevice(Request $request)
	{
		if (!is_array($request->device))
			$this->throwCensorException($request, 'sdk::app.lose_device', 4006);

		$data = $this->censor($request->device, 'sdk::device.fields', ['*']);

		return $data;
	}

	protected function censorProperty(Request $request)
	{
		if (!is_array($request->property))
			$this->throwCensorException($request, 'sdk::app.lose_device', 4007);

		$data = $this->censor($request->property, 'sdk::property.fields', ['*']);

		return $data;
	}

	protected function censorUser(Request $request)
	{
		$uid = $request->uid;
		$auid = $request->auid;

		if (empty($uid) || empty($auid))
			$this->throwCensorException($request, 'sdk::user.empty_user', 4010);

		$this->user = $user = (new UserRepository)->find($uid);
		$this->appUser = $appUser = (new AppUserRepository)->find($auid);

		if (empty($user) || empty($appUser))
			$this->throwCensorException($request, 'sdk::user.empty_user', 4011);

		if ($user->getKey() != $appUser->uid)
			$this->throwCensorException($request, 'sdk::user.appUser_bind_invalid', 4012);

		return [$user, $appUser];
	}

	protected function censorOrder(Request $request)
	{
		$oid = $request->oid;

		if (empty($oid))
			$this->throwCensorException($request, 'sdk::purchase.empty_orderr', 4020);

		$order = (new AppOrderRepository)->find($oid);

		if (empty($order))
			$this->throwCensorException($request, 'sdk::purchase.empty_orderr', 4021);

		return $order;
	}

	private function throwCensorException(Request $request, string $message, int $code)
	{
		throw (new OutputResponseException($message))
			->request($request)
			->code($code);
	}

}
