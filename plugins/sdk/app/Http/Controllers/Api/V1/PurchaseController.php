<?php

namespace Plugins\Sdk\App\Http\Controllers\Api\V1;

use Event;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Plugins\Sdk\App\Events\SdkEvent;
use App\Http\Controllers\Controller;
use Plugins\Sdk\App\Http\Controllers\CensorTrait;

use App\AppUser;
use App\AppOrder;
use App\AppLaunch;
use App\Repositories\AppLaunchRepository;

class AppController extends Controller
{
	use CensorTrait;

	public function pay(Request $request)
	{

	}

	public function paid(Request $request)
	{

	}

	public function cancel_pay(Request $request)
	{

	}
}
