<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2LiveMatchesTeamsPicksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_live_matches_teams_picks', function (Blueprint $table) {
            $table->unsignedBigInteger('dota2_live_matches_teams_id');
            $table->unsignedInteger('dota2_heroes_id');
            $table->unsignedTinyInteger('pick_order');
            $table->timestamps();

            $table->primary(['dota2_live_matches_teams_id', 'dota2_heroes_id'], 'dota2_live_matches_teams_picks_primary');

            $table->foreign('dota2_live_matches_teams_id', 'dota2_live_matches_teams_picks_foreign_1')->references('id')->on('dota2_live_matches_teams')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('dota2_heroes_id', 'dota2_live_matches_teams_picks_foreign_2')->references('id')->on('dota2_heroes')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_live_matches_teams_picks');
    }
}
