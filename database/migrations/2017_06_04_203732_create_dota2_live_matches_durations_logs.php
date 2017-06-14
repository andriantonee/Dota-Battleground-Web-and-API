<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2LiveMatchesDurationsLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_live_matches_durations_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('dota2_live_matches_id');
            $table->unsignedMediumInteger('duration');
            $table->timestamps();

            $table->foreign('dota2_live_matches_id', 'dota2_live_matches_durations_logs_foreign_1')->references('id')->on('dota2_live_matches')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_live_matches_durations_logs');
    }
}
