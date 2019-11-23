<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // App
        Schema::create('apps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pid')->index()->default(0)->comment = '父级ID';
            $table->string('path')->index()->comment = 'Tree Path';
            $table->string('name', 50)->comment = 'App 名字';
            $table->string('key', 50)->nullable()->comment = 'APP KEY';
            $table->string('package_name', 100)->nullable()->comment = '包名';
            $table->unsignedInteger('app_status')->index()->default(0)->comment = '状态';
            $table->unsignedInteger('channel')->index()->default(0)->comment = '第三方SDK通道，比如tencent_ysdk';
            $table->json('sdk_params')->nullable()->comment = 'SDK的参数';
            $table->string('cp_callback', 255)->nullable()->comment = 'CP支付回调';
            $table->decimal('channel_rate', 7, 5)->default('0')->comment = '硬核通道费比例';
            $table->decimal('cp_rate', 7, 5)->default('0')->comment = 'CP比例';
            $table->decimal('cpa', 7, 2)->default('0')->comment = 'CPA费用';
            $table->timestamps();
            $table->softDeletes(); //软删除

            $table->index('created_at');
            $table->index('deleted_at');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('apps');
    }
}
