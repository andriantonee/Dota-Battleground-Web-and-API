<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsRegistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments_registrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tournaments_id');
            $table->unsignedBigInteger('teams_id');
            $table->unsignedBigInteger('challonges_participants_id')->nullable();
            $table->timestamps();

            $table->unique(['tournaments_id', 'teams_id']);

            $table->foreign('tournaments_id')->references('id')->on('tournaments')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('teams_id')->references('id')->on('teams')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournaments_registrations');
    }
}
