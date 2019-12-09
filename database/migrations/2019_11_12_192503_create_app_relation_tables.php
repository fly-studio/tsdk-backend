<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppRelationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // APP Device 的关系表
        Schema::create('app_devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 32)->index()->comment = '由android生成的uuid，设备上每个应用不同，卸载重装也会改变';
            $table->unsignedInteger('aid')->index()->comment = 'apps id';
            $table->unsignedBigInteger('did')->nullable()->index()->comment = '真正的设备ID，可能为空';

            $table->timestamps();
            $table->index('created_at');
            $table->unique(['uuid', 'aid']);
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('did')->references('id')->on('devices')->onDelete('cascade');
        });

        \DB::statement('ALTER TABLE `app_devices` ADD `uuid_binary` VARBINARY(16) after `uuid`;');
        \DB::statement('ALTER TABLE `app_devices` ADD INDEX(`uuid_binary`);');

        // 冗余表：APP启动表，用于计算激活数据
        // Token字段贯穿Application的生命周期
        Schema::create('app_launches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->comment = 'apps id';
            $table->unsignedBigInteger('adid')->index()->comment = 'app_devices ID';
            $table->unsignedBigInteger('sub_channel')->index()->comment = '渠道 ID';
            $table->text('private_key')->comment = 'Private Key';
            $table->text('public_key')->comment = 'Public Key';
            $table->timestamp('expired_at')->comment = 'RSA过期时间';
            $table->timestamps();

            $table->index('created_at');

            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('adid')->references('id')->on('app_devices')->onDelete('cascade');

        });

        // App+用户关系表
        Schema::create('app_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->comment = 'apps id';
            $table->unsignedBigInteger('uid')->index()->comment = 'users id';
            $table->unsignedBigInteger('alid')->index()->comment = '注册时 app_launches ID';
            $table->unsignedBigInteger('adid')->index()->comment = '注册时 app_devices ID(冗余)';
            $table->string('cp_user_id', 100)->nullable()->index()->comment = 'CP用户ID';

            $table->timestamps();

            $table->unique(['aid', 'uid']);
            $table->index('created_at');
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('alid')->references('id')->on('app_launches')->onDelete('cascade');
            $table->foreign('adid')->references('id')->on('app_devices')->onDelete('cascade');

        });

        \DB::statement('ALTER TABLE `app_users` ADD `cp_user_binary` VARBINARY(16) after `cp_user_id`;');
        \DB::statement('ALTER TABLE `app_users` ADD INDEX(`cp_user_binary`);');

        /**
         * 冗余表：用户登录表，用于计算活跃、新增
         */
        Schema::create('app_logins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('auid')->index()->comment = 'app_users UID';
            $table->unsignedBigInteger('alid')->index()->comment = 'app_launches ID';
            $table->unsignedInteger('aid')->index()->comment = 'apps ID(冗余)';
            $table->unsignedBigInteger('adid')->index()->comment = 'app_devices ID(冗余)';

            $table->timestamps();

            $table->index('created_at');
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('auid')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('alid')->references('id')->on('app_launches')->onDelete('cascade');
            $table->foreign('adid')->references('id')->on('app_devices')->onDelete('cascade');
        });

        /**
         * 订单表
         */
        Schema::create('app_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no', 30)->index()->comment = '订单号';

            $table->unsignedBigInteger('auid')->index()->comment = 'apps id(冗余)';
            $table->unsignedBigInteger('alid')->index()->comment = 'app_launches id';
            $table->unsignedInteger('aid')->index()->comment = 'apps id(冗余)';
            $table->unsignedBigInteger('adid')->index()->comment = 'app_devices ID(冗余)';

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
            $table->foreign('adid')->references('id')->on('app_devices')->onDelete('cascade');
        });

        Schema::create('app_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('at')->index()->comment = '日期';
            $table->unsignedInteger('aid')->index()->comment = 'apps ID(冗余)';

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
            $table->unsignedInteger('aid')->index()->comment = 'apps ID(冗余)';

            $table->unsignedInteger('retain')->index()->default(0)->comment = '留存';
            $table->decimal('ltv', 10, 2)->index()->default('0')->comment = 'LTV';

            $table->timestamps();

            $table->unique(['at', 'days', 'aid']);
            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
        });

        Schema::create('app_events', function(Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('event_type')->index()->default(0)->comment = '事件类型';
            $table->unsignedInteger('aid')->index()->comment = 'apps ID';
            $table->unsignedBigInteger('adid')->index()->comment = 'app_devices ID';
            $table->unsignedBigInteger('auid')->index()->nullable()->comment = 'app_users id（部分事件为空）';
            $table->nullableMorphs("from");
            $table->json('value')->nullable()->comment = '上报的其它参数';
            $table->string('carrier', 20)->nullable()->comment = '运营商';
            $table->string('connection', 20)->nullable()->comment = 'Wifi/4G/3G/2G/None';
            $table->unsignedBigInteger('app_version_code')->nullable()->comment = 'APP version code';
            $table->string('app_version', 20)->nullable()->comment = 'APP version';
            $table->string('sdk_version', 20)->nullable()->comment = 'SDK version';
            $table->string('geometry')->nullable()->comment = '地理位置';
            $table->unsignedBigInteger('device_at')->nullable()->comment = '设备时间戳ms';
            $table->integer('device_zone')->default(0)->comment = '设备时区';
            $table->ipAddress('ip')->nullable()->comment = '登录IP';

            $table->timestamps();

            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('auid')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('adid')->references('id')->on('app_devices')->onDelete('cascade');
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
        Schema::dropIfExists('app_devices');
        Schema::dropIfExists('app_launches');
    }
}
