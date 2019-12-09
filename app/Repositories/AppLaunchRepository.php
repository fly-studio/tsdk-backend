<?php

namespace App\Repositories;

use DB;
use Carbon\Carbon;
use phpseclib\Crypt\RSA;
use Illuminate\Http\Request;
use Addons\Core\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;

use App\AppLaunch;

class AppLaunchRepository extends Repository {

	public function prePage()
	{
		return config('size.models.'.(new AppLaunch)->getTable(), config('size.common'));
	}

	public function find($id, array $columns = ['*'])
	{
		return AppLaunch::with(['app_device', 'app'])->find($id, $columns);
	}

	public function findOrFail($id, array $columns = ['*'])
	{
		return AppLaunch::with(['app_device', 'app'])->findOrFail($id, $columns);
	}

	public function launch(int $aid, int $did, int $sub_channel)
	{
		$rsa = new RSA();
		$rsaKeys = $rsa->createKey(1024);
		$public_key = $rsaKeys['publickey'];
		$private_key = $rsaKeys['privatekey'];
		$expired_at = Carbon::now()->addDays(7);

		return $this->store(compact('aid', 'did', 'sub_channel', 'public_key', 'private_key', 'expired_at'));
	}

	public function store(array $data)
	{
		return DB::transaction(function() use ($data) {
			$model = AppLaunch::create($data);
			return $model;
		});
	}

	public function update(Model $model, array $data)
	{
		return DB::transaction(function() use ($model, $data){
			$model->update($data);
			return $model;
		});
	}

	public function destroy(array $ids)
	{
		DB::transaction(function() use ($ids) {
			AppLaunch::destroy($ids);
		});
	}

	public function data(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new AppLaunch;
		$builder = $model->newQuery()->with([]);

		$total = $this->_getCount($request, $builder, false);
		$data = $this->_getData($request, $builder, $callback, $columns);
		$data['recordsTotal'] = $total; //不带 f q 条件的总数
		$data['recordsFiltered'] = $data['total']; //带 f q 条件的总数

		return $data;
	}

	public function export(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new AppLaunch;
		$builder = $model->newQuery()->with([]);
		$size = $request->input('size') ?: config('size.export', 1000);

		$data = $this->_getExport($request, $builder, $callback, $columns);

		return $data;
	}

}
