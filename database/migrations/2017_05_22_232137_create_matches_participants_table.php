<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('matches_id');
            $table->unsignedBigInteger('tournaments_registrations_id');
            $table->unsignedTinyInteger('side');
            $table->unsignedTinyInteger('matches_result')->nullable();
            $table->timestamps();

            $table->primary(['matches_id', 'tournaments_registrations_id'], 'matches_participants_primary');

            $table->foreign('matches_id')->references('id')->on('matches')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('tournaments_registrations_id')->references('id')->on('tournaments_registrations')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches_participants');
    }
}
