<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeApp extends Migration
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
                    'times|Times SDK' => [],
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
                    'unionpay|银联' => [],
                    'ysdk|应用宝' => [],
                    'vivo|VIVO' => [],
                    'oppo|OPPO' => [],
                    'mi|小米' => [],
                    'huawei|华为' => [],
                    'facebook|FaceBook' => [],
                    'google|Google' => [],
                ],
                'event_type|APP事件' => [
                    'launch|启动APP(Application onCreate)' => [],
                    'start|App主界面启动(Activity onResume)' => [],
                    'pause|APP被暂停(Activity onPause)' => [],
                    'tick|定时上报' => [],
                    'exception|异常' => [],
                    'crash|崩溃' => [],
                    'register|注册' => [],
                    'generate_username|快速注册' => [],
                    'login|登录' => [],
                    'verify|登录校验' => [],
                    'logout|登出' => [],
                    'pay|唤起支付' => [],
                    'paid|支付完成' => [],
                    'cancel_pay|支付取消' => [],
                ],
            ];

            \App\Catalog::import($fields, \App\Catalog::findByName('fields'));

            $status = [
                'app_status|游戏状态' => [
                    'enabled|启用' => [],
                    'disabled|禁用' => [],
                ],
                'app_order_status|游戏订单状态' => [
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
