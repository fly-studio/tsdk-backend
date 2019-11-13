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

        Schema::create('game_players', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('aid')->index()->comment = 'apps id(冗余)';
            $table->unsignedBigInteger('auid')->index()->comment = 'app_users id';
            $table->string('cp_player_id', 100)->index()->comment = 'CP角色ID';
            $table->string('zone_id', 100)->index()->comment = '角色区服';
            $table->string('nickname', 100)->nullable()->comment = '游戏角色名';
            $table->unsignedInteger('level')->index()->default(0)->comment = '角色等级';
            $table->unsignedInteger('vip_level')->index()->default(0)->comment = '角色等级';

            $table->timestamps();

            $table->index('created_at');

            $table->foreign('aid')->references('id')->on('apps')->onDelete('cascade');
            $table->foreign('auid')->references('id')->on('app_users')->onDelete('cascade');
        });

        \DB::statement('ALTER TABLE `game_players` ADD `cp_player_binary` VARBINARY(16) after `cp_player_id`;');
        \DB::statement('ALTER TABLE `game_players` ADD INDEX(`cp_player_binary`);');

        \DB::statement('ALTER TABLE `game_players` ADD `zone_binary` VARBINARY(16) after `zone_id`;');
        \DB::statement('ALTER TABLE `game_players` ADD INDEX(`zone_binary`);');


        Schema::create('game_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique()->primary()->comment = 'app_orders id';

            $table->unsignedBigInteger('gpid')->index()->comment = 'game_players id';

            $table->timestamps();

            $table->foreign('id')->references('id')->on('app_orders')->onDelete('cascade');
            $table->foreign('gpid')->references('id')->on('game_players')->onDelete('cascade');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_orders');
        Schema::dropIfExists('game_players');
    }
}
