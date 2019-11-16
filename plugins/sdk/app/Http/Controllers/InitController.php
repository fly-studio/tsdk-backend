<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Repositories\DeviceRepository;

class InitController extends Controller
{
	public function index(Request $request, DeviceRepository $deviceRepo)
	{
		$data = $this->censor($request, 'sdk::device.fields', ['game_id', 'app_id', 'imei', 'udid', 'device', 'platform', 'os', 'os_version']);

		$device = $deviceRepo->createOrUpdate($data['imei'] ?: $data['udid'], Arr::except($data, ['game_id', 'app_id']));

		return $this->api();
	}
}
