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
            $table->unsignedInteger('app_status')->index()->default(0)->comment = '状态';
            $table->unsignedInteger('channel')->index()->default(0)->comment = '硬核通道';
            $table->string('app_id', 50)->index()->comment = '对外的Appid';
            $table->string('app_key', 50)->comment = 'AppKey';
            $table->json('app_params')->nullable()->comment = '其它参数';
            $table->string('cp_callback', 255)->nullable()->comment = 'CP支付回调';
            $table->decimal('channel_rate', 7, 5)->default('0')->comment = '硬核通道费比例';
            $table->decimal('cp_rate', 7, 5)->default('0')->comment = 'CP比例';
            $table->decimal('cpa', 7, 2)->default('0')->comment = 'CPA费用';
            $table->timestamps();
            $table->softDeletes(); //软删除

            $table->index('created_at');
            $table->index('deleted_at');
        });

        // APP启动表，Application onCreate的时候会来拉取
        // Token贯穿Application的生命周期
        Schema::create('app_launches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->default(0)->comment = 'apps id';
            $table->unsignedBigInteger('did')->index()->default(0)->comment = '设备ID';
            $table->string('token', 100)->comment = '伴随APP生命周期的token';
            $table->timestamps();

            $table->index('created_at');

            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('did')->references('id')->on('devices')->onDelete('cascade');

        });

        // App 用户
        Schema::create('app_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->default(0)->comment = 'apps id';
            $table->unsignedInteger('uid')->index()->default(0)->comment = 'users id';
            $table->string('cp_user_id', 100)->index()->comment = 'CP用户ID';

            $table->timestamps();

            $table->unique(['aid', 'uid']);
            $table->index('created_at');
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');

        });

        \DB::statement('ALTER TABLE `app_users` ADD `cp_user_binary` VARBINARY(16) after `cp_user_id`;');
        \DB::statement('ALTER TABLE `app_users` ADD INDEX(`cp_user_binary`);');

        Schema::create('app_user_logins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->default(0)->comment = 'apps ID(冗余)';
            $table->unsignedBigInteger('auid')->index()->default(0)->comment = 'app_users UID';
            $table->unsignedBigInteger('alid')->index()->default(0)->comment = 'app_launches ID';
            $table->unsignedInteger('online')->index()->default(0)->comment = '在线时长(秒)';
            $table->timestamps();

            $table->index('created_at');
            $table->foreign('alid')->references('id')->on('app_launches')->onDelete('cascade');
        });

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

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_daily_stats');
        Schema::dropIfExists('app_stats');
        Schema::dropIfExists('app_orders');
        Schema::dropIfExists('app_user_logins');
        Schema::dropIfExists('app_users');
        Schema::dropIfExists('app_launches');
        Schema::dropIfExists('apps');
    }
}
