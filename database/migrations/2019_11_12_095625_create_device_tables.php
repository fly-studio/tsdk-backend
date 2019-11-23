<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->bigIncrements('id');

            //以下三个需要至少有一个
            $table->string('imei', 64)->nullable()->index()->comment = '国际移动设备识别码';
            $table->string('idfa', 64)->nullable()->index()->comment = '苹果IDFA';
            $table->string('odid', 64)->nullable()->index()->comment = '移动安全联盟的OAID';

            $table->string('android_id', 64)->nullable()->index()->comment = 'Android ID';
            $table->string('serial', 64)->nullable()->index()->comment = '手机序列号';

            $table->string('brand', 32)->nullable()->index()->comment = '品牌，比如:LG，Redmi';
            $table->string('model', 32)->index()->nullable()->comment = '设备，比如:Note 8';
            $table->string('arch', 32)->nullable()->comment = '平台，比如armeabi-v7a_armeabi';
            $table->string('os', '20')->index()->nullable()->comment = '系统，比如：Android';
            $table->string('os_version', '20')->index()->nullable()->comment = '系统版本 比如：8.0.0';
            $table->macAddress('mac', 32)->nullable()->comment = 'Wifi MAC';
            $table->macAddress('bluetooth', 32)->nullable()->comment = 'Wifi MAC';
            $table->string('metrics', 15)->index()->nullable()->comment = '屏幕尺寸，比如：720x1280';
            $table->tinyInt('is_rooted')->index()->default(0)->comment = '是否Root或越狱';
            $table->tinyInt('is_simulator')->index()->default(0)->comment = '是否模拟器';

            $table->timestamp('last_at')->nullable()->index()->comment = '最后活动时间';

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
