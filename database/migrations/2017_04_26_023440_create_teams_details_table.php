<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams_details', function (Blueprint $table) {
            $table->unsignedBigInteger('teams_id');
            $table->unsignedBigInteger('members_id');
            $table->unsignedTinyInteger('members_privilege');
            $table->timestamps();

            $table->primary(['teams_id', 'members_id']);
            $table->foreign('teams_id')->references('id')->on('teams')->onUpdate('restrict')->onDelete('restrict');
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
        Schema::dropIfExists('teams_details');
    }
}
