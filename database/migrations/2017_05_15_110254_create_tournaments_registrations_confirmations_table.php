<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsRegistrationsConfirmationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments_registrations_confirmations', function (Blueprint $table) {
            $table->unsignedBigInteger('tournaments_registrations_id');
            $table->string('name', 255);
            $table->unsignedSmallInteger('banks_id');
            $table->string('confirmation_file_name', 255);
            $table->timestamps();

            $table->primary('tournaments_registrations_id', 'tournaments_registrations_confirmations_primary');

            $table->foreign('tournaments_registrations_id', 'tournaments_registrations_confirmations_foreign_1')->references('id')->on('tournaments_registrations')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournaments_registrations_confirmations');
    }
}
