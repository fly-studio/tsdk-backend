<?php

namespace Plugins\Sdk\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Addons\Core\Tools\OutputEncrypt;
use Addons\Core\Http\Middleware\EncryptBody;
use Plugins\Sdk\App\Http\Controllers\CensorTrait;
use Addons\Core\Http\Output\Response\TextResponse;

class EncryptBodyByAppLaunch extends EncryptBody {

	use CensorTrait;

	public function handle($request, Closure $next, string $rsaKey = null)
	{
		$appLaunch = $this->censorAppLaunch($request);

		$request->route()->setParameter('appLaunch', $appLaunch);

		return parent::handle($request, $next, $appLaunch->private_key);
	}
}
