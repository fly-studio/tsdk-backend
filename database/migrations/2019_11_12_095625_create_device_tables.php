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
            $table->string('device_id', 64)->unique()->comment = '全局唯一码，不限于IMEI，UDID或安全联盟的OAID';
            $table->string('imei', 64)->nullable()->index()->comment = '国际移动设备识别码';
            $table->string('udid', 64)->nullable()->index()->comment = '设备唯一标识符';
            $table->string('device', 100)->index()->nullable()->comment = '设备，比如：iPhone, Nexus, AsusTablet';
            $table->string('platform', '20')->index()->nullable()->comment = '平台，比如：Ubuntu, Windows, OS X,';
            $table->string('os', '20')->index()->nullable()->comment = '系统';
            $table->string('os_version', '20')->index()->nullable()->comment = '系统';

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
        Schema::dropIfExists('devices');
    }
}
