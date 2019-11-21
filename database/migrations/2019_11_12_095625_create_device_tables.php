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
            $table->string('uuid', 64)->unique()->comment = '本站全局唯一码';

            $table->string('imei', 64)->nullable()->index()->comment = '国际移动设备识别码(不一定能获取)';
            $table->string('udid', 64)->nullable()->index()->comment = '设备唯一标识符(不一定能获取)';
            $table->string('idfa', 64)->nullable()->index()->comment = '苹果IDFA';
            $table->string('android_id', 64)->nullable()->index()->comment = 'Android ID';
            $table->string('serial', 64)->nullable()->index()->comment = '手机序列号';

            $table->string('brand', 32)->nullable()->index()->comment = '品牌，比如:LG，Redmi';
            $table->string('model', 32)->nullable()->comment = '设备，比如:Note 8';
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

        \DB::statement('ALTER TABLE `devices` ADD `device_binary` VARBINARY(16) after `device_id`;');
        \DB::statement('ALTER TABLE `devices` ADD INDEX(`device_binary`);');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('devices');
    }
}
