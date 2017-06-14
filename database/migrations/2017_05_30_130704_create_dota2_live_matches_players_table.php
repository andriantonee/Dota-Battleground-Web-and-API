<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2LiveMatchesPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_live_matches_players', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('steam32_id');
            $table->string('name', 255);
            $table->unsignedBigInteger('members_id')->nullable();
            $table->unsignedBigInteger('dota2_live_matches_teams_id');
            $table->unsignedInteger('dota2_heroes_id')->nullable();
            $table->unsignedSmallInteger('kills');
            $table->unsignedSmallInteger('death');
            $table->unsignedSmallInteger('assists');
            $table->unsignedSmallInteger('last_hits');
            $table->unsignedSmallInteger('denies');
            $table->unsignedMediumInteger('gold');
            $table->unsignedSmallInteger('level');
            $table->unsignedSmallInteger('gold_per_min');
            $table->unsignedSmallInteger('xp_per_min');
            $table->unsignedSmallInteger('respawn_timer');
            $table->smallInteger('position_x');
            $table->smallInteger('position_y');
            $table->unsignedMediumInteger('net_worth');
            $table->unsignedTinyInteger('player_order');
            $table->timestamps();

            $table->foreign('members_id', 'dota2_live_matches_players_foreign_1')->references('id')->on('members')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('dota2_live_matches_teams_id', 'dota2_live_matches_players_foreign_2')->references('id')->on('dota2_live_matches_teams')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('dota2_heroes_id', 'dota2_live_matches_players_foreign_3')->references('id')->on('dota2_heroes')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_live_matches_players');
    }
}
