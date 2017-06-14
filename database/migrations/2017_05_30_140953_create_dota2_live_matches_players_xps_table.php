<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2LiveMatchesPlayersXpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_live_matches_players_xps', function (Blueprint $table) {
            $table->unsignedBigInteger('dota2_live_matches_players_id');
            $table->unsignedSmallInteger('xp_per_min');
            $table->unsignedMediumInteger('xp');
            $table->unsignedMediumInteger('duration');
            $table->timestamps();

            $table->foreign('dota2_live_matches_players_id', 'dota2_live_matches_players_xps_foreign_1')->references('id')->on('dota2_live_matches_players')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_live_matches_players_xps');
    }
}
