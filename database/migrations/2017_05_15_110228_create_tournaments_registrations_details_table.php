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
            $table->string('steam32_id', 255);
            $table->string('identification_file_name', 255)->nullable();
            $table->string('qr_identifier', 20);
            $table->timestamps();

            $table->primary(['tournaments_registrations_id', 'members_id'], 'tournaments_registrations_details_primary');

            $table->unique('qr_identifier');

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
