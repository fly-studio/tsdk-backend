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

        // App+用户关系表
        Schema::create('app_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->comment = 'apps id';
            $table->unsignedBigInteger('uid')->index()->comment = 'users id';
            $table->string('cp_user_id', 100)->nullable()->index()->comment = 'CP用户ID';

            $table->timestamps();

            $table->unique(['aid', 'uid']);
            $table->index('created_at');
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');

        });

        \DB::statement('ALTER TABLE `app_users` ADD `cp_user_binary` VARBINARY(16) after `cp_user_id`;');
        \DB::statement('ALTER TABLE `app_users` ADD INDEX(`cp_user_binary`);');

        // 冗余表：APP启动表，用于计算激活数据
        // Token字段贯穿Application的生命周期
        Schema::create('app_launches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->comment = 'apps id';
            $table->unsignedBigInteger('did')->index()->comment = '设备ID';
            $table->string('token', 128)->comment = '伴随APP生命周期的token';
            $table->timestamp('expired_at')->comment = 'TOKEN过期时间';
            $table->timestamps();

            $table->index('created_at');

            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('did')->references('id')->on('devices')->onDelete('cascade');

        });

        /**
         * 冗余表：用户登录表，用于计算活跃、新增
         */
        Schema::create('app_user_logins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->comment = 'apps ID(冗余)';
            $table->unsignedBigInteger('auid')->index()->comment = 'app_users UID';
            $table->unsignedBigInteger('alid')->index()->comment = 'app_launches ID';

            $table->timestamps();

            $table->index('created_at');
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('auid')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('alid')->references('id')->on('app_launches')->onDelete('cascade');
        });

        /**
         * 订单表
         */
        Schema::create('app_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no', 50)->index()->comment = '订单号';
            $table->unsignedInteger('aid')->index()->default(0)->comment = 'apps id(冗余)';
            $table->unsignedBigInteger('auid')->index()->default(0)->comment = 'apps id(冗余)';
            $table->unsignedBigInteger('alid')->index()->default(0)->comment = 'app_launches id';
            $table->string('item_name', 100)->nullable()->comment = '商品名称';
            $table->decimal('amount', 10, 2)->index()->default('0')->comment = '商品价格';
            $table->string('cp_order_no', 100)->nullable()->comment = 'CP订单号';
            $table->string('payment_no', 100)->nullable()->comment = '支付通道订单号';
            $table->unsignedInteger('payment_type')->index()->default(0)->comment = '支付通道类型';
            $table->unsignedInteger('app_order_status')->index()->default(0)->comment = '状态';
            $table->timestamp('paid_at')->index()->nullable()->comment = '支付时间';
            $table->timestamp('cp_callbacked_at')->index()->nullable()->comment = '请求支付回调时间';
            $table->timestamp('success_at')->index()->nullable()->comment = '支付回调成功时间';
            $table->text('cp_callback_response')->nullable()->comment = '回调Response';
            $table->json('order_params')->nullable()->comment = '其它透传参数';

            $table->timestamps();
            $table->softDeletes(); //软删除

            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');

            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('auid')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('alid')->references('id')->on('app_launches')->onDelete('cascade');
        });

        Schema::create('app_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('at')->index()->comment = '日期';
            $table->unsignedInteger('aid')->index()->default(0)->comment = 'apps ID(冗余)';

            $table->unsignedInteger('devices')->index()->default(0)->comment = '激活(设备去重)';
            $table->unsignedInteger('new_devices')->index()->default(0)->comment = '激活新增';

            $table->unsignedInteger('logins')->index()->default(0)->comment = '活跃(登录去重)';
            $table->unsignedInteger('new_logins')->index()->default(0)->comment = '登录新增';

            $table->decimal('amount', 10, 2)->index()->default('0')->comment = '付费总额';
            $table->unsignedInteger('paid_count')->index()->default('0')->comment = '付费人数(用户去重)';
            $table->unsignedInteger('order_count')->index()->default('0')->comment = '订单总数';
            $table->decimal('paid_arup', 10, 2)->index()->default('0')->comment = '付费ARUP';
            $table->decimal('active_arup', 10, 2)->index()->default('0')->comment = '活跃ARUP';

            $table->timestamps();

            $table->unique(['at', 'aid']);
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
        });

        Schema::create('app_daily_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('at')->index()->comment = '日期';
            $table->unsignedInteger('days')->index()->default(0)->comment = '第几日';
            $table->unsignedInteger('aid')->index()->default(0)->comment = 'apps ID(冗余)';

            $table->unsignedInteger('retain')->index()->default(0)->comment = '留存';
            $table->decimal('ltv', 10, 2)->index()->default('0')->comment = 'LTV';

            $table->timestamps();

            $table->unique(['at', 'days', 'aid']);
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
        });

        Schema::create('app_events', function(Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('event_type')->index()->default(0)->comment = '事件类型';
            $table->unsignedInteger('aid')->index()->default(0)->comment = 'apps ID';
            $table->unsignedBigInteger('did')->index()->comment = '设备ID';
            $table->unsignedBigInteger('auid')->index()->nullable()->comment = 'app_users id（部分事件为空）';
            $table->nullableMorphs("from");
            $table->json('value')->nullable()->comment = '上报的正文';
            $table->string('carrier', 20)->nullable()->comment = '运营商';
            $table->string('connection', 20)->nullable()->comment = 'Wifi/4G/3G/2G/None';
            $table->unsignedBigInteger('app_version_code')->nullable()->comment = 'APP version code';
            $table->string('app_version', 20)->nullable()->comment = 'APP version';
            $table->string('sdk_version', 20)->nullable()->comment = 'SDK version';
            $table->string('geometry')->nullable()->comment = 'SDK version';
            $table->datetime('device_at')->default(0)->comment = '设备时间W3C格式';
            $table->ipAddress('ip')->nullable()->comment = '登录IP';

            $table->timestamps();

            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('auid')->references('id')->on('app_users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_events');
        Schema::dropIfExists('app_daily_stats');
        Schema::dropIfExists('app_stats');
        Schema::dropIfExists('app_orders');
        Schema::dropIfExists('app_user_logins');
        Schema::dropIfExists('app_users');
        Schema::dropIfExists('app_launches');
        Schema::dropIfExists('apps');
    }
}
