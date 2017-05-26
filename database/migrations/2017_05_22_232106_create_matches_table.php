<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('challonges_match_id')->nullable();
            $table->unsignedBigInteger('tournaments_id');
            $table->dateTime('scheduled_time')->nullable();
            $table->smallInteger('round');
            $table->timestamps();

            $table->unique('challonges_match_id');

            $table->foreign('tournaments_id')->references('id')->on('tournaments')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches');
    }
}
