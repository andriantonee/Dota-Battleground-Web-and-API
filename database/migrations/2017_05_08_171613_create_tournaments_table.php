<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->text('description');
            $table->string('logo_file_name', 255);
            $table->unsignedTinyInteger('type');
            $table->unsignedBigInteger('challonges_id')->nullable();
            $table->string('challonges_url', 255)->nullable();
            $table->unsignedInteger('leagues_id')->nullable();
            $table->unsignedMediumInteger('cities_id')->nullable();
            $table->string('address', 255)->nullable();
            $table->unsignedSmallInteger('max_participant');
            $table->text('rules');
            $table->string('prize_1st', 255)->nullable();
            $table->string('prize_2nd', 255)->nullable();
            $table->string('prize_3rd', 255)->nullable();
            $table->text('prize_other')->nullable();
            $table->unsignedInteger('entry_fee');
            $table->dateTime('registration_closed');
            $table->boolean('need_identifications');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('start')->default('0');
            $table->boolean('complete')->default('0');
            $table->boolean('cancel')->default('0');
            $table->unsignedBigInteger('members_id');
            $table->timestamps();

            $table->unique('challonges_id');
            $table->unique('leagues_id');

            $table->foreign('cities_id')->references('id')->on('cities')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('members_id')->references('id')->on('members')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournaments');
    }
}
