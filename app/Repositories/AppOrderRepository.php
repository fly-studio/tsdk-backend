<?php

namespace App\Repositories;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Addons\Core\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;

use App\AppUser;
use App\AppOrder;
use App\AppLaunch;

class AppOrderRepository extends Repository {

	public function prePage()
	{
		return config('size.models.'.(new AppOrder)->getTable(), config('size.common'));
	}

	public function find($id, array $columns = ['*'])
	{
		return AppOrder::with(['app_device', 'app'])->find($id, $columns);
	}

	public function findOrFail($id, array $columns = ['*'])
	{
		return AppOrder::with(['app_device', 'app'])->findOrFail($id, $columns);
	}

	public function order(AppLaunch $appLaunch, AppUser $appUser, array $data)
	{
		return DB::transaction(function() use ($appLaunch, $appUser, $data) {

			$order = AppOrder::create([
				'auid' => $appUser->getKey(),
				'alid' => $appLaunch->getKey(),
				'aid' => $appLaunch->aid,
				'adid' => $appLaunch->adid,

				'order_no' => '',
				'item_name' => $data['item_name'],
				'amount' => $data['amount'],
				'cp_order_no' => $data['cp_order_no'],
				'order_params' => $data['order_params'],
				'app_order_status' => catalog_search('status.app_order_status.pedding', 'id'),
			]);

			$todayCount = AppOrder::where($order->getKeyName(), '<=', $order->getKey())->where('created_at', '>=', Carbon::now()->startOfDay())->count();
			$order->order_no = $order->created_at->toString('YmdHis').str_pad($todayCount, 7, '0', STR_PAD_LEFT);
			$order->save();

			return $order;
		});
	}

	public function store(array $data)
	{
		return DB::transaction(function() use ($data){
			$model = AppOrder::create($data);
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
			AppOrder::destroy($ids);
		});
	}

	public function data(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new AppOrder;
		$builder = $model->newQuery()->with([]);

		$total = $this->_getCount($request, $builder, false);
		$data = $this->_getData($request, $builder, $callback, $columns);
		$data['recordsTotal'] = $total; //不带 f q 条件的总数
		$data['recordsFiltered'] = $data['total']; //带 f q 条件的总数

		return $data;
	}

	public function export(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new AppOrder;
		$builder = $model->newQuery()->with([]);
		$size = $request->input('size') ?: config('size.export', 1000);

		$data = $this->_getExport($request, $builder, $callback, $columns);

		return $data;
	}

}
