<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsRegistrationsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments_registrations_details', function (Blueprint $table) {
            $table->unsignedBigInteger('tournaments_registrations_id');
            $table->unsignedBigInteger('members_id');
            $table->timestamps();

            $table->primary(['tournaments_registrations_id', 'members_id'], 'tournaments_registrations_details_primary');

            $table->foreign('tournaments_registrations_id', 'tournaments_registrations_details_foreign_1')->references('id')->on('tournaments_registrations')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('members_id', 'tournaments_registrations_details_foreign_2')->references('id')->on('members')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournaments_registrations_details');
    }
}
