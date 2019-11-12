<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeGame extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::transaction(function() {
            \Illuminate\Database\Eloquent\Model::unguard(true);
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $fields = [
                'channel|渠道' => [
                    'sdk|Times SDK' => [],
                    'ysdk|应用宝' => [],
                    'vivo|VIVO' => [],
                    'oppo|OPPO' => [],
                    'mi|小米' => [],
                    'huawei|华为' => [],
                    'facebook|FaceBook' => [],
                    'google|Google' => [],
                ],
                'payment_type|支付方式' => [
                    'vc|代金券' => [],
                    'wechat|微信' => [],
                    'alipay|支付宝' => [],
                    'ysdk|应用宝' => [],
                    'vivo|VIVO' => [],
                    'oppo|OPPO' => [],
                    'mi|小米' => [],
                    'huawei|华为' => [],
                    'facebook|FaceBook' => [],
                    'google|Google' => [],
                ],
            ];

            \App\Catalog::import($fields, \App\Catalog::findByName('fields'));

            $status = [
                'game_status|游戏状态' => [
                    'enabled|启用' => [],
                    'disabled|禁用' => [],
                ],
                'game_order_status|游戏订单状态' => [
                    'pedding|订单创建' => [],
                    'paid_success|支付成功' => [],
                    'success|发货成功' => [],
                    'fail|发货失败' => [],
                    'not_matched|金额不匹配' => [],
                ],
            ];

            \App\Catalog::import($status, \App\Catalog::findByName('status'));

            \Illuminate\Database\Eloquent\Model::unguard(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
