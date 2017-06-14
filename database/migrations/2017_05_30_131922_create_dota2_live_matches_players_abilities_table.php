<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2LiveMatchesPlayersAbilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_live_matches_players_abilities', function (Blueprint $table) {
            $table->unsignedBigInteger('dota2_live_matches_players_id');
            $table->unsignedInteger('dota2_abilities_id');
            $table->unsignedTinyInteger('ability_order');
            $table->timestamps();

            $table->foreign('dota2_live_matches_players_id', 'dota2_live_matches_players_abilities_foreign_1')->references('id')->on('dota2_live_matches_players')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('dota2_abilities_id', 'dota2_live_matches_players_abilities_foreign_2')->references('id')->on('dota2_abilities')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_live_matches_players_abilities');
    }
}
