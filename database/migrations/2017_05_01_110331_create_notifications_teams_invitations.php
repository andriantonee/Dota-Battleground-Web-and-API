<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTeamsInvitations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications_teams_invitations', function (Blueprint $table) {
            $table->unsignedBigInteger('notifications_id');
            $table->unsignedBigInteger('teams_id');
            $table->unsignedTinyInteger('invitation_status');

            $table->primary('notifications_id');
            $table->foreign('notifications_id')->references('id')->on('notifications')->onUpdate('restrict')->onDelete('restrict');
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
        Schema::dropIfExists('notifications_teams_invitations');
    }
}
