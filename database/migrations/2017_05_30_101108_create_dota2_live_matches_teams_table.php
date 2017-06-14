<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2LiveMatchesTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_live_matches_teams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dota2_teams_id')->nullable();
            $table->string('dota2_teams_name', 255)->nullable();
            $table->string('dota2_teams_logo', 255)->nullable();
            $table->unsignedBigInteger('tournaments_registrations_id')->nullable();
            $table->unsignedBigInteger('dota2_live_matches_id');
            $table->unsignedTinyInteger('series_wins');
            $table->unsignedSmallInteger('score');
            $table->unsignedSmallInteger('tower_state');
            $table->unsignedSmallInteger('barracks_state');
            $table->unsignedTinyInteger('side');
            $table->unsignedTinyInteger('matches_result')->nullable();
            $table->timestamps();

            $table->foreign('tournaments_registrations_id', 'dota2_live_matches_teams_foreign_1')->references('id')->on('tournaments_registrations')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('dota2_live_matches_id', 'dota2_live_matches_teams_foreign_2')->references('id')->on('dota2_live_matches')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_live_matches_teams');
    }
}
