<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2LiveMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_live_matches', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('matches_id')->nullable();
            $table->unsignedInteger('leagues_id');
            $table->unsignedTinyInteger('series_type');
            $table->unsignedBigInteger('spectators');
            $table->unsignedMediumInteger('duration');
            $table->unsignedSmallInteger('roshan_respawn_timer');
            $table->timestamps();

            $table->primary('id');

            $table->foreign('matches_id')->references('id')->on('matches')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_live_matches');
    }
}
