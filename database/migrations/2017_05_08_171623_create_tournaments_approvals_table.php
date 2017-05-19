<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments_approvals', function (Blueprint $table) {
            $table->unsignedBigInteger('tournaments_id');
            $table->unsignedBigInteger('members_id');
            $table->boolean('accepted');
            $table->timestamps();

            $table->primary('tournaments_id');

            $table->foreign('members_id')->references('id')->on('members')->onUpdate('restrict')->onDelete('restrict');
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
        Schema::dropIfExists('tournaments_approvals');
    }
}
