<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Game
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pid')->index()->default(0)->comment = '父级ID';
            $table->string('path')->index()->comment = 'Tree Path';
            $table->string('name', 50)->comment = '游戏名字';
            $table->unsignedInteger('game_status')->index()->default(0)->comment = '状态';
            $table->unsignedInteger('channel')->index()->default(0)->comment = '硬核通道';
            $table->string('app_id', 50)->index()->comment = 'Appid';
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

        Schema::create('game_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('gid')->index()->default(0)->comment = '游戏ID';
            $table->string('cp_user_id', 100)->index()->comment = 'CP用户ID';

            $table->timestamps();

            $table->index('created_at');

            $table->foreign('gid')->references('id')->on('games')->onDelete('cascade');

        });

        \DB::statement('ALTER TABLE `game_users` ADD `cp_user_binary` VARBINARY(16) after `cp_user_id`;');
        \DB::statement('ALTER TABLE `game_users` ADD INDEX(`cp_user_binary`);');


        Schema::create('game_players', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('gid')->index()->default(0)->comment = '游戏ID(冗余)';
            $table->unsignedBigInteger('guid')->index()->default(0)->comment = '游戏玩家UID';
            $table->string('cp_player_id', 100)->index()->comment = 'CP角色ID';
            $table->string('cp_zone_id', 100)->index()->comment = 'CP角色区服ID';
            $table->string('nickname', 100)->nullable()->comment = '游戏角色名';
            $table->unsignedInteger('level')->index()->default(0)->comment = '角色等级';
            $table->unsignedInteger('vip_level')->index()->default(0)->comment = '角色等级';

            $table->timestamps();

            $table->index('created_at');

            $table->foreign('gid')->references('id')->on('games')->onDelete('cascade');
            $table->foreign('guid')->references('id')->on('game_users')->onDelete('cascade');
        });

        \DB::statement('ALTER TABLE `game_players` ADD `cp_player_binary` VARBINARY(16) after `cp_player_id`;');
        \DB::statement('ALTER TABLE `game_players` ADD INDEX(`cp_player_binary`);');

        \DB::statement('ALTER TABLE `game_players` ADD `cp_zone_binary` VARBINARY(16) after `cp_zone_id`;');
        \DB::statement('ALTER TABLE `game_players` ADD INDEX(`cp_zone_binary`);');


        Schema::create('game_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no', 50)->index()->comment = '订单号';
            $table->unsignedInteger('gid')->index()->default(0)->comment = '游戏ID(冗余)';
            $table->unsignedBigInteger('guid')->index()->default(0)->comment = '游戏玩家UID(冗余)';
            $table->unsignedBigInteger('gpid')->index()->default(0)->comment = '游戏玩家PID';
            $table->string('item_name', 100)->nullable()->comment = '商品名称';
            $table->decimal('amount', 7, 2)->index()->default('0')->comment = '商品价格';
            $table->string('cp_order_no', 100)->nullable()->comment = 'CP订单号';
            $table->string('payment_no', 100)->nullable()->comment = '支付通道订单号';
            $table->unsignedInteger('payment_type')->index()->default(0)->comment = '支付通道类型';
            $table->unsignedInteger('game_order_status')->index()->default(0)->comment = '状态';
            $table->timestamp('paid_at')->index()->nullable()->comment = '支付时间';
            $table->timestamp('cp_callbacked_at')->index()->nullable()->comment = '请求支付回调时间';
            $table->timestamp('success_at')->index()->nullable()->comment = '支付回调成功时间';
            $table->text('cp_callback_response')->nullable()->comment = '回调Response';
            $table->json('payment_params')->nullable()->comment = '其它参数';

            $table->timestamps();
            $table->softDeletes(); //软删除

            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');

            $table->foreign('gid')->references('id')->on('games')->onDelete('cascade');
            $table->foreign('guid')->references('id')->on('game_users')->onDelete('cascade');
            $table->foreign('gpid')->references('id')->on('game_players')->onDelete('cascade');
        });

        Schema::create('game_user_logins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('gid')->index()->default(0)->comment = '游戏ID(冗余)';
            $table->unsignedBigInteger('guid')->index()->default(0)->comment = '游戏玩家UID';
            $table->string('device', 50)->nullable()->comment = '设备名和版本';
            $table->string('imei', 50)->nullable()->comment = 'IMEI';
            $table->string('udid', 50)->nullable()->comment = 'UDID';
            $table->unsignedInteger('online')->index()->default(0)->comment = '在线时长(秒)';
            $table->timestamps();

            $table->index('created_at');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}
